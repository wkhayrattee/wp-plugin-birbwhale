<?php

/**
 * View: BirbWhale dashboard (the app-shell home). Inner content only — the shell
 * provides the frame, so use `.bw-section` (NOT `.wrap`).
 *
 * $args: settings_url, log_url, connectors_url, credentials_url (strings),
 *        ai_client_available, enabled, has_key (bool), ai_client_version (string)
 *
 * @package BirbWhale
 */

defined('ABSPATH') || exit;

$args = wp_parse_args($args, [
    'settings_url'        => '',
    'log_url'             => '',
    'connectors_url'      => '',
    'credentials_url'     => '',
    'ai_client_available' => false,
    'ai_client_version'   => '',
    'enabled'             => true,
    'has_key'             => false,
]);

$ready = $args['ai_client_available'] && $args['enabled'] && $args['has_key'];
?>
<div class="bw-section">
    <h2 class="bw-section__title"><?php esc_html_e('Dashboard', 'birbwhale'); ?></h2>
    <p class="bw-section__lead">
        <?php esc_html_e('BirbWhale registers DeepSeek with the WordPress AI Client. The API key is managed by WordPress on the Connectors screen.', 'birbwhale'); ?>
    </p>

    <div class="bw-callout <?php echo $ready ? 'is-ok' : 'is-todo'; ?>">
        <span class="dashicons <?php echo $ready ? 'dashicons-yes-alt' : 'dashicons-info'; ?>" aria-hidden="true"></span>
        <span>
            <?php
            if ($ready) {
                esc_html_e('DeepSeek is connected and ready to use.', 'birbwhale');
            } elseif (!$args['ai_client_available']) {
                esc_html_e('The WordPress AI Client was not found — BirbWhale needs WordPress 7.0 or newer.', 'birbwhale');
            } elseif (!$args['enabled']) {
                esc_html_e('The connector is disabled. Enable it in Settings to register DeepSeek.', 'birbwhale');
            } else {
                esc_html_e('Almost there — add your DeepSeek API key on the Connectors screen.', 'birbwhale');
            }
            ?>
        </span>
    </div>

    <div class="bw-cards">
        <div class="bw-card">
            <h3><?php esc_html_e('Connector', 'birbwhale'); ?></h3>
            <p class="bw-status <?php echo $args['enabled'] ? 'is-ok' : 'is-todo'; ?>">
                <?php echo $args['enabled'] ? esc_html__('Enabled', 'birbwhale') : esc_html__('Disabled', 'birbwhale'); ?>
            </p>
            <a href="<?php echo esc_url($args['settings_url']); ?>"><?php esc_html_e('Open settings', 'birbwhale'); ?> &rarr;</a>
        </div>
        <div class="bw-card">
            <h3><?php esc_html_e('API key', 'birbwhale'); ?></h3>
            <p class="bw-status <?php echo $args['has_key'] ? 'is-ok' : 'is-todo'; ?>">
                <?php echo $args['has_key'] ? esc_html__('Set', 'birbwhale') : esc_html__('Not set', 'birbwhale'); ?>
            </p>
            <a href="<?php echo esc_url($args['connectors_url']); ?>"><?php esc_html_e('Connectors screen', 'birbwhale'); ?> &rarr;</a>
        </div>
        <div class="bw-card">
            <h3><?php esc_html_e('AI Client', 'birbwhale'); ?></h3>
            <p class="bw-status <?php echo $args['ai_client_available'] ? 'is-ok' : 'is-todo'; ?>">
                <?php
                echo $args['ai_client_available']
                    ? esc_html(sprintf(/* translators: %s: version */ __('Detected (v%s)', 'birbwhale'), $args['ai_client_version']))
                    : esc_html__('Not found', 'birbwhale');
                ?>
            </p>
            <a href="<?php echo esc_url($args['log_url']); ?>"><?php esc_html_e('View log', 'birbwhale'); ?> &rarr;</a>
        </div>
    </div>
</div>
