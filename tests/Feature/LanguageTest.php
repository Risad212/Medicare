<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Language\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::create(['name' => 'English', 'code' => 'en', 'is_default' => true, 'is_active' => true]);
        Language::create(['name' => 'Bangla', 'code' => 'bn', 'is_default' => false, 'is_active' => true]);
    }

    public function test_switcher_changes_locale_and_renders_bangla(): void
    {
        $response = $this->get('/language/bn');

        $response->assertRedirect();
        $this->assertSame('bn', session('locale'));

        $this->get('/')->assertInertia(fn ($page) => $page
            ->component('Public/Home')
            ->where('navLabels.home', 'হোম')
        );
    }

    public function test_unknown_or_inactive_code_returns_404(): void
    {
        $this->get('/language/xx')->assertNotFound();

        Language::where('code', 'bn')->update(['is_active' => false]);
        $this->get('/language/bn')->assertNotFound();
    }

    public function test_browser_language_is_respected_without_session(): void
    {
        $this->withHeaders(['Accept-Language' => 'bn'])->get('/')->assertInertia(fn ($page) => $page
            ->component('Public/Home')
            ->where('navLabels.home', 'হোম')
        );
    }

    public function test_english_stays_the_default(): void
    {
        $this->get('/')->assertInertia(fn ($page) => $page
            ->component('Public/Home')
            ->where('navLabels.home', 'Home')
        );
    }

    public function test_switch_persists_to_user_profile(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        $this->actingAs($user)->get('/language/bn');
        $this->assertSame('bn', $user->fresh()->locale);

        // Profile wins even with a fresh session.
        $this->actingAs($user)->get('/')->assertInertia(fn ($page) => $page
            ->component('Public/Home')
            ->where('navLabels.home', 'হোম')
        );
    }

    public function test_validation_messages_render_in_bangla(): void
    {
        $this->withHeaders(['Accept-Language' => 'bn', 'Referer' => route('appointment')])
            ->followingRedirects()
            ->post('/appointment', [])
            ->assertInertia(fn ($page) => $page
                ->component('Public/Appointment')
                ->has('errors.doctor_id')
                ->where('navLabels.home', 'হোম')
            );
    }

    public function test_admin_can_manage_languages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bangla = Language::where('code', 'bn')->first();

        $this->actingAs($admin)->get(route('admin.languages.index'))
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Languages/Index')
                ->where('languages.data.0.code', 'en')
                ->where('languages.data.0.isDefault', true)
            );
        $this->actingAs($admin)->get(route('admin.languages.create'))
            ->assertInertia(fn ($page) => $page->component('Admin/Languages/Form')->where('mode', 'create'));
        $this->actingAs($admin)->get(route('admin.languages.edit', $bangla))
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Languages/Form')
                ->where('mode', 'edit')
                ->where('language.code', 'bn')
            );

        // Create.
        $this->actingAs($admin)->post(route('admin.languages.store'), [
            'name' => 'Arabic',
            'code' => 'ar',
            'is_active' => true,
        ])->assertRedirect(route('admin.languages.index'));
        $this->assertDatabaseHas('languages', ['code' => 'ar']);

        $arabic = Language::where('code', 'ar')->first();

        // Deactivate then try to make default -> blocked.
        $this->actingAs($admin)->post(route('admin.languages.toggle', $arabic))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.languages.default', $arabic))->assertStatus(422);

        // Reactivate and make default; old default loses the flag.
        $this->actingAs($admin)->post(route('admin.languages.toggle', $arabic->fresh()));
        $this->actingAs($admin)->post(route('admin.languages.default', $arabic->fresh()))->assertRedirect();
        $this->assertTrue($arabic->fresh()->is_default);
        $this->assertFalse(Language::where('code', 'en')->first()->is_default);

        // Default cannot be deleted or deactivated.
        $this->actingAs($admin)->delete(route('admin.languages.destroy', $arabic->fresh()))->assertRedirect();
        $this->assertDatabaseHas('languages', ['code' => 'ar']);
        $this->actingAs($admin)->post(route('admin.languages.toggle', $arabic->fresh()))->assertRedirect();
        $this->assertTrue($arabic->fresh()->is_active);

        // Non-default can be deleted.
        $this->actingAs($admin)->delete(route('admin.languages.destroy', Language::where('code', 'bn')->first()))
            ->assertRedirect(route('admin.languages.index'));
        $this->assertDatabaseMissing('languages', ['code' => 'bn']);
    }

    public function test_disabled_module_serves_english_and_hides_routes(): void
    {
        config()->set('modules.language', false);

        $this->get('/language/bn')->assertNotFound();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.languages.index'))->assertNotFound();

        // Even a stale Bangla session falls back to English.
        $this->withSession(['locale' => 'bn'])->get('/')->assertInertia(fn ($page) => $page
            ->component('Public/Home')
            ->where('navLabels.home', 'Home')
        );
    }

    public function test_non_admin_cannot_manage_languages(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->get(route('admin.languages.index'))->assertRedirect('/login');
        $this->actingAs($patient)->post(route('admin.languages.store'), [
            'name' => 'X',
            'code' => 'xx',
        ])->assertRedirect('/login');
    }
}
