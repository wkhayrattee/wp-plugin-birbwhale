<?php

/**
 * View: BirbWhale Log section (inner content only — the app shell wraps it).
 *
 * $args:
 *   - error_msg    (string) empty if none
 *   - txtlog_value (string) log content or status message
 *   - max_lines    (int)
 *
 * @package BirbWhale
 */

defined('ABSPATH') || exit;

$defaults = [
    'error_msg'    => '',
    'txtlog_value' => '',
    'max_lines'    => 100,
];
$args = wp_parse_args($args, $defaults);
?>
<div class="bw-section">
    <h2 class="bw-section__title"><?php esc_html_e('Log', 'birbwhale'); ?></h2>
    <p class="bw-section__lead">
        <?php
        printf(
            /* translators: %d: number of log entries shown. */
            esc_html__('Use the error log below to spot issues. Only the latest %d entries are displayed.', 'birbwhale'),
            (int) $args['max_lines']
        );
        ?>
    </p>

    <form name="birbwhale_log_form" action="" method="post">
        <?php wp_nonce_field(\BirbWhale\Core\Enum::NONCE_CLEAR_LOG); ?>

        <?php if (!empty($args['error_msg'])) : ?>
            <div class="notice notice-info inline"><p><?php echo esc_html($args['error_msg']); ?></p></div>
        <?php endif; ?>

        <p>
            <textarea
                name="txtlog"
                class="bw-log large-text code"
                style="height:430px;"
                wrap="off"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                readonly
            ><?php echo esc_textarea($args['txtlog_value']); ?></textarea>
        </p>

        <p>
            <input type="submit" class="button button-primary" name="clearlog_btn" value="<?php echo esc_attr__('Clear log', 'birbwhale'); ?>" />
        </p>
    </form>
</div>
