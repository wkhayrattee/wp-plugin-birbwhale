<?php

declare(strict_types=1);

namespace BirbWhale\Tests\Unit;

use BirbWhale\Metadata\DeepSeekModelMetadataDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Exception\ResponseException;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

#[CoversClass(DeepSeekModelMetadataDirectory::class)]
final class DeepSeekModelMetadataDirectoryTest extends TestCase
{
    /**
     * Invoke the protected parser with a fake DeepSeek /models response.
     *
     * @param string[] $ids
     * @return ModelMetadata[]
     */
    private function parse(array $ids): array
    {
        $dir    = new DeepSeekModelMetadataDirectory();
        $method = new ReflectionMethod($dir, 'parseResponseToModelMetadataList');
        $method->setAccessible(true);

        $body = json_encode([
            'object' => 'list',
            'data'   => array_map(static fn (string $id): array => ['id' => $id], $ids),
        ]);

        $response = new Response(200, ['Content-Type' => ['application/json']], (string) $body);

        return $method->invoke($dir, $response);
    }

    public function test_parses_all_models_and_sorts_current_first_deprecated_last(): void
    {
        $models = $this->parse(['deepseek-reasoner', 'deepseek-v4-pro', 'deepseek-chat', 'deepseek-v4-flash']);

        self::assertCount(4, $models);

        $ids = array_map(static fn (ModelMetadata $m): string => $m->getId(), $models);

        // Current models come first (alphabetical); flash < pro.
        self::assertSame('deepseek-v4-flash', $ids[0]);
        self::assertSame('deepseek-v4-pro', $ids[1]);
        // Deprecated aliases are pushed to the end.
        self::assertContains('deepseek-chat', [$ids[2], $ids[3]]);
        self::assertContains('deepseek-reasoner', [$ids[2], $ids[3]]);
    }

    public function test_every_model_gets_the_full_capability_and_option_set(): void
    {
        foreach ($this->parse(['deepseek-v4-flash', 'deepseek-reasoner', 'deepseek-chat']) as $model) {
            self::assertCount(2, $model->getSupportedCapabilities(), $model->getId());
            self::assertCount(15, $model->getSupportedOptions(), $model->getId());
        }
    }

    public function test_unknown_future_model_is_still_listed(): void
    {
        $models = $this->parse(['deepseek-v5-something']);

        self::assertCount(1, $models);
        self::assertSame('deepseek-v5-something', $models[0]->getId());
    }

    public function test_throws_when_the_response_has_no_models(): void
    {
        $this->expectException(ResponseException::class);
        $this->parse([]);
    }
}
