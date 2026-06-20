<?php

declare(strict_types=1);

namespace BirbWhale\Core;

defined('ABSPATH') || exit;

/**
 * Centralized constants for BirbWhale.
 *
 * @since 1.0.0
 */
class Enum
{
    // Plugin identity.
    public const PLUGIN_KEY = 'birbwhale';
    public const PLUGIN_VERSION = '1.0.0';
    public const TEXT_DOMAIN = 'birbwhale';

    // The provider this plugin connects. Matches the AI Client provider id.
    public const PROVIDER_ID = 'deepseek';
    // WordPress core stores the key under this option (connectors_ai_{id}_api_key).
    public const PROVIDER_KEY_OPTION = 'connectors_ai_deepseek_api_key';
    public const PROVIDER_CREDENTIALS_URL = 'https://platform.deepseek.com/api_keys';

    // Capabilities.
    public const ADMIN_CAPABILITY = 'manage_options';

    // Admin page slugs.
    public const MENU_SLUG_SETTINGS = 'birbwhale';
    public const MENU_SLUG_LOG = 'birbwhale-log';

    // Settings API.
    public const SETTINGS_OPTION_GROUP = 'birbwhale_settings_group';
    public const SETTINGS_OPTION_NAME = 'birbwhale_settings';
    public const SETTINGS_SECTION = 'birbwhale_general_section';

    // Settings fields.
    public const FIELD_ENABLED = 'enabled';

    // Nonce actions.
    public const NONCE_CLEAR_LOG = 'birbwhale_clear_log';

    // Log levels.
    public const LOG_INFO = 'INFO';
    public const LOG_ERROR = 'ERROR';
    public const LOG_WARNING = 'WARNING';

    /**
     * Priority at which we register the provider with the AI Client.
     *
     * WordPress core discovers connectors on `init` at priority 15
     * (see wp-includes/connectors.php). We register earlier so DeepSeek is
     * present when core builds the Connectors screen.
     */
    public const PROVIDER_REGISTER_PRIORITY = 5;
}
