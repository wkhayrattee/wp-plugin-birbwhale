<?php

/**
 * PHPUnit bootstrap for BirbWhale unit tests.
 *
 * Loads the plugin's Composer autoloader (BirbWhale\*) and the WordPress AI Client
 * SDK, plus the minimal WordPress function shims the unit-tested classes touch.
 * Unit tests run WITHOUT a full WordPress bootstrap.
 *
 * @package BirbWhale\Tests
 */

declare(strict_types=1);

error_reporting(E_ALL);

if (!defined('ABSPATH')) {
    define('ABSPATH', rtrim(sys_get_temp_dir(), '/') . '/');
}

// Minimal WordPress shims (no WP loaded in unit tests).
if (!function_exists('__')) {
    function __($text, $domain = null)
    {
        return $text;
    }
}
if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args)
    {
        return $value;
    }
}
if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return $text;
    }
}

$pluginDir = dirname(__DIR__);

// 1) Plugin autoloader (BirbWhale\* classes).
$autoload = $pluginDir . '/includes/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Plugin autoloader missing — run: composer dump-autoload --optimize\n");
    exit(1);
}
require $autoload;

// 2) WordPress AI Client SDK. Prefer a Composer copy; otherwise load a local
//    WordPress-core copy. Override the path with BIRBWHALE_SDK_AUTOLOAD.
if (!class_exists(\WordPress\AiClient\AiClient::class)) {
    $candidates = array_filter([
        getenv('BIRBWHALE_SDK_AUTOLOAD') ?: null,
        // Default workspace layout: ../wordpress/wp-includes/php-ai-client/
        $pluginDir . '/../wordpress/wp-includes/php-ai-client/autoload.php',
    ]);
    foreach ($candidates as $candidate) {
        if (is_file((string) $candidate)) {
            require $candidate;
            break;
        }
    }
}
if (!class_exists(\WordPress\AiClient\AiClient::class)) {
    fwrite(
        STDERR,
        "WordPress AI Client SDK not found.\n"
        . "Set BIRBWHALE_SDK_AUTOLOAD to its autoload.php, or `composer require --dev wordpress/php-ai-client`.\n"
    );
    exit(1);
}
