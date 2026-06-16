<?php
/**
 * Boot order: services listed here are resolved from the container and have
 * their registerHooks() called during Plugin::boot(). Each must implement
 * Reorder\Contract\HasHooks. Admin-only classes are listed only in wp-admin.
 *
 * @package Reorder
 *
 * @return array<class-string>
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

use Reorder\Admin\Assets;
use Reorder\Admin\Settings;
use Reorder\Service\ReorderService;
use Reorder\Storefront\Assets as StorefrontAssets;

return is_admin()
    ? [
        ReorderService::class,
        Settings::class,
        Assets::class,
    ]
    : [
        ReorderService::class,
        StorefrontAssets::class,
    ];
