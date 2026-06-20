<?php

declare(strict_types=1);

namespace BirbWhale\Admin;

use BirbWhale\Core\Enum;
use BirbWhale\Core\PluginManager;
use BirbWhale\Core\Utils;
use WordPress\AiClient\AiClient;

defined('ABSPATH') || exit;

/**
 * BirbWhale settings / status page.
 *
 * The API key itself lives on WordPress core's Settings → Connectors screen (core
 * owns it). This page provides one real setting — an on/off toggle for the
 * connector — plus an at-a-glance status panel and links to where the key is set.
 *
 * @since 1.0.0
 */
class SettingsPage
{
    /**
     * Register the Settings API setting, section, and field.
     *
     * @since 1.0.0
     */
    public static function registerSettings(): void
    {
        register_setting(
            Enum::SETTINGS_OPTION_GROUP,
            Enum::SETTINGS_OPTION_NAME,
            [
                'type'              => 'array',
                'sanitize_callback' => [self::class, 'sanitizeSettings'],
                'default'           => [Enum::FIELD_ENABLED => 'on'],
            ]
        );

        add_settings_section(
            Enum::SETTINGS_SECTION,
            __('DeepSeek connector', 'birbwhale'),
            '__return_null',
            Enum::MENU_SLUG_SETTINGS
        );

        add_settings_field(
            Enum::FIELD_ENABLED,
            __('Enable connector', 'birbwhale'),
            [self::class, 'renderEnabledField'],
            Enum::MENU_SLUG_SETTINGS,
            Enum::SETTINGS_SECTION,
            ['label_for' => Enum::FIELD_ENABLED]
        );
    }

    /**
     * Centralized sanitization for BirbWhale settings.
     *
     * @since 1.0.0
     *
     * @param mixed $input Raw input from the settings form.
     * @return array<string, mixed> Sanitized settings.
     */
    public static function sanitizeSettings($input): array
    {
        $input = is_array($input) ? $input : [];

        $sanitized = [
            // On/off — whitelist, never trust the raw value.
            Enum::FIELD_ENABLED => (($input[Enum::FIELD_ENABLED] ?? '') === 'on') ? 'on' : 'off',
        ];

        /**
         * Filters the sanitized BirbWhale settings before they are stored.
         *
         * @since 1.0.0
         *
         * @param array<string, mixed> $sanitized Sanitized settings.
         * @param array<string, mixed> $input     Raw input.
         */
        return apply_filters('birbwhale_sanitized_settings', $sanitized, $input);
    }

    /**
     * Render the enable toggle field.
     *
     * @since 1.0.0
     */
    public static function renderEnabledField(): void
    {
        Utils::loadView('admin/field-enabled.php', [
            'field_id'   => Enum::FIELD_ENABLED,
            'field_name' => Enum::SETTINGS_OPTION_NAME . '[' . Enum::FIELD_ENABLED . ']',
            'enabled'    => PluginManager::isEnabled(),
        ]);
    }

    /**
     * Render the settings/status page.
     *
     * @since 1.0.0
     */
    public static function render(): void
    {
        $capability = apply_filters('birbwhale_admin_capability', Enum::ADMIN_CAPABILITY);
        if (!current_user_can($capability)) {
            return;
        }

        global $title;

        $ai_client = class_exists(AiClient::class);

        $args = [
            'page_title'          => $title ?: __('BirbWhale', 'birbwhale'),
            'option_group'        => Enum::SETTINGS_OPTION_GROUP,
            'menu_slug'           => Enum::MENU_SLUG_SETTINGS,
            'connectors_url'      => admin_url('options-connectors.php'),
            'credentials_url'     => Enum::PROVIDER_CREDENTIALS_URL,
            'ai_client_available' => $ai_client,
            'ai_client_version'   => $ai_client ? AiClient::VERSION : '',
            'enabled'             => PluginManager::isEnabled(),
            'has_key'             => '' !== (string) get_option(Enum::PROVIDER_KEY_OPTION, ''),
        ];

        /**
         * Filters the args passed to the BirbWhale settings template.
         *
         * @since 1.0.0
         *
         * @param array<string, mixed> $args          Template args.
         * @param string               $template_file Template path.
         */
        $args = apply_filters('birbwhale_template_args', $args, 'admin/page-settings.php');

        Utils::loadView('admin/page-settings.php', $args);
    }
}
