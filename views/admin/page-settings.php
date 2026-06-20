<?php

/**
 * View: BirbWhale settings / status page.
 *
 * Expected $args:
 *   - page_title          (string)
 *   - option_group        (string) Settings API group
 *   - menu_slug           (string) page slug for do_settings_sections()
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
    'page_title'          => 'BirbWhale',
    'option_group'        => '',
    'menu_slug'           => '',
    'connectors_url'      => '',
    'credentials_url'     => '',
    'ai_client_available' => false,
    'ai_client_version'   => '',
    'enabled'             => true,
    'has_key'             => false,
];
$args = wp_parse_args($args, $defaults);
?>
<div class="wrap birbwhale-wrap">
    <h1><?php echo esc_html($args['page_title']); ?></h1>

    <p class="birbwhale-intro">
        <?php
        esc_html_e(
            'BirbWhale registers DeepSeek as a provider for the WordPress AI Client. The API key is managed by WordPress on the Connectors screen.',
            'birbwhale'
        );
        ?>
    </p>

    <h2 class="screen-reader-text"><?php esc_html_e('Status', 'birbwhale'); ?></h2>
    <ul class="birbwhale-status">
        <li class="<?php echo $args['ai_client_available'] ? 'is-ok' : 'is-error'; ?>">
            <span class="dashicons <?php echo $args['ai_client_available'] ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
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
        <li class="<?php echo $args['enabled'] ? 'is-ok' : 'is-warn'; ?>">
            <span class="dashicons <?php echo $args['enabled'] ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>"></span>
            <?php
            echo $args['enabled']
                ? esc_html__('Connector enabled.', 'birbwhale')
                : esc_html__('Connector disabled.', 'birbwhale');
            ?>
        </li>
        <li class="<?php echo $args['has_key'] ? 'is-ok' : 'is-warn'; ?>">
            <span class="dashicons <?php echo $args['has_key'] ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>"></span>
            <?php
            echo $args['has_key']
                ? esc_html__('DeepSeek API key is set.', 'birbwhale')
                : esc_html__('No DeepSeek API key yet.', 'birbwhale');
            ?>
        </li>
    </ul>

    <form action="options.php" method="post">
        <?php
        settings_fields($args['option_group']);
        do_settings_sections($args['menu_slug']);
        submit_button(__('Save changes', 'birbwhale'));
        ?>
    </form>

    <hr />

    <h2><?php esc_html_e('API key', 'birbwhale'); ?></h2>
    <p>
        <?php
        esc_html_e(
            'The DeepSeek API key is stored by WordPress and shared across plugins. Add or update it on the Connectors screen.',
            'birbwhale'
        );
        ?>
    </p>
    <p>
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
