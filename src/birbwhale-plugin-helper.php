<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!function_exists('birbwhale_log')) {
    /**
     * Global shorthand for BirbWhale's file logger.
     *
     * @since 1.0.0
     *
     * @param string $message The message to log.
     * @param string $level   One of BirbWhale\Core\Enum::LOG_* levels.
     */
    function birbwhale_log(string $message, string $level = 'ERROR'): void
    {
        \BirbWhale\Core\Utils::log($message, $level);
    }
}
