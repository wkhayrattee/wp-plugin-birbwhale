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

// Real text generation through DeepSeek. A unique nonce in the prompt bypasses
// the AI Client cache, so this ALWAYS hits the live API — and we assert that
// DeepSeek reported real token usage (proof it wasn't served from cache).
try {
    $bw_nonce = 'BW-' . substr(md5(uniqid('', true)), 0, 12);
    $bw_t0    = microtime(true);
    $bw_res   = \WordPress\AiClient\AiClient::prompt("Reply with only this exact token and nothing else: {$bw_nonce}")
        ->usingProvider('deepseek')
        ->generateTextResult();
    $bw_ms     = (int) round((microtime(true) - $bw_t0) * 1000);
    $bw_reply  = trim($bw_res->toText());
    $bw_tokens = $bw_res->getTokenUsage()->getTotalTokens();
    $bw_ck(
        'Text generation returns a non-empty reply',
        $bw_reply !== '',
        "{$bw_ms} ms, model {$bw_res->getModelMetadata()->getId()}: {$bw_reply}"
    );
    $bw_ck(
        'DeepSeek reported real token usage (live API hit, not cached)',
        $bw_tokens > 0,
        "{$bw_tokens} tokens"
    );
} catch (\Throwable $e) {
    $bw_ck('Text generation returns a non-empty reply', false, get_class($e) . ': ' . $e->getMessage());
}

echo "\nLIVE RESULT: {$bw_pass} passed, {$bw_fail} failed\n";

if ($bw_fail > 0 && class_exists('WP_CLI')) {
    WP_CLI::halt(1);
}
