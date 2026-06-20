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
 * Lists DeepSeek models via its OpenAI-compatible `GET /models` endpoint and maps
 * each to its capabilities and supported options.
 *
 * DeepSeek's models endpoint, like OpenAI's, does not report capabilities, so we
 * map them from the model id:
 *  - `deepseek-chat`     → DeepSeek-V3: full chat option set (sampling, tools, JSON).
 *  - `deepseek-reasoner` → DeepSeek-R1: reasoning model that ignores sampling
 *    params and does not support tools/JSON output, so it gets a reduced set.
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

        // deepseek-chat (DeepSeek-V3): full Chat Completions feature set.
        $chatOptions = [
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

        // deepseek-reasoner (DeepSeek-R1): reasoning model — no sampling/tools/JSON.
        $reasonerOptions = [
            new SupportedOption(OptionEnum::systemInstruction()),
            new SupportedOption(OptionEnum::maxTokens()),
            new SupportedOption(OptionEnum::stopSequences()),
            new SupportedOption(OptionEnum::customOptions()),
            new SupportedOption(OptionEnum::inputModalities(), [[ModalityEnum::text()]]),
            new SupportedOption(OptionEnum::outputModalities(), [[ModalityEnum::text()]]),
        ];

        $models = [];
        foreach ($responseData['data'] as $modelData) {
            if (!isset($modelData['id']) || !is_string($modelData['id'])) {
                continue;
            }

            $modelId    = $modelData['id'];
            $isReasoner = str_contains($modelId, 'reasoner') || str_contains($modelId, '-r1');

            $models[] = new ModelMetadata(
                $modelId,
                $modelId, // DeepSeek's API does not return a display name.
                $capabilities,
                $isReasoner ? $reasonerOptions : $chatOptions
            );
        }

        usort($models, [$this, 'modelSortCallback']);

        return $models;
    }

    /**
     * Sort callback: surface the general chat model first, then the reasoner,
     * then anything else alphabetically.
     *
     * @since 1.0.0
     *
     * @param ModelMetadata $a First model.
     * @param ModelMetadata $b Second model.
     * @return int Comparison result.
     */
    protected function modelSortCallback(ModelMetadata $a, ModelMetadata $b): int
    {
        $rank = static function (string $id): int {
            if ('deepseek-chat' === $id) {
                return 0;
            }
            if ('deepseek-reasoner' === $id) {
                return 1;
            }
            return 2;
        };

        $rankA = $rank($a->getId());
        $rankB = $rank($b->getId());

        if ($rankA !== $rankB) {
            return $rankA <=> $rankB;
        }

        return strcmp($a->getId(), $b->getId());
    }
}
