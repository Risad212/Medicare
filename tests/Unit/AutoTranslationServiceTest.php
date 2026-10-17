<?php

namespace Tests\Unit;

use App\Services\AutoTranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutoTranslationServiceTest extends TestCase
{
    public function test_same_as_source_returns_verbatim_without_http(): void
    {
        Http::fake();
        config(['auto_translate.source' => 'en']);

        $this->assertSame('Hello', app(AutoTranslationService::class)->translate('Hello', 'en'));
        Http::assertNothingSent();
    }

    public function test_mymemory_driver_translates_and_caches(): void
    {
        config(['auto_translate.driver' => 'mymemory', 'auto_translate.source' => 'en']);
        Http::fake(fn ($request) => Http::response([
            'responseData' => ['translatedText' => 'T:'.$request->data()['q']],
            'responseStatus' => 200,
        ], 200));

        $service = app(AutoTranslationService::class);

        $this->assertSame('T:Hello world', $service->translate('Hello world', 'bn'));

        // Second call must come from cache — provider hit only once.
        $this->assertSame('T:Hello world', $service->translate('Hello world', 'bn'));
        Http::assertSentCount(1);
    }

    public function test_translate_many_preserves_keys_and_caches(): void
    {
        config(['auto_translate.driver' => 'mymemory', 'auto_translate.source' => 'en']);
        Http::fake(fn ($request) => Http::response([
            'responseData' => ['translatedText' => 'T:'.$request->data()['q']],
            'responseStatus' => 200,
        ], 200));

        $out = app(AutoTranslationService::class)->translateMany([0 => 'One', 5 => 'Two'], 'bn');

        $this->assertSame([0 => 'T:One', 5 => 'T:Two'], $out);
    }

    public function test_provider_failure_falls_back_to_source_text(): void
    {
        config(['auto_translate.driver' => 'mymemory', 'auto_translate.source' => 'en']);
        Http::fake(fn () => Http::response([], 500));

        $this->assertSame('Hello world', app(AutoTranslationService::class)->translate('Hello world', 'bn'));
    }

    public function test_google_driver_batches_in_one_request(): void
    {
        config(['auto_translate.driver' => 'google', 'auto_translate.key' => 'test-key', 'auto_translate.source' => 'en']);
        Http::fake(fn () => Http::response([
            'data' => ['translations' => [
                ['translatedText' => 'G:One'],
                ['translatedText' => 'G:Two'],
            ]],
        ], 200));

        $out = app(AutoTranslationService::class)->translateMany(['One', 'Two'], 'bn');

        $this->assertSame(['G:One', 'G:Two'], $out);
        Http::assertSentCount(1);
    }

    public function test_mymemory_quota_warning_falls_back_to_source_text(): void
    {
        config(['auto_translate.driver' => 'mymemory', 'auto_translate.source' => 'en']);
        Http::fake(fn () => Http::response([
            'responseData' => ['translatedText' => 'MYMEMORY WARNING: YOU USED ALL AVAILABLE FREE TRANSLATIONS FOR TODAY'],
            'responseStatus' => 429,
        ], 200));

        $this->assertSame('Hello world', app(AutoTranslationService::class)->translate('Hello world', 'bn'));
    }

    public function test_null_driver_returns_source_text(): void
    {
        config(['auto_translate.driver' => 'null', 'auto_translate.source' => 'en']);
        Http::fake();

        $this->assertSame('Hello', app(AutoTranslationService::class)->translate('Hello', 'bn'));
        Http::assertNothingSent();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
