<?php

/**
 * BirbWhale uninstall handler.
 *
 * Runs when the plugin is deleted. Removes all data BirbWhale created —
 * including the DeepSeek API key that only exists because BirbWhale registered
 * the DeepSeek provider.
 *
 * @package BirbWhale
 */

// Only ever run as a genuine WordPress uninstall.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Plugin's own options.
delete_option('birbwhale_settings');
delete_option('birbwhale'); // activation seed, if it somehow survived.

/*
 * The DeepSeek API key is stored by WordPress core as `connectors_ai_deepseek_api_key`,
 * but it only exists because BirbWhale registered the DeepSeek provider. Clean it up.
 */
$birbwhale_options = ['connectors_ai_deepseek_api_key'];

/**
 * Filters the list of option names BirbWhale deletes on uninstall.
 *
 * @since 1.0.0
 *
 * @param string[] $options Option names to delete.
 */
$birbwhale_options = apply_filters('birbwhale_uninstall_options', $birbwhale_options);

foreach ($birbwhale_options as $birbwhale_option) {
    delete_option($birbwhale_option);
}

// Remove any of our transients.
global $wpdb;
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_birbwhale_') . '%',
        $wpdb->esc_like('_transient_timeout_birbwhale_') . '%'
    )
);

// Delete the log file.
$birbwhale_log_file = WP_CONTENT_DIR . '/birbwhale-error.log';
if (file_exists($birbwhale_log_file)) {
    if (function_exists('wp_delete_file')) {
        wp_delete_file($birbwhale_log_file);
    } else {
        @unlink($birbwhale_log_file);
    }
}
