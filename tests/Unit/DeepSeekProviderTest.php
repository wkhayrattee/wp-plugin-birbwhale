<?php

declare(strict_types=1);

namespace BirbWhale\Tests\Unit;

use BirbWhale\Provider\DeepSeekProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;

#[CoversClass(DeepSeekProvider::class)]
final class DeepSeekProviderTest extends TestCase
{
    public function test_extends_the_api_provider_base(): void
    {
        self::assertTrue(is_subclass_of(DeepSeekProvider::class, AbstractApiProvider::class));
    }

    public function test_provider_metadata(): void
    {
        $meta = DeepSeekProvider::metadata();

        self::assertSame('deepseek', $meta->getId());
        self::assertSame('DeepSeek', $meta->getName());
        self::assertStringContainsString('deepseek.com', (string) $meta->getCredentialsUrl());
    }

    public function test_connector_logo_is_the_deepseek_mark_and_exists(): void
    {
        $logo = (string) DeepSeekProvider::metadata()->getLogoPath();

        // The Connectors card shows DeepSeek's own logo (not the plugin's branding).
        self::assertStringEndsWith('deepseek.svg', $logo);
        self::assertFileExists($logo);
    }

    public function test_base_url_and_endpoint_paths(): void
    {
        self::assertSame('https://api.deepseek.com', DeepSeekProvider::url());
        self::assertSame('https://api.deepseek.com/chat/completions', DeepSeekProvider::url('chat/completions'));
        self::assertSame('https://api.deepseek.com/models', DeepSeekProvider::url('models'));
    }
}
