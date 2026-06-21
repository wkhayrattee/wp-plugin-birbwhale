<?php

/**
 * View: the BirbWhale app shell (branded header bar + in-page sidebar + content
 * panel). Loaded via Utils::loadView('admin/shell.php', [...]) from Shell::renderPage().
 *
 * Uses `.wrap` so WordPress positions admin notices relative to it.
 *
 * $args:
 *   - active   (string) current nav key (a VIEW_* value)
 *   - brand    (string) brand name
 *   - tagline  (string) header tag pill text
 *   - mark_url (string) URL to the brand mark SVG
 *   - nav      (array)  sidebar model (mix of 'group' and 'item' rows)
 *   - content  (string) pre-rendered HTML for the active section
 *
 * @package BirbWhale
 */

defined('ABSPATH') || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- View loaded via load_template(); $args and locals are template-scoped, not globals.

$args = wp_parse_args($args, [
    'active'   => '',
    'brand'    => 'BirbWhale',
    'tagline'  => '',
    'mark_url' => '',
    'nav'      => [],
    'content'  => '',
]);
?>
<div class="wrap bw-shell">
    <div class="bw-shell__bar">
        <?php if (!empty($args['mark_url'])) : ?>
            <img class="bw-shell__mark" src="<?php echo esc_url($args['mark_url']); ?>" alt="" width="34" height="34" />
        <?php endif; ?>
        <span class="bw-shell__brand"><?php echo esc_html($args['brand']); ?></span>
        <?php if (!empty($args['tagline'])) : ?>
            <span class="bw-shell__tag"><?php echo esc_html($args['tagline']); ?></span>
        <?php endif; ?>
    </div>

    <div class="bw-shell__body">
        <nav class="bw-shell__nav" aria-label="<?php esc_attr_e('BirbWhale sections', 'birbwhale'); ?>">
            <ul>
                <?php foreach ($args['nav'] as $row) : ?>
                    <?php if (($row['type'] ?? '') === 'group') : ?>
                        <li class="bw-nav__group"><?php echo esc_html($row['label']); ?></li>
                    <?php else :
                        $is_active = ($row['key'] ?? '') === $args['active'];
                        $disabled  = !empty($row['disabled']);
                        $classes   = 'bw-nav__item'
                            . ($is_active ? ' is-active' : '')
                            . ($disabled ? ' is-disabled' : '');
                        ?>
                        <li>
                            <?php if ($disabled) : ?>
                                <span class="<?php echo esc_attr($classes); ?>" aria-disabled="true">
                                    <span class="dashicons <?php echo esc_attr($row['icon'] ?? 'dashicons-marker'); ?>" aria-hidden="true"></span>
                                    <span class="bw-nav__label"><?php echo esc_html($row['label']); ?></span>
                                    <?php if (!empty($row['badge'])) : ?>
                                        <span class="bw-nav__badge"><?php echo esc_html($row['badge']); ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php else : ?>
                                <a class="<?php echo esc_attr($classes); ?>" href="<?php echo esc_url($row['url'] ?? '#'); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                                    <span class="dashicons <?php echo esc_attr($row['icon'] ?? 'dashicons-marker'); ?>" aria-hidden="true"></span>
                                    <span class="bw-nav__label"><?php echo esc_html($row['label']); ?></span>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>

        <main class="bw-shell__content">
            <?php
            // Anchor for WordPress core/third-party admin notices: common.js moves
            // them to just before this marker (top of the content panel).
            ?>
            <hr class="wp-header-end" />
            <?php
            // $args['content'] is pre-rendered section markup; each section view escapes its own output.
            echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </main>
    </div>
</div>
