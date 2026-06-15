<?php

declare(strict_types=1);

namespace Reorder\Service;

defined('ABSPATH') || exit;

use Reorder\Contract\HasHooks;
use Reorder\Settings\SettingsRepository;
use WC_Order;
use WC_Order_Item_Product;

/**
 * Adds a "reorder" button to past orders and handles the reorder action:
 * re-adds every still-purchasable item from the order to the cart (skipping
 * anything no longer available, with a notice) and redirects to cart/checkout.
 *
 * Security: every reorder link carries a per-order nonce, and the handler
 * re-checks that the current user actually owns the order before acting.
 */
final class ReorderService implements HasHooks
{
    private const ACTION    = 'reorder_again';
    private const QUERY_VAR = 'reorder_order';

    public function __construct(
        private readonly SettingsRepository $settings,
    ) {
    }

    public function registerHooks(): void
    {
        // Button in the My Account → Orders list (one row per order).
        add_filter('woocommerce_my_account_my_orders_actions', [$this, 'addListAction'], 10, 2);

        // Handle the reorder request early, before output, so we can redirect.
        add_action('template_redirect', [$this, 'handleRequest']);
    }

    /**
     * Adds the reorder action to a row in the My Account orders table.
     *
     * @param array<string, array{url: string, name: string}> $actions
     * @param WC_Order                                         $order
     * @return array<string, array{url: string, name: string}>
     */
    public function addListAction(array $actions, $order): array
    {
        if (! $order instanceof WC_Order || ! $this->orderQualifies($order)) {
            return $actions;
        }

        $actions['reorder'] = [
            'url'  => $this->reorderUrl($order),
            'name' => $this->settings->buttonText(),
        ];

        return $actions;
    }

    /**
     * Intercepts a reorder request, validates it, and acts.
     */
    public function handleRequest(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce is verified below before any state change.
        if (! isset($_GET[self::QUERY_VAR], $_GET['action']) || sanitize_key(wp_unslash($_GET['action'])) !== self::ACTION) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Cast/validated immediately; nonce checked next.
        $orderId = absint(wp_unslash($_GET[self::QUERY_VAR]));
        $nonce   = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($orderId <= 0 || ! wp_verify_nonce($nonce, self::ACTION . '_' . $orderId)) {
            wc_add_notice(__('That reorder link has expired. Please try again.', 'reorder'), 'error');
            $this->redirect(wc_get_account_endpoint_url('orders'));
        }

        if (! is_user_logged_in()) {
            $this->redirect(wc_get_account_endpoint_url('orders'));
        }

        $order = wc_get_order($orderId);

        // Ownership + qualification check — never act on someone else's order (no IDOR).
        if (! $order instanceof WC_Order
            || $order->get_customer_id() !== get_current_user_id()
            || ! $this->orderQualifies($order)
        ) {
            wc_add_notice(__('We could not find that order.', 'reorder'), 'error');
            $this->redirect(wc_get_account_endpoint_url('orders'));
        }

        /** @var WC_Order $order */
        $this->refill($order);
        $this->redirect($this->settings->redirectUrl());
    }

    /**
     * Re-adds every still-purchasable line item to the cart, collecting notices
     * for anything skipped.
     */
    private function refill(WC_Order $order): void
    {
        if (! function_exists('WC') || WC()->cart === null) {
            wc_add_notice(__('The cart is not available right now. Please try again.', 'reorder'), 'error');

            return;
        }

        $added   = 0;
        $skipped = [];

        foreach ($order->get_items() as $item) {
            if (! $item instanceof WC_Order_Item_Product) {
                continue;
            }

            $productId   = $item->get_product_id();
            $variationId = $item->get_variation_id();
            $quantity    = max(1, (int) $item->get_quantity());
            $product     = $item->get_product();
            $name        = $product instanceof \WC_Product ? $product->get_name() : $item->get_name();

            if (! $product instanceof \WC_Product || ! $product->is_purchasable() || ! $product->is_in_stock()) {
                $skipped[] = $name;
                continue;
            }

            // Variation attributes (e.g. size/colour) so the right variation is re-added.
            $variations = [];
            foreach ($item->get_meta_data() as $meta) {
                $data = $meta->get_data();
                if (is_string($data['key']) && str_starts_with($data['key'], 'pa_')) {
                    $variations[$data['key']] = $data['value'];
                }
            }

            $result = WC()->cart->add_to_cart(
                $productId,
                $quantity,
                $variationId,
                $variations,
            );

            if ($result === false) {
                $skipped[] = $name;
                continue;
            }

            ++$added;
        }

        $this->reportResult($added, $skipped);

        /**
         * Fires after a reorder has re-added the order's items to the cart.
         *
         * Add-ons (e.g. Reorder Pro) hook this to react to a completed reorder —
         * for example, applying a reward coupon to the cart.
         *
         * @param WC_Order     $order   The order that was reordered.
         * @param int          $added   Number of line items added back to the cart.
         * @param list<string> $skipped Names of items that could not be re-added.
         */
        do_action('reorder/refilled', $order, $added, $skipped);
    }

    /**
     * Surfaces a user-facing summary of what was added vs. skipped.
     *
     * @param int          $added
     * @param list<string> $skipped
     */
    private function reportResult(int $added, array $skipped): void
    {
        if ($added > 0) {
            wc_add_notice(
                sprintf(
                    /* translators: %d: number of items added back to the cart. */
                    _n('%d item from your order was added back to the cart.', '%d items from your order were added back to the cart.', $added, 'reorder'),
                    $added,
                ),
                'success',
            );
        }

        if ($skipped !== []) {
            wc_add_notice(
                sprintf(
                    /* translators: %s: comma-separated list of product names that could not be re-added. */
                    __('These items are no longer available and were skipped: %s', 'reorder'),
                    implode(', ', array_map('sanitize_text_field', $skipped)),
                ),
                'notice',
            );
        }

        if ($added === 0 && $skipped === []) {
            wc_add_notice(__('There was nothing from that order to add to the cart.', 'reorder'), 'notice');
        }
    }

    /**
     * Whether an order is eligible for a reorder button (status + has items).
     */
    private function orderQualifies(WC_Order $order): bool
    {
        if (! in_array($order->get_status(), $this->settings->statuses(), true)) {
            return false;
        }

        return count($order->get_items()) > 0;
    }

    /**
     * Builds the nonce-protected reorder URL for an order.
     */
    private function reorderUrl(WC_Order $order): string
    {
        $orderId = $order->get_id();

        $url = add_query_arg(
            [
                self::QUERY_VAR => $orderId,
                'action'        => self::ACTION,
            ],
            wc_get_account_endpoint_url('orders'),
        );

        return wp_nonce_url($url, self::ACTION . '_' . $orderId);
    }

    /**
     * Safe redirect + exit. Extracted so tests and PHPStan see a single exit point.
     */
    private function redirect(string $url): void
    {
        wp_safe_redirect($url);
        exit;
    }
}
