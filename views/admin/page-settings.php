<?php

/**
 * View: BirbWhale Settings section (inner content only — the app shell wraps it).
 *
 * $args:
 *   - option_group        (string) Settings API group
 *   - settings_page       (string) page id for do_settings_sections()
 *   - connectors_url      (string) URL to Settings > Connectors
 *   - credentials_url     (string) URL to get a DeepSeek API key
 *   - ai_client_available (bool)
 *   - ai_client_version   (string)
 *   - enabled             (bool)
 *   - has_key             (bool)
 *
 * @package BirbWhale
 */

defined('ABSPATH') || exit;

$defaults = [
    'option_group'        => '',
    'settings_page'       => '',
    'connectors_url'      => '',
    'credentials_url'     => '',
    'ai_client_available' => false,
    'ai_client_version'   => '',
    'enabled'             => true,
    'has_key'             => false,
];
$args = wp_parse_args($args, $defaults);
?>
<div class="bw-section">
    <h2 class="bw-section__title"><?php esc_html_e('Settings', 'birbwhale'); ?></h2>
    <p class="bw-section__lead">
        <?php esc_html_e('Enable or disable the DeepSeek connector and check its status. The API key lives on the Connectors screen.', 'birbwhale'); ?>
    </p>

    <ul class="bw-statuslist">
        <li class="<?php echo $args['ai_client_available'] ? 'is-ok' : 'is-error'; ?>">
            <span class="dashicons <?php echo $args['ai_client_available'] ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>" aria-hidden="true"></span>
            <?php
            if ($args['ai_client_available']) {
                printf(
                    /* translators: %s: AI Client version. */
                    esc_html__('WordPress AI Client detected (v%s).', 'birbwhale'),
                    esc_html($args['ai_client_version'])
                );
            } else {
                esc_html_e('WordPress AI Client not found — BirbWhale requires WordPress 7.0 or newer.', 'birbwhale');
            }
            ?>
        </li>
        <li class="<?php echo $args['enabled'] ? 'is-ok' : 'is-todo'; ?>">
            <span class="dashicons <?php echo $args['enabled'] ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>" aria-hidden="true"></span>
            <?php echo $args['enabled'] ? esc_html__('Connector enabled.', 'birbwhale') : esc_html__('Connector disabled.', 'birbwhale'); ?>
        </li>
        <li class="<?php echo $args['has_key'] ? 'is-ok' : 'is-todo'; ?>">
            <span class="dashicons <?php echo $args['has_key'] ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>" aria-hidden="true"></span>
            <?php echo $args['has_key'] ? esc_html__('DeepSeek API key is set.', 'birbwhale') : esc_html__('No DeepSeek API key yet.', 'birbwhale'); ?>
        </li>
    </ul>

    <div class="bw-card">
        <form action="options.php" method="post">
            <?php
            settings_fields($args['option_group']);
            do_settings_sections($args['settings_page']);
            submit_button(__('Save changes', 'birbwhale'));
            ?>
        </form>
    </div>

    <h3 class="bw-subhead"><?php esc_html_e('API key', 'birbwhale'); ?></h3>
    <p class="bw-section__lead">
        <?php esc_html_e('The DeepSeek API key is stored by WordPress and shared across plugins. Add or update it on the Connectors screen.', 'birbwhale'); ?>
    </p>
    <p class="bw-actions">
        <a class="button button-secondary" href="<?php echo esc_url($args['connectors_url']); ?>">
            <?php esc_html_e('Open Settings → Connectors', 'birbwhale'); ?>
        </a>
        <?php if (!empty($args['credentials_url'])) : ?>
            <a class="button button-secondary" href="<?php echo esc_url($args['credentials_url']); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Get a DeepSeek API key', 'birbwhale'); ?>
            </a>
        <?php endif; ?>
    </p>
</div>
