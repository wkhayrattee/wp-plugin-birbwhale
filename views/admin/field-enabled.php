<?php

/**
 * View: "Enable connector" settings field.
 *
 * Expected $args:
 *   - field_id   (string) input id (matches label_for)
 *   - field_name (string) HTML name for the hidden+checkbox pair
 *   - enabled    (bool)   current value
 *
 * @package BirbWhale
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View loaded via load_template(); $args and locals are template-scoped, not globals.

$defaults = [
    'field_id'   => 'enabled',
    'field_name' => '',
    'enabled'    => true,
];
$args = wp_parse_args($args, $defaults);
?>
<label for="<?php echo esc_attr($args['field_id']); ?>">
    <input
        type="checkbox"
        id="<?php echo esc_attr($args['field_id']); ?>"
        name="<?php echo esc_attr($args['field_name']); ?>"
        value="on"
        <?php checked($args['enabled']); ?>
    />
    <?php esc_html_e('Register the DeepSeek provider with the WordPress AI Client.', 'birbwhale'); ?>
</label>
<p class="description">
    <?php esc_html_e('When disabled, DeepSeek is not registered and will not appear on the Connectors screen.', 'birbwhale'); ?>
</p>
