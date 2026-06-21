<?php

/**
 * BirbWhale — WordPress integration smoke test (no network).
 *
 * Run against a live WordPress install with the plugin active:
 *   wp eval-file tests/wp-cli/smoke.php
 *
 * Verifies the plugin's WordPress wiring: provider registration, the core
 * Connectors card, the admin shell views, and the bundled assets. Prints a
 * PASS/FAIL line per check and a final SMOKE RESULT line (non-zero exit on
 * failure via WP_CLI::halt).
 *
 * @package BirbWhale\Tests
 */

if (!defined('ABSPATH')) {
    exit;
}

$bw_pass = 0;
$bw_fail = 0;
$bw_ck = static function (string $label, bool $cond, string $detail = '') use (&$bw_pass, &$bw_fail): void {
    if ($cond) {
        echo "  \xE2\x9C\x93 {$label}\n";
        $bw_pass++;
    } else {
        echo "  \xE2\x9C\x97 {$label}" . ($detail !== '' ? " ({$detail})" : '') . "\n";
        $bw_fail++;
    }
};

$registry = \WordPress\AiClient\AiClient::defaultRegistry();

// Provider registration.
$bw_ck('DeepSeek provider registered with the AI Client', $registry->hasProvider('deepseek'));
$bw_ck(
    'Registered provider is BirbWhale\\Provider\\DeepSeekProvider',
    $registry->hasProvider('deepseek') && $registry->getProviderClassName('deepseek') === \BirbWhale\Provider\DeepSeekProvider::class
);

// Core Connectors discovery + logo resolution.
if (function_exists('wp_get_connectors')) {
    $connectors = wp_get_connectors();
    $bw_ck('DeepSeek appears on Settings → Connectors', isset($connectors['deepseek']));

    $logo = \BirbWhale\Provider\DeepSeekProvider::metadata()->getLogoPath();
    $url  = function_exists('_wp_connectors_resolve_ai_provider_logo_url')
        ? _wp_connectors_resolve_ai_provider_logo_url($logo)
        : null;
    $bw_ck('Connector logo resolves to a URL', !empty($url), (string) $url);
} else {
    echo "  ! wp_get_connectors() unavailable (WordPress < 7.0?) — skipping Connectors checks\n";
}

// Settings default.
$bw_ck('Connector enabled by default', \BirbWhale\Core\PluginManager::isEnabled());

// Admin shell renders every section (needs an admin user for the capability check).
$bw_admins = get_users(['role' => 'administrator', 'number' => 1]);
if ($bw_admins) {
    wp_set_current_user($bw_admins[0]->ID);
    foreach (['dashboard', 'settings', 'log', 'help'] as $bw_view) {
        $_GET['view'] = $bw_view;
        ob_start();
        \BirbWhale\Admin\Shell::render();
        $bw_html = (string) ob_get_clean();
        $bw_ck("Shell renders the '{$bw_view}' view", str_contains($bw_html, 'bw-shell__bar') && str_contains($bw_html, 'bw-shell__nav'));
    }
    unset($_GET['view']);
} else {
    echo "  ! No administrator user found — skipping shell render checks\n";
}

// Bundled assets present.
$bw_assets = WP_PLUGIN_DIR . '/birbwhale/assets/images/';
foreach (['icon.svg', 'icon-menu.svg', 'deepseek.svg', 'banner-1544x500.svg'] as $bw_asset) {
    $bw_ck("Asset present: {$bw_asset}", is_file($bw_assets . $bw_asset));
}

echo "\nSMOKE RESULT: {$bw_pass} passed, {$bw_fail} failed\n";

if ($bw_fail > 0 && class_exists('WP_CLI')) {
    WP_CLI::halt(1);
}
