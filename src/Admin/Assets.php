<?php

declare(strict_types=1);

namespace Reorder\Admin;

defined('ABSPATH') || exit;

use Reorder\Contract\HasHooks;
use Reorder\Plugin;

/**
 * Enqueues the admin stylesheet, but only on Reorder's own settings screen,
 * so nothing leaks into the rest of wp-admin.
 */
final class Assets implements HasHooks
{
    private const HANDLE = 'reorder-admin';

    /** Hook suffix of the Reorder settings page (`admin_enqueue_scripts` arg). */
    private const PAGE_HOOK = 'woocommerce_page_reorder-settings';

    public function registerHooks(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(string $hookSuffix): void
    {
        if ($hookSuffix !== self::PAGE_HOOK) {
            return;
        }

        $plugin = Plugin::instance();

        wp_enqueue_style(
            self::HANDLE,
            $plugin->url('assets/css/admin.css'),
            [],
            \Reorder\VERSION,
        );

        wp_enqueue_script(
            self::HANDLE,
            $plugin->url('assets/js/admin.js'),
            [],
            \Reorder\VERSION,
            true,
        );
    }
}
