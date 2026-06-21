<?php

/**
 * BirbWhale — live DeepSeek end-to-end test (needs a saved API key + network).
 *
 * This makes REAL (small) calls to the DeepSeek API using the key stored on
 * Settings → Connectors. Run it deliberately:
 *   wp eval-file tests/wp-cli/live-deepseek.php
 *
 * Checks: key stored → core passes it to the SDK → live /models call →
 * a real text generation through DeepSeek → response parsed back.
 *
 * @package BirbWhale\Tests
 */

if (!defined('ABSPATH')) {
    exit;
}

$bw_pass = 0;
$bw_fail = 0;
$bw_ck = static function (string $label, bool $cond, string $detail = '') use (&$bw_pass, &$bw_fail): void {
    if ($cond) {
        echo "  \xE2\x9C\x93 {$label}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
        $bw_pass++;
    } else {
        echo "  \xE2\x9C\x97 {$label}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
        $bw_fail++;
    }
};

$key = (string) get_option('connectors_ai_deepseek_api_key', '');
$bw_ck('DeepSeek API key is stored', $key !== '', $key !== '' ? substr($key, 0, 4) . '…' . substr($key, -3) : 'set it on Settings → Connectors');

if ($key === '') {
    echo "\nLIVE RESULT: skipped (no API key)\n";
    return;
}

// Live auth + connectivity (real GET /models with the key).
try {
    $bw_ck('Provider is configured (live /models call)', \WordPress\AiClient\AiClient::isConfigured('deepseek'));
} catch (\Throwable $e) {
    $bw_ck('Provider is configured (live /models call)', false, get_class($e) . ': ' . $e->getMessage());
}

// Real text generation through DeepSeek.
try {
    $bw_t0   = microtime(true);
    $bw_text = \WordPress\AiClient\AiClient::prompt('Reply with exactly these three words and nothing else: BirbWhale works now')
        ->usingProvider('deepseek')
        ->generateText();
    $bw_ms = (int) round((microtime(true) - $bw_t0) * 1000);
    $bw_ck('Text generation returns a non-empty reply', trim((string) $bw_text) !== '', "{$bw_ms} ms: " . trim((string) $bw_text));
} catch (\Throwable $e) {
    $bw_ck('Text generation returns a non-empty reply', false, get_class($e) . ': ' . $e->getMessage());
}

echo "\nLIVE RESULT: {$bw_pass} passed, {$bw_fail} failed\n";

if ($bw_fail > 0 && class_exists('WP_CLI')) {
    WP_CLI::halt(1);
}
