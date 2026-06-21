<?php

/**
 * View: BirbWhale "Get Help" section (inner content only — the app shell wraps it).
 *
 * $args:
 *   - github_url (string) URL to the GitHub issues page
 *   - email      (string) support email address
 *   - mailto     (string) mailto: link with a pre-filled subject
 *
 * @package BirbWhale
 */

defined('ABSPATH') || exit;

$defaults = [
    'github_url' => '',
    'email'      => '',
    'mailto'     => '',
];
$args = wp_parse_args($args, $defaults);
?>
<div class="bw-section">
    <h2 class="bw-section__title"><?php esc_html_e('Get Help', 'birbwhale'); ?></h2>
    <p class="bw-section__lead">
        <?php esc_html_e('Have a question, run into a snag, or just want to say hello? We are glad you are here. Pick whichever channel suits you below and we will be happy to help.', 'birbwhale'); ?>
    </p>

    <div class="bw-cards">
        <div class="bw-card">
            <h3><span class="dashicons dashicons-external" aria-hidden="true"></span> <?php esc_html_e('Open a GitHub issue', 'birbwhale'); ?></h3>
            <p class="bw-status">
                <?php esc_html_e('Report a bug or request a feature in the public issue tracker — best for anything reproducible.', 'birbwhale'); ?>
            </p>
            <a class="button button-secondary" href="<?php echo esc_url($args['github_url']); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Open an issue on GitHub', 'birbwhale'); ?>
            </a>
        </div>

        <div class="bw-card">
            <h3><span class="dashicons dashicons-email-alt" aria-hidden="true"></span> <?php esc_html_e('Email support', 'birbwhale'); ?></h3>
            <p class="bw-status">
                <?php
                printf(
                    /* translators: %s: support email address. */
                    esc_html__('Prefer email? Write to %s — the subject is pre-filled with your site address.', 'birbwhale'),
                    '<strong class="bw-email">' . esc_html($args['email']) . '</strong>'
                );
                ?>
            </p>
            <a class="button button-secondary" href="<?php echo esc_url($args['mailto'], ['mailto']); ?>">
                <?php esc_html_e('Email BirbWhale support', 'birbwhale'); ?>
            </a>
        </div>
    </div>

    <p class="bw-help-note"><strong><?php esc_html_e('Support language:', 'birbwhale'); ?></strong> English</p>
</div>
