<?php

declare(strict_types=1);

namespace Reorder\Admin;

defined('ABSPATH') || exit;

use Reorder\Contract\HasHooks;
use Reorder\Settings\SettingsRepository;

/**
 * Admin settings page registered under the WooCommerce menu.
 *
 * Stores settings in the `reorder_settings` option (array).
 */
final class Settings implements HasHooks
{
    private const PAGE            = 'reorder-settings';
    private const SECTION_GENERAL = 'reorder_general';
    private const SECTION_DISPLAY = 'reorder_display';

    public function __construct(
        private readonly SettingsRepository $settings,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_filter(
            'plugin_action_links_' . plugin_basename(\Reorder\PLUGIN_FILE),
            [$this, 'addSettingsLink'],
        );
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Reorder Settings', 'reorder'),
            __('Reorder', 'reorder'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
        );
    }

    /**
     * Adds a "Settings" shortcut on the Plugins screen.
     *
     * @param array<int|string, string> $links
     * @return array<int|string, string>
     */
    public function addSettingsLink(array $links): array
    {
        $url = admin_url('admin.php?page=' . self::PAGE);

        $settingsLink = sprintf(
            '<a href="%s">%s</a>',
            esc_url($url),
            esc_html__('Settings', 'reorder'),
        );

        array_unshift($links, $settingsLink);

        return $links;
    }

    public function registerSettings(): void
    {
        register_setting(
            self::PAGE,
            SettingsRepository::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
            ],
        );

        // ── General ──────────────────────────────────────────────────────────
        add_settings_section(
            self::SECTION_GENERAL,
            __('General', 'reorder'),
            '__return_false',
            self::PAGE,
        );

        add_settings_field(
            'button_text',
            __('Button text', 'reorder'),
            [$this, 'renderText'],
            self::PAGE,
            self::SECTION_GENERAL,
            [
                'id'          => 'button_text',
                'placeholder' => __('Order again', 'reorder'),
                'help'        => __('Label shown on the reorder button in My Account. An action phrase such as "Order again" or "Buy these again" works well. Leave blank to use the default.', 'reorder'),
            ],
        );

        add_settings_field(
            'redirect',
            __('After reordering', 'reorder'),
            [$this, 'renderRedirect'],
            self::PAGE,
            self::SECTION_GENERAL,
            [
                'id'   => 'redirect',
                'help' => __('Where to send the customer once their items are back in the cart. Send straight to checkout for the fastest repeat purchase, or to the cart so they can review and adjust first.', 'reorder'),
            ],
        );

        // ── Display ──────────────────────────────────────────────────────────
        add_settings_section(
            self::SECTION_DISPLAY,
            __('Where it appears', 'reorder'),
            static function (): void {
                echo '<p>' . esc_html__('Choose which past orders show a reorder button.', 'reorder') . '</p>';
            },
            self::PAGE,
        );

        add_settings_field(
            'statuses',
            __('Order statuses', 'reorder'),
            [$this, 'renderStatuses'],
            self::PAGE,
            self::SECTION_DISPLAY,
            [
                'id'   => 'statuses',
                'help' => __('The button only appears on orders with one of the ticked statuses. "Completed" is the usual choice; add "Processing" if you want customers to reorder before fulfilment.', 'reorder'),
            ],
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }
        ?>
        <div class="wrap reorder-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p class="reorder-admin__lead">
                <?php esc_html_e('Add a one-click reorder button to past orders so customers can buy the same items again in seconds.', 'reorder'); ?>
            </p>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::PAGE);
                do_settings_sections(self::PAGE);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Renders a field's help text as a standard description paragraph.
     */
    private function helpText(string $text): string
    {
        if ($text === '') {
            return '';
        }

        return sprintf('<p class="description">%s</p>', esc_html($text));
    }

    /**
     * @param array<string, string> $args
     */
    public function renderText(array $args): void
    {
        $options     = $this->settings->all();
        $id          = $args['id'] ?? '';
        $value       = isset($options[$id]) ? (string) $options[$id] : '';
        $placeholder = $args['placeholder'] ?? '';

        printf(
            '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" placeholder="%4$s" class="regular-text" />',
            esc_attr($id),
            esc_attr(SettingsRepository::OPTION),
            esc_attr($value),
            esc_attr($placeholder),
        );

        echo $this->helpText($args['help'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped by helpText().
    }

    /**
     * @param array<string, string> $args
     */
    public function renderRedirect(array $args): void
    {
        $id      = $args['id'] ?? 'redirect';
        $current = $this->settings->redirect();
        $choices = [
            'cart'     => __('Go to the cart', 'reorder'),
            'checkout' => __('Go straight to checkout', 'reorder'),
        ];

        echo '<fieldset>';
        foreach ($choices as $value => $label) {
            printf(
                '<label class="reorder-choice"><input type="radio" name="%1$s[%2$s]" value="%3$s" %4$s /> %5$s</label>',
                esc_attr(SettingsRepository::OPTION),
                esc_attr($id),
                esc_attr($value),
                checked($current, $value, false),
                esc_html($label),
            );
        }
        echo '</fieldset>';
        echo $this->helpText($args['help'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped by helpText().
    }

    /**
     * @param array<string, string> $args
     */
    public function renderStatuses(array $args): void
    {
        $id       = $args['id'] ?? 'statuses';
        $selected = $this->settings->statuses();
        $labels   = wc_get_order_statuses(); // keys like `wc-completed`.

        echo '<fieldset>';
        foreach ($this->settings->knownStatuses() as $status) {
            $label = $labels['wc-' . $status] ?? ucfirst($status);
            printf(
                '<label class="reorder-choice"><input type="checkbox" name="%1$s[%2$s][]" value="%3$s" %4$s /> %5$s</label>',
                esc_attr(SettingsRepository::OPTION),
                esc_attr($id),
                esc_attr($status),
                checked(in_array($status, $selected, true), true, false),
                esc_html($label),
            );
        }
        echo '</fieldset>';
        echo $this->helpText($args['help'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped by helpText().
    }

    /**
     * Sanitizes and coerces incoming POST values before they are saved.
     *
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $known = $this->settings->knownStatuses();

        $statuses = [];
        if (isset($raw['statuses']) && is_array($raw['statuses'])) {
            $statuses = array_values(array_intersect(
                array_map('sanitize_key', $raw['statuses']),
                $known,
            ));
        }
        if ($statuses === []) {
            $statuses = ['completed'];
        }

        $redirect = sanitize_key((string) ($raw['redirect'] ?? 'cart'));
        if (! in_array($redirect, ['cart', 'checkout'], true)) {
            $redirect = 'cart';
        }

        return [
            'button_text' => sanitize_text_field((string) ($raw['button_text'] ?? '')),
            'statuses'    => $statuses,
            'redirect'    => $redirect,
        ];
    }
}
