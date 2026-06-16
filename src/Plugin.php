<?php

declare(strict_types=1);

namespace Reorder;

defined('ABSPATH') || exit;

use Reorder\Contract\HasHooks;

/**
 * Main plugin class: wires the DI container, runs migrations, and boots
 * every HasHooks service listed in config/hooks.php.
 */
final class Plugin
{
    private static ?self $instance = null;

    private Container $container;

    private bool $booted = false;

    private function __construct()
    {
        $this->container = new Container();

        // Register service factories up front so the container is usable from the
        // activation hook, which fires during activate_plugin() — BEFORE
        // plugins_loaded and boot() run. (Lazy closures: nothing is built here.)
        (require PLUGIN_DIR . '/config/services.php')($this->container);
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Absolute path to the plugin directory (with optional relative path appended).
     */
    public function path(string $relative = ''): string
    {
        return PLUGIN_DIR . ($relative !== '' ? '/' . ltrim($relative, '/') : '');
    }

    /**
     * URL to a file inside the plugin directory.
     */
    public function url(string $relative = ''): string
    {
        return plugins_url($relative, PLUGIN_FILE);
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        $this->container->get(Migrator::class)->maybeMigrate();

        /** @var array<class-string<HasHooks>> $hooks */
        $hooks = require PLUGIN_DIR . '/config/hooks.php';
        foreach ($hooks as $id) {
            $service = $this->container->get($id);
            if ($service instanceof HasHooks) {
                $service->registerHooks();
            }
        }

        /**
         * Fires after the plugin has fully booted and all services have
         * registered their hooks. Add-ons (e.g. Reorder Pro) extend the shared
         * container and register their own hooks from here.
         *
         * @param Plugin $plugin The booted plugin instance.
         */
        do_action('reorder/booted', $this);
    }
}
