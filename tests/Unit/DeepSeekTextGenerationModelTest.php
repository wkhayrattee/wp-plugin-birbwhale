<?php

declare(strict_types=1);

namespace BirbWhale\Tests\Unit;

use BirbWhale\Models\DeepSeekTextGenerationModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WordPress\AiClient\Providers\Models\TextGeneration\Contracts\TextGenerationModelInterface;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

#[CoversClass(DeepSeekTextGenerationModel::class)]
final class DeepSeekTextGenerationModelTest extends TestCase
{
    public function test_implements_the_text_generation_contract(): void
    {
        self::assertTrue(
            is_subclass_of(DeepSeekTextGenerationModel::class, TextGenerationModelInterface::class)
        );
    }

    public function test_extends_the_openai_compatible_base(): void
    {
        // DeepSeek uses the classic /chat/completions API, so the model must build
        // on the OpenAI-compatible base (NOT OpenAI's newer /responses base).
        self::assertTrue(
            is_subclass_of(DeepSeekTextGenerationModel::class, AbstractOpenAiCompatibleTextGenerationModel::class)
        );
    }
}
