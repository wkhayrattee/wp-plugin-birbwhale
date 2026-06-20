<?php

declare(strict_types=1);

namespace BirbWhale\Core;

use BirbWhale\Admin\SettingsPage;
use BirbWhale\Admin\Shell;
use BirbWhale\Provider\DeepSeekProvider;
use WordPress\AiClient\AiClient;

defined('ABSPATH') || exit;

/**
 * Orchestrator: lifecycle, hook wiring, provider registration, admin menu.
 *
 * @since 1.0.0
 */
class PluginManager
{
    /**
     * Activation hook. Uses the "activation seed" pattern.
     *
     * @since 1.0.0
     */
    public static function activate(): void
    {
        add_option(Enum::PLUGIN_KEY, true);
    }

    /**
     * Deactivation hook. Clears transient/runtime data only — never user settings.
     *
     * @since 1.0.0
     */
    public static function deactivate(): void
    {
        /**
         * Fires while BirbWhale is being deactivated.
         *
         * @since 1.0.0
         */
        do_action('birbwhale_deactivating');

        delete_transient('birbwhale_provider_status');
    }

    /**
     * Wire all runtime hooks. Hooked on `plugins_loaded`.
     *
     * @since 1.0.0
     */
    public static function boot(): void
    {
        // Translations and provider registration must run on the front-end too,
        // because AI generation can happen during any request — not only in wp-admin.
        add_action('init', [self::class, 'loadTextdomain']);
        add_action('init', [self::class, 'registerProvider'], Enum::PROVIDER_REGISTER_PRIORITY);

        if (is_admin()) {
            add_action('admin_init', [self::class, 'adminInit']);
            add_action('admin_menu', [self::class, 'adminMenu']);
            add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
        }

        /**
         * Fires once BirbWhale has wired its hooks and is ready.
         *
         * @since 1.0.0
         */
        do_action('birbwhale_loaded');
    }

    /**
     * Load the plugin text domain.
     *
     * @since 1.0.0
     */
    public static function loadTextdomain(): void
    {
        load_plugin_textdomain(
            Enum::TEXT_DOMAIN,
            false,
            dirname(BIRBWHALE_BASENAME) . '/languages/'
        );
    }

    /**
     * Whether the DeepSeek connector is enabled in settings (default: yes).
     *
     * @since 1.0.0
     */
    public static function isEnabled(): bool
    {
        $settings = get_option(Enum::SETTINGS_OPTION_NAME, []);
        if (!is_array($settings) || !isset($settings[Enum::FIELD_ENABLED])) {
            return true; // Enabled by default until the admin says otherwise.
        }

        return 'on' === $settings[Enum::FIELD_ENABLED];
    }

    /**
     * Register the DeepSeek provider with the AI Client default registry.
     *
     * Hooked on `init` at {@see Enum::PROVIDER_REGISTER_PRIORITY}.
     *
     * @since 1.0.0
     */
    public static function registerProvider(): void
    {
        if (!self::isEnabled()) {
            return;
        }

        // The AI Client ships with WordPress 7.0 core. Bail gracefully on older installs.
        if (!class_exists(AiClient::class)) {
            Utils::log(
                'WordPress AI Client (WordPress\\AiClient\\AiClient) not found. '
                . 'BirbWhale requires WordPress 7.0+; the DeepSeek provider was not registered.',
                Enum::LOG_WARNING
            );
            return;
        }

        $registry = AiClient::defaultRegistry();

        if ($registry->hasProvider(DeepSeekProvider::class) || $registry->hasProvider(Enum::PROVIDER_ID)) {
            return;
        }

        /**
         * Fires before the DeepSeek provider is registered with the AI Client.
         *
         * @since 1.0.0
         */
        do_action('birbwhale_before_register_provider');

        $registry->registerProvider(DeepSeekProvider::class);

        /**
         * Fires after the DeepSeek provider is registered with the AI Client.
         *
         * @since 1.0.0
         */
        do_action('birbwhale_after_register_provider');
    }

    /**
     * Process the activation seed and register settings.
     *
     * @since 1.0.0
     */
    public static function adminInit(): void
    {
        if (get_option(Enum::PLUGIN_KEY)) {
            /**
             * Filters the default settings applied on first activation.
             *
             * @since 1.0.0
             *
             * @param array<string, mixed> $defaults Default settings.
             */
            $defaults = apply_filters('birbwhale_default_settings', [
                Enum::FIELD_ENABLED => 'on',
            ]);

            add_option(Enum::SETTINGS_OPTION_NAME, $defaults);

            delete_option(Enum::PLUGIN_KEY);
        }

        SettingsPage::registerSettings();
    }

    /**
     * Register the admin menu — a single top-level BirbWhale page. All sections
     * (Dashboard, Settings, Log) render inside the app shell via the `view` arg,
     * so there is only one menu item with an in-page sidebar.
     *
     * @since 1.0.0
     */
    public static function adminMenu(): void
    {
        /**
         * Filters the capability required to view BirbWhale admin pages.
         *
         * @since 1.0.0
         *
         * @param string $capability A WordPress capability.
         */
        $capability = apply_filters('birbwhale_admin_capability', Enum::ADMIN_CAPABILITY);

        add_menu_page(
            __('BirbWhale', 'birbwhale'),
            __('BirbWhale', 'birbwhale'),
            $capability,
            Enum::MENU_SLUG,
            [Shell::class, 'render'],
            self::menuIcon(),
            Enum::MENU_POSITION
        );

        // Relabel the auto-created duplicate submenu to "Dashboard" so the single
        // menu reads cleanly (sub-sections live in the in-page sidebar, not here).
        add_submenu_page(
            Enum::MENU_SLUG,
            __('BirbWhale', 'birbwhale'),
            __('Dashboard', 'birbwhale'),
            $capability,
            Enum::MENU_SLUG,
            [Shell::class, 'render']
        );
    }

    /**
     * The admin menu icon — the BirbWhale plugin icon (CaptainBirb owl + DeepSeek badge).
     *
     * @since 1.0.0
     */
    private static function menuIcon(): string
    {
        // A 20px-intrinsic build of the plugin icon — WordPress renders custom menu
        // icons as an unconstrained <img>, so a 256px SVG would overflow the menu.
        return BIRBWHALE_URL . 'assets/images/icon-menu.svg';
    }

    /**
     * Enqueue admin assets only on the BirbWhale page.
     *
     * @since 1.0.0
     *
     * @param string $hook Current admin page hook suffix.
     */
    public static function enqueueAdminAssets(string $hook): void
    {
        if ('toplevel_page_' . Enum::MENU_SLUG !== $hook) {
            return;
        }

        wp_enqueue_style(
            'birbwhale-admin',
            BIRBWHALE_URL . 'assets/css/admin.css',
            [],
            BIRBWHALE_CACHE_NONCE
        );

        wp_enqueue_script(
            'birbwhale-admin',
            BIRBWHALE_URL . 'assets/js/admin.js',
            [],
            BIRBWHALE_CACHE_NONCE,
            true
        );
    }
}
