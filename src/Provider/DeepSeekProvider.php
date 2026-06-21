<?php

declare(strict_types=1);

namespace BirbWhale\Provider;

use BirbWhale\Metadata\DeepSeekModelMetadataDirectory;
use BirbWhale\Models\DeepSeekTextGenerationModel;
use WordPress\AiClient\AiClient;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\ApiBasedImplementation\ListModelsApiBasedProviderAvailability;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

defined('ABSPATH') || exit;

/**
 * The DeepSeek provider for the WordPress AI Client.
 *
 * DeepSeek exposes an OpenAI-compatible REST API, so the heavy lifting lives in
 * the SDK's OpenAI-compatible base classes; this provider only declares its
 * identity, base URL, and which model classes back each capability.
 *
 * @since 1.0.0
 */
class DeepSeekProvider extends AbstractApiProvider
{
    /**
     * {@inheritDoc}
     *
     * DeepSeek's OpenAI-compatible base. The SDK appends `chat/completions` and
     * `models` to this, matching DeepSeek's endpoints.
     *
     * @since 1.0.0
     */
    protected static function baseUrl(): string
    {
        return 'https://api.deepseek.com';
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function createModel(
        ModelMetadata $modelMetadata,
        ProviderMetadata $providerMetadata
    ): ModelInterface {
        foreach ($modelMetadata->getSupportedCapabilities() as $capability) {
            if ($capability->isTextGeneration()) {
                return new DeepSeekTextGenerationModel($modelMetadata, $providerMetadata);
            }
        }

        throw new RuntimeException(
            sprintf('Unsupported DeepSeek model capabilities for model "%s".', esc_html($modelMetadata->getId()))
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function createProviderMetadata(): ProviderMetadata
    {
        $args = [
            'deepseek',
            'DeepSeek',
            ProviderTypeEnum::cloud(),
            'https://platform.deepseek.com/api_keys',
            RequestAuthenticationMethod::apiKey(),
        ];

        // Provider description support was added in AI Client 1.2.0.
        if (version_compare(AiClient::VERSION, '1.2.0', '>=')) {
            $args[] = function_exists('__')
                ? __('Text generation and reasoning with DeepSeek.', 'birbwhale')
                : 'Text generation and reasoning with DeepSeek.';
        }

        // Provider logoPath support was added in AI Client 1.3.0.
        if (version_compare(AiClient::VERSION, '1.3.0', '>=')) {
            $args[] = self::logoPath();
        }

        return new ProviderMetadata(...$args);
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function createProviderAvailability(): ProviderAvailabilityInterface
    {
        // Considered "configured" once a valid key lets us list models.
        return new ListModelsApiBasedProviderAvailability(
            static::modelMetadataDirectory()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface
    {
        return new DeepSeekModelMetadataDirectory();
    }

    /**
     * Absolute path to the DeepSeek provider logo, as WordPress core expects it.
     *
     * This is DeepSeek's own logo, shown on the Settings → Connectors card to
     * identify the provider (as the official AI provider connectors do for theirs).
     * BirbWhale's own branding (menu/header/directory icon) uses original art.
     *
     * WordPress core only converts a provider logo path to a URL when the path
     * string lives under WP_PLUGIN_DIR (see
     * `_wp_connectors_resolve_ai_provider_logo_url()` in wp-includes/connectors.php).
     * When the plugin is symlinked into wp-content/plugins, `__DIR__` resolves to
     * the real (symlink target) path, which sits outside WP_PLUGIN_DIR and would
     * be rejected — leaving the connector card with a generic fallback icon. So,
     * inside WordPress, build the path from WP_PLUGIN_DIR + the plugin's basename
     * (which {@see plugin_basename()} reports symlink-aware). Fall back to the real
     * path for standalone (non-WordPress) use of this provider.
     *
     * @since 1.0.0
     *
     * @return string Absolute path to the logo SVG.
     */
    private static function logoPath(): string
    {
        $relative = 'assets/images/deepseek.svg';

        if (
            defined('WP_PLUGIN_DIR')
            && defined('BIRBWHALE_BASENAME')
            && function_exists('wp_normalize_path')
        ) {
            $candidate = wp_normalize_path(WP_PLUGIN_DIR)
                . '/' . dirname(BIRBWHALE_BASENAME)
                . '/' . $relative;

            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return dirname(__DIR__, 2) . '/' . $relative;
    }
}
