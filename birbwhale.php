<?php

/**
 * Plugin Name:       BirbWhale
 * Plugin URI:        https://wordpress.org/plugins/birbwhale/
 * Description:       DeepSeek AI connector for the WordPress AI Client — adds DeepSeek text generation and reasoning to WordPress.
 * Version:           1.0.0
 * Requires at least: 7.0
 * Requires PHP:      8.4
 * Author:            Wasseem Khayrattee
 * Author URI:        https://github.com/wkhayrattee
 * License:           GPL-3.0-only
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       birbwhale
 * Domain Path:       /languages
 *
 * @package BirbWhale
 */

declare(strict_types=1);

// Prevent direct file access.
if (!function_exists('add_action')) {
    exit;
}

// Plugin constants.
define('BIRBWHALE_VERSION', '1.0.0');
define('BIRBWHALE_DIR', plugin_dir_path(__FILE__));
define('BIRBWHALE_URL', plugin_dir_url(__FILE__));
define('BIRBWHALE_VIEWS', BIRBWHALE_DIR . 'views/');
define('BIRBWHALE_BASENAME', plugin_basename(__FILE__));
define('BIRBWHALE_ERROR_LOG_FILE', WP_CONTENT_DIR . '/birbwhale-error.log');

// Cache nonce for asset versioning (cache-busting on version change).
if (!defined('BIRBWHALE_CACHE_NONCE')) {
    define('BIRBWHALE_CACHE_NONCE', BIRBWHALE_VERSION);
}

// Fatal error shutdown handler — catches fatal errors originating from this plugin only.
register_shutdown_function(static function () {
    $error = error_get_last();

    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!defined('BIRBWHALE_DIR') || mb_strpos($error['file'], BIRBWHALE_DIR) !== 0) {
            return;
        }

        $message = sprintf(
            '[Fatal Error] %s in %s on line %d',
            $error['message'],
            $error['file'],
            $error['line']
        );

        if (defined('BIRBWHALE_ERROR_LOG_FILE')) {
            @file_put_contents(
                BIRBWHALE_ERROR_LOG_FILE,
                gmdate('c') . ' ' . $message . PHP_EOL,
                FILE_APPEND
            );
        }
    }
});

// Composer autoloader.
$birbwhale_autoload = BIRBWHALE_DIR . 'includes/vendor/autoload.php';

if (!is_readable($birbwhale_autoload)) {
    // Autoloader missing — most likely `composer dump-autoload` was never run.
    add_action('admin_notices', static function () {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        echo '<div class="notice notice-error"><p>';
        echo esc_html__(
            'BirbWhale could not load its autoloader. Run "composer dump-autoload --optimize" inside the plugin folder.',
            'birbwhale'
        );
        echo '</p></div>';
    });

    return;
}

require_once $birbwhale_autoload;

// Lifecycle hooks.
register_activation_hook(__FILE__, ['BirbWhale\\Core\\PluginManager', 'activate']);
register_deactivation_hook(__FILE__, ['BirbWhale\\Core\\PluginManager', 'deactivate']);

// Boot the orchestrator. It registers the DeepSeek provider (front-end + admin) and the admin UI.
add_action('plugins_loaded', ['BirbWhale\\Core\\PluginManager', 'boot']);
