<?php

declare(strict_types=1);

namespace Reorder\Settings;

defined('ABSPATH') || exit;

use const Reorder\PLUGIN_DIR;

/**
 * Typed, defaulted access to the `reorder_settings` option.
 *
 * Reads are cached for the request. Every getter falls back to a sane default so
 * the plugin behaves correctly before the settings page is ever saved.
 */
final class SettingsRepository
{
    public const OPTION = 'reorder_settings';

    /** Order statuses (sans `wc-`) we allow the button on, regardless of config. */
    private const KNOWN_STATUSES = ['completed', 'processing', 'on-hold'];

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        /** @var array<string, mixed> $defaults */
        $defaults = require PLUGIN_DIR . '/config/defaults.php';

        return $defaults;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->cache === null) {
            $stored      = get_option(self::OPTION, []);
            $this->cache = array_merge($this->defaults(), is_array($stored) ? $stored : []);
        }

        return $this->cache;
    }

    /**
     * The button label, falling back to a translated default when unset.
     */
    public function buttonText(): string
    {
        $text = (string) ($this->all()['button_text'] ?? '');

        return $text !== '' ? $text : __('Order again', 'reorder');
    }

    /**
     * Order statuses (without the `wc-` prefix) the button should appear on.
     * Always intersected with a known-safe allow-list.
     *
     * @return list<string>
     */
    public function statuses(): array
    {
        $raw = $this->all()['statuses'] ?? [];

        if (! is_array($raw)) {
            return ['completed'];
        }

        $clean = array_values(array_intersect(
            array_map('strval', $raw),
            self::KNOWN_STATUSES,
        ));

        return $clean !== [] ? $clean : ['completed'];
    }

    /**
     * @return list<string>
     */
    public function knownStatuses(): array
    {
        return self::KNOWN_STATUSES;
    }

    /**
     * Redirect target after re-adding items: `cart` or `checkout`.
     */
    public function redirect(): string
    {
        $value = (string) ($this->all()['redirect'] ?? 'cart');

        return in_array($value, ['cart', 'checkout'], true) ? $value : 'cart';
    }

    /**
     * The resolved redirect URL for the configured target.
     */
    public function redirectUrl(): string
    {
        $target = $this->redirect();

        return $target === 'checkout' ? wc_get_checkout_url() : wc_get_cart_url();
    }
}
