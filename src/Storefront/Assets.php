<?php

declare(strict_types=1);

namespace Reorder\Storefront;

defined('ABSPATH') || exit;

use Reorder\Contract\HasHooks;
use Reorder\Plugin;

/**
 * Enqueues the storefront stylesheet that gives the "Order again" action its
 * own mark — but only on the My Account → Orders page where the button lives,
 * so nothing is loaded on the rest of the store.
 *
 * CSS only: the plugin adds no front-end JavaScript and no extra markup. The
 * stylesheet targets WooCommerce's own `.button.reorder` action class.
 */
final class Assets implements HasHooks
{
    private const HANDLE = 'reorder-storefront';

    public function registerHooks(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(): void
    {
        // Only on the account "orders" list, where the reorder button renders.
        if (! function_exists('is_account_page') || ! is_account_page()) {
            return;
        }

        if (function_exists('is_wc_endpoint_url') && ! is_wc_endpoint_url('orders')) {
            return;
        }

        $plugin = Plugin::instance();

        wp_enqueue_style(
            self::HANDLE,
            $plugin->url('assets/css/storefront.css'),
            [],
            \Reorder\VERSION,
        );
    }
}
