<?php

declare(strict_types=1);

namespace BirbWhale\Metadata;

use BirbWhale\Provider\DeepSeekProvider;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleModelMetadataDirectory;

defined('ABSPATH') || exit;

/**
 * Lists DeepSeek models via its OpenAI-compatible `GET /models` endpoint.
 *
 * Models are discovered dynamically — no model names are hard-coded — so current
 * and future DeepSeek models (e.g. `deepseek-v4-flash`, `deepseek-v4-pro`) appear
 * automatically. The endpoint does not report capabilities, so each text model is
 * registered with the full OpenAI-compatible chat option set; DeepSeek's
 * "thinking"/reasoning output is surfaced separately as a "thought" message part
 * by the SDK base class.
 *
 * Note: DeepSeek is deprecating the legacy `deepseek-chat` / `deepseek-reasoner`
 * aliases in favour of its newer models; they are sorted last while they remain.
 *
 * @since 1.0.0
 *
 * @phpstan-type ModelsResponseData array{data: list<array{id: string}>}
 */
class DeepSeekModelMetadataDirectory extends AbstractOpenAiCompatibleModelMetadataDirectory
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected function createRequest(
        HttpMethodEnum $method,
        string $path,
        array $headers = [],
        $data = null
    ): Request {
        return new Request(
            $method,
            DeepSeekProvider::url($path),
            $headers,
            $data
        );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    protected function parseResponseToModelMetadataList(Response $response): array
    {
        /** @var ModelsResponseData $responseData */
        $responseData = $response->getData();

        if (!isset($responseData['data']) || !$responseData['data']) {
            throw ResponseException::fromMissingData('DeepSeek', 'data');
        }

        $capabilities = [
            CapabilityEnum::textGeneration(),
            CapabilityEnum::chatHistory(),
        ];

        // DeepSeek's current models all support the full OpenAI-compatible Chat
        // Completions feature set (sampling, penalties, tools, JSON output, etc.).
        // Reasoning ("thinking") output, when present, is returned as
        // `reasoning_content` and mapped to a "thought" part by the SDK base class.
        $options = [
            new SupportedOption(OptionEnum::systemInstruction()),
            new SupportedOption(OptionEnum::maxTokens()),
            new SupportedOption(OptionEnum::temperature()),
            new SupportedOption(OptionEnum::topP()),
            new SupportedOption(OptionEnum::stopSequences()),
            new SupportedOption(OptionEnum::presencePenalty()),
            new SupportedOption(OptionEnum::frequencyPenalty()),
            new SupportedOption(OptionEnum::logprobs()),
            new SupportedOption(OptionEnum::topLogprobs()),
            new SupportedOption(OptionEnum::outputMimeType(), ['text/plain', 'application/json']),
            new SupportedOption(OptionEnum::outputSchema()),
            new SupportedOption(OptionEnum::functionDeclarations()),
            new SupportedOption(OptionEnum::customOptions()),
            new SupportedOption(OptionEnum::inputModalities(), [[ModalityEnum::text()]]),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::text()]]),
        ];

        $models = [];
        foreach ($responseData['data'] as $modelData) {
            if (!isset($modelData['id']) || !is_string($modelData['id'])) {
                continue;
            }

            $modelId = $modelData['id'];

            $models[] = new ModelMetadata(
                $modelId,
                $modelId, // DeepSeek's API does not return a display name.
                $capabilities,
                $options
            );
        }

        usort($models, [$this, 'modelSortCallback']);

        return $models;
    }

    /**
     * Sort callback: current models first (alphabetically), with the deprecated
     * legacy aliases (`deepseek-chat`, `deepseek-reasoner`) pushed to the end.
     *
     * @since 1.0.0
     *
     * @param ModelMetadata $a First model.
     * @param ModelMetadata $b Second model.
     * @return int Comparison result.
     */
    protected function modelSortCallback(ModelMetadata $a, ModelMetadata $b): int
    {
        $deprecated = ['deepseek-chat', 'deepseek-reasoner'];

        $rankA = in_array($a->getId(), $deprecated, true) ? 1 : 0;
        $rankB = in_array($b->getId(), $deprecated, true) ? 1 : 0;

        if ($rankA !== $rankB) {
            return $rankA <=> $rankB;
        }

        return strcmp($a->getId(), $b->getId());
    }
}
