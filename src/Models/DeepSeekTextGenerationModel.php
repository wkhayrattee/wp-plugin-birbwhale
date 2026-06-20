<?php

declare(strict_types=1);

namespace BirbWhale\Models;

use BirbWhale\Provider\DeepSeekProvider;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

defined('ABSPATH') || exit;

/**
 * DeepSeek text generation model (Chat Completions API).
 *
 * DeepSeek implements the OpenAI Chat Completions format, so the SDK base class
 * handles request/response shaping. We only build the Request against DeepSeek's
 * base URL. Note: DeepSeek returns reasoning output for `deepseek-reasoner` in a
 * `reasoning_content` field, which the base class already maps to a "thought"
 * message part.
 *
 * @since 1.0.0
 */
class DeepSeekTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel
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
            $data,
            $this->getRequestOptions()
        );
    }
}
