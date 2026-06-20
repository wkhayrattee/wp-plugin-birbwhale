<?php

declare(strict_types=1);

namespace BirbWhale\Admin;

use BirbWhale\Core\Enum;
use BirbWhale\Core\Utils;

defined('ABSPATH') || exit;

/**
 * "Get Help" section — points users at the available support channels
 * (GitHub issues and a support email). Static content; no data leaves the site.
 *
 * @since 1.0.0
 */
class HelpPage
{
    /**
     * Render the Help section's inner content (no chrome — the app shell wraps it).
     *
     * @since 1.0.0
     */
    public static function renderSection(): void
    {
        $subject = sprintf(
            /* translators: %s: site URL. */
            __('BirbWhale Help Request from: %s', 'birbwhale'),
            home_url()
        );

        Utils::loadView('admin/page-help.php', [
            'github_url' => Enum::SUPPORT_GITHUB_URL,
            'email'      => Enum::SUPPORT_EMAIL,
            'mailto'     => 'mailto:' . Enum::SUPPORT_EMAIL . '?subject=' . rawurlencode($subject),
        ]);
    }
}
