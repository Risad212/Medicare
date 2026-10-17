<?php

namespace Tests\Feature;

use App\Http\Middleware\TranslateResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TranslateResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // phpunit.xml disables auto-translate suite-wide (hermetic tests);
        // this test opts back in with a faked provider.
        config(['auto_translate.enabled' => true, 'auto_translate.driver' => 'mymemory', 'auto_translate.source' => 'en']);

        Route::middleware(['web', TranslateResponse::class])->get('/_translate_probe', fn () => response(
            '<html><head><title>Our Doctors</title></head><body>'
            .'<h1>Experienced Medical Specialist</h1>'
            .'<p data-notranslate>Dr. John Doe</p>'
            .'<script>var label = "Experienced Medical Specialist";</script>'
            .'<p>Contact us at info@example.com</p>'
            .'</body></html>'
        ));
    }

    public function test_frontend_html_is_translated_except_exclusions(): void
    {
        config(['auto_translate.driver' => 'mymemory', 'auto_translate.source' => 'en']);
        Http::fake(fn ($request) => Http::response([
            'responseData' => ['translatedText' => 'T:'.$request->data()['q']],
            'responseStatus' => 200,
        ], 200));

        $response = $this->withSession(['locale' => 'bn'])->get('/_translate_probe');

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('T:Experienced Medical Specialist</h1>', $html);
        $this->assertStringContainsString('T:Our Doctors</title>', $html);
        // Exclusions survive verbatim.
        $this->assertStringContainsString('<p data-notranslate>Dr. John Doe</p>', $html);
        $this->assertStringContainsString('var label = "Experienced Medical Specialist";', $html);
        $this->assertStringContainsString('info@example.com', $html);
        $this->assertStringNotContainsString('T:info@example.com', $html);
    }

    public function test_source_locale_passes_through_without_http(): void
    {
        Http::fake();

        $response = $this->withSession(['locale' => 'en'])->get('/_translate_probe');

        $response->assertOk();
        $this->assertStringContainsString('<h1>Experienced Medical Specialist</h1>', $response->getContent());
        Http::assertNothingSent();
    }

    public function test_admin_prefix_is_never_translated(): void
    {
        config(['auto_translate.driver' => 'mymemory', 'auto_translate.source' => 'en']);
        Http::fake();

        Route::middleware(['web', TranslateResponse::class])->get('/admin/_probe', fn () => response('<html><body><p>Admin Panel</p></body></html>'));

        $response = $this->withSession(['locale' => 'bn'])->get('/admin/_probe');

        $response->assertOk();
        $this->assertStringContainsString('<p>Admin Panel</p>', $response->getContent());
        Http::assertNothingSent();
    }

    public function test_disabled_flag_passes_through_without_http(): void
    {
        config(['auto_translate.enabled' => false]);
        Http::fake();

        $response = $this->withSession(['locale' => 'bn'])->get('/_translate_probe');

        $response->assertOk();
        $this->assertStringContainsString('<h1>Experienced Medical Specialist</h1>', $response->getContent());
        Http::assertNothingSent();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
