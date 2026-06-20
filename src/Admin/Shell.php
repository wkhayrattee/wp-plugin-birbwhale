<?php

declare(strict_types=1);

namespace BirbWhale\Admin;

use BirbWhale\Core\Enum;
use BirbWhale\Core\PluginManager;
use BirbWhale\Core\Utils;
use WordPress\AiClient\AiClient;

defined('ABSPATH') || exit;

/**
 * The branded admin "app shell": one top-level BirbWhale page with a header bar,
 * an in-page left sidebar, and a content panel. Every section renders inside the
 * same chrome via the buffer-then-wrap pattern, so the plugin feels like one app.
 *
 * The shell owns chrome + routing only; each section delegates to its own
 * renderer (SettingsPage / LogPage / the dashboard).
 *
 * @since 1.0.0
 */
class Shell
{
    /** In-shell sections this page can route to (whitelist for the `view` arg). */
    private const VIEWS = [
        Enum::VIEW_DASHBOARD,
        Enum::VIEW_SETTINGS,
        Enum::VIEW_LOG,
        Enum::VIEW_HELP,
    ];

    /**
     * Resolve the requested section, defaulting to the dashboard.
     *
     * @since 1.0.0
     */
    public static function currentView(): string
    {
        $view = isset($_GET[Enum::SHELL_VIEW_PARAM])
            ? sanitize_key((string) $_GET[Enum::SHELL_VIEW_PARAM])
            : Enum::VIEW_DASHBOARD;

        return in_array($view, self::VIEWS, true) ? $view : Enum::VIEW_DASHBOARD;
    }

    /**
     * Admin URL for a given in-shell section.
     *
     * @since 1.0.0
     */
    public static function url(string $view = Enum::VIEW_DASHBOARD): string
    {
        $args = ['page' => Enum::MENU_SLUG];
        if (Enum::VIEW_DASHBOARD !== $view) {
            $args[Enum::SHELL_VIEW_PARAM] = $view;
        }

        return add_query_arg($args, admin_url('admin.php'));
    }

    /**
     * Top-level page callback. Routes the `view` arg to a section renderer,
     * buffers its output, then wraps it in the chrome.
     *
     * @since 1.0.0
     */
    public static function render(): void
    {
        $capability = apply_filters('birbwhale_admin_capability', Enum::ADMIN_CAPABILITY);
        if (!current_user_can($capability)) {
            return;
        }

        $view = self::currentView();

        ob_start();
        switch ($view) {
            case Enum::VIEW_SETTINGS:
                SettingsPage::renderSection();
                break;
            case Enum::VIEW_LOG:
                LogPage::renderSection();
                break;
            case Enum::VIEW_HELP:
                HelpPage::renderSection();
                break;
            case Enum::VIEW_DASHBOARD:
            default:
                self::renderDashboard();
                break;
        }

        self::renderPage($view, (string) ob_get_clean());
    }

    /**
     * Wrap pre-rendered section HTML in the shell chrome.
     *
     * @since 1.0.0
     *
     * @param string $active  Nav key to highlight.
     * @param string $content Pre-rendered (already-escaped) section HTML.
     */
    public static function renderPage(string $active, string $content): void
    {
        Utils::loadView('admin/shell.php', [
            'active'   => $active,
            'brand'    => __('BirbWhale', 'birbwhale'),
            'tagline'  => __('DeepSeek · AI Connector', 'birbwhale'),
            'mark_url' => BIRBWHALE_URL . 'assets/images/icon.svg',
            'nav'      => self::navItems($active),
            'content'  => $content,
        ]);
    }

    /**
     * The dashboard overview (shell home).
     *
     * @since 1.0.0
     */
    private static function renderDashboard(): void
    {
        $ai = class_exists(AiClient::class);

        Utils::loadView('admin/shell-dashboard.php', [
            'settings_url'        => self::url(Enum::VIEW_SETTINGS),
            'log_url'             => self::url(Enum::VIEW_LOG),
            'connectors_url'      => admin_url('options-connectors.php'),
            'credentials_url'     => Enum::PROVIDER_CREDENTIALS_URL,
            'ai_client_available' => $ai,
            'ai_client_version'   => $ai ? AiClient::VERSION : '',
            'enabled'             => PluginManager::isEnabled(),
            'has_key'             => '' !== (string) get_option(Enum::PROVIDER_KEY_OPTION, ''),
        ]);
    }

    /**
     * The in-page sidebar model. One zone (single menu); rows are a `group`
     * heading or an `item`.
     *
     * @since 1.0.0
     *
     * @return array<int, array<string, mixed>>
     */
    private static function navItems(string $active): array
    {
        $items = [
            ['type' => 'item', 'key' => Enum::VIEW_DASHBOARD, 'label' => __('Dashboard', 'birbwhale'), 'icon' => 'dashicons-dashboard', 'url' => self::url(Enum::VIEW_DASHBOARD)],
            ['type' => 'item', 'key' => Enum::VIEW_SETTINGS, 'label' => __('Settings', 'birbwhale'), 'icon' => 'dashicons-admin-generic', 'url' => self::url(Enum::VIEW_SETTINGS)],
            ['type' => 'group', 'label' => __('Maintenance', 'birbwhale')],
            ['type' => 'item', 'key' => Enum::VIEW_LOG, 'label' => __('Log', 'birbwhale'), 'icon' => 'dashicons-warning', 'url' => self::url(Enum::VIEW_LOG)],
            ['type' => 'group', 'label' => __('Support', 'birbwhale')],
            ['type' => 'item', 'key' => Enum::VIEW_HELP, 'label' => __('Get Help', 'birbwhale'), 'icon' => 'dashicons-sos', 'url' => self::url(Enum::VIEW_HELP)],
        ];

        /**
         * Filters the in-page sidebar model.
         *
         * @since 1.0.0
         *
         * @param array<int, array<string, mixed>> $items  Sidebar rows.
         * @param string                           $active Active nav key.
         */
        return apply_filters('birbwhale_shell_nav', $items, $active);
    }
}
