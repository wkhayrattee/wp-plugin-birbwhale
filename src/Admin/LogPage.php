<?php

declare(strict_types=1);

namespace BirbWhale\Admin;

use BirbWhale\Core\Enum;
use BirbWhale\Core\Utils;

defined('ABSPATH') || exit;

/**
 * Admin error-log page.
 *
 * Reads only the tail of the log via fseek() (O(1) memory), and clears the log
 * behind a nonce + capability check.
 *
 * @since 1.0.0
 */
class LogPage
{
    /**
     * Render the log page.
     *
     * @since 1.0.0
     */
    public static function render(): void
    {
        $capability = apply_filters('birbwhale_admin_capability', Enum::ADMIN_CAPABILITY);
        if (!current_user_can($capability)) {
            return;
        }

        global $title;

        $log_file  = apply_filters('birbwhale_log_file_path', BIRBWHALE_ERROR_LOG_FILE);
        $error_msg = '';

        if (isset($_POST['clearlog_btn'])) {
            check_admin_referer(Enum::NONCE_CLEAR_LOG);
            $error_msg = self::clearErrorLog($log_file);

            /**
             * Fires after the BirbWhale log has been cleared.
             *
             * @since 1.0.0
             */
            do_action('birbwhale_log_cleared');
        }

        Utils::loadView('admin/page-log.php', [
            'admin_page_title' => $title ?: __('BirbWhale Log', 'birbwhale'),
            'error_msg'        => $error_msg,
            'txtlog_value'     => self::fetchLogData($log_file),
            'max_lines'        => self::maxLines(),
        ]);
    }

    /**
     * Number of log lines to display.
     *
     * @since 1.0.0
     */
    private static function maxLines(): int
    {
        /**
         * Filters how many log lines the admin log page displays.
         *
         * @since 1.0.0
         *
         * @param int $max_lines Maximum lines.
         */
        return (int) apply_filters('birbwhale_log_max_lines', 100);
    }

    /**
     * Read the tail of the log file efficiently.
     *
     * @since 1.0.0
     *
     * @param string $log_file_path Absolute path to the log file.
     * @return string Log content or a status message.
     */
    public static function fetchLogData(string $log_file_path): string
    {
        $max_lines  = self::maxLines();
        $tail_bytes = 467000; // ~456KB — comfortably more than 100 entries.

        if (!file_exists($log_file_path)) {
            return __('The log is empty.', 'birbwhale');
        }

        if (!is_writable($log_file_path)) {
            return __('[NOTICE] The log file is not writable.', 'birbwhale');
        }

        $file_size = filesize($log_file_path);
        if (0 === $file_size) {
            return __('The log is empty.', 'birbwhale');
        }

        $fp = fopen($log_file_path, 'r');
        if (false === $fp) {
            return __('Unable to open the log file for reading.', 'birbwhale');
        }

        $read_bytes = (int) min($file_size, $tail_bytes);
        fseek($fp, -$read_bytes, SEEK_END);
        $tail = fread($fp, $read_bytes);
        fclose($fp);

        if (false === $tail) {
            return __('Unable to read the log file.', 'birbwhale');
        }

        $lines = array_filter(explode("\n", $tail), static fn ($line) => '' !== $line);
        if (0 === count($lines)) {
            return __('The log is empty.', 'birbwhale');
        }

        return implode("\n", array_slice($lines, -$max_lines)) . "\n";
    }

    /**
     * Delete the log file.
     *
     * @since 1.0.0
     *
     * @param string $log_file_path Absolute path to the log file.
     * @return string Status message.
     */
    public static function clearErrorLog(string $log_file_path): string
    {
        if (!file_exists($log_file_path)) {
            return __('The log is already empty.', 'birbwhale');
        }

        if (!is_writable($log_file_path)) {
            return __('[NOTICE] The log file is not writable.', 'birbwhale');
        }

        wp_delete_file($log_file_path);

        return __('[done] The log was cleared.', 'birbwhale');
    }
}
