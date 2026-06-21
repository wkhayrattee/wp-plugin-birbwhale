<?php

declare(strict_types=1);

namespace BirbWhale\Core;

defined('ABSPATH') || exit;

/**
 * Shared helpers: file-based logging, log rotation, and template loading.
 *
 * @since 1.0.0
 */
class Utils
{
    /**
     * Append a line to the plugin's dedicated log file.
     *
     * @since 1.0.0
     *
     * @param string $message The message to log.
     * @param string $level   One of the Enum::LOG_* levels.
     */
    public static function log(string $message, string $level = Enum::LOG_ERROR): void
    {
        /**
         * Filters the log file path.
         *
         * @since 1.0.0
         *
         * @param string $log_file Absolute path to the log file.
         */
        $log_file = apply_filters('birbwhale_log_file_path', BIRBWHALE_ERROR_LOG_FILE);

        $log_dir = dirname($log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        // Rotate if the file exceeds 5MB.
        if (file_exists($log_file) && filesize($log_file) > 5 * 1024 * 1024) {
            self::rotateLog($log_file);
        }

        $timestamp = gmdate('Y-m-d H:i:s');
        $formatted = "[{$timestamp}] {$level}: {$message}" . PHP_EOL;

        if (false === @file_put_contents($log_file, $formatted, FILE_APPEND | LOCK_EX)) {
            error_log('[BirbWhale] ' . $level . ': ' . $message); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- last-resort fallback when writing the plugin's own log file fails.
        }
    }

    /**
     * Keep only the last ~1MB of the log, using fseek() for O(1) memory.
     *
     * @since 1.0.0
     *
     * @param string $log_file Absolute path to the log file.
     */
    public static function rotateLog(string $log_file): void
    {
        $keep_bytes = 1024 * 1024; // Keep last 1MB.

        // Direct filesystem access (phpcs:ignore below): reads the plugin's OWN log
        // file and uses fseek() for an O(1)-memory tail read, which the WP_Filesystem
        // API cannot do.
        $fp = fopen($log_file, 'r'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        if (false === $fp) {
            return;
        }

        $file_size = filesize($log_file);
        if ($file_size <= $keep_bytes) {
            fclose($fp); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
            return;
        }

        fseek($fp, $file_size - $keep_bytes, SEEK_SET);
        $tail = fread($fp, $keep_bytes); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
        fclose($fp); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

        if (false === $tail) {
            return;
        }

        // Drop the first (partial) line.
        $first_newline = strpos($tail, "\n");
        if (false !== $first_newline) {
            $tail = substr($tail, $first_newline + 1);
        }

        file_put_contents($log_file, $tail, LOCK_EX);
    }

    /**
     * Load an admin view template, passing data via $args.
     *
     * @since 1.0.0
     *
     * @param string               $template_file Relative path within views/, e.g. 'admin/page-settings.php'.
     * @param array<string, mixed> $args          Data extracted into the template scope.
     * @param bool                 $require_once  Whether to require_once the template.
     */
    public static function loadView(string $template_file, array $args = [], bool $require_once = false): void
    {
        $template_path = BIRBWHALE_VIEWS . $template_file;

        if (!file_exists($template_path)) {
            self::log("Template not found: {$template_path}", Enum::LOG_ERROR);
            return;
        }

        load_template($template_path, $require_once, $args);
    }
}
