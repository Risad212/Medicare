<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Language\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::firstOrCreate(['code' => 'en'], ['name' => 'English', 'is_default' => true, 'is_active' => true]);
        Language::firstOrCreate(['code' => 'bn'], ['name' => 'Bangla', 'is_default' => false, 'is_active' => true]);
    }

    public function test_switcher_changes_locale_and_renders_bangla(): void
    {
        $response = $this->get('/language/bn');

        $response->assertRedirect();
        $this->assertSame('bn', session('locale'));

        $this->get('/')->assertSee('হোম', false);
    }

    public function test_unknown_or_inactive_code_returns_404(): void
    {
        $this->get('/language/xx')->assertNotFound();

        Language::where('code', 'bn')->update(['is_active' => false]);
        $this->get('/language/bn')->assertNotFound();
    }

    public function test_browser_language_is_respected_without_session(): void
    {
        $this->withHeaders(['Accept-Language' => 'bn'])->get('/')->assertSee('হোম', false);
    }

    public function test_english_stays_the_default(): void
    {
        $this->get('/')->assertSee('Home', false);
    }

    public function test_requested_languages_are_available_and_have_translated_navigation(): void
    {
        $translations = [
            'ar' => 'الرئيسية',
            'zh' => '首页',
            'en' => 'Home',
            'fr' => 'Accueil',
            'de' => 'Startseite',
            'it' => 'Home',
            'pt' => 'Início',
            'ru' => 'Главная',
            'es' => 'Inicio',
            'tr' => 'Ana sayfa',
        ];

        foreach ($translations as $code => $homeLabel) {
            $this->assertDatabaseHas('languages', ['code' => $code, 'is_active' => true]);
            $this->withSession(['locale' => $code])
                ->get('/')
                ->assertOk()
                ->assertSee('العربية', false)
                ->assertSee('中文', false)
                ->assertSee($homeLabel, false);
        }
    }

    public function test_appointment_form_is_translated_in_every_supported_language(): void
    {
        $translations = [
            'ar' => 'احجز موعدًا',
            'bn' => 'অ্যাপয়েন্টমেন্ট বুক করুন',
            'de' => 'Termin buchen',
            'en' => 'Book an appointment',
            'es' => 'Reservar una cita',
            'fr' => 'Prendre un rendez-vous',
            'it' => 'Prenota un appuntamento',
            'pt' => 'Agendar consulta',
            'ru' => 'Записаться на приём',
            'tr' => 'Randevu al',
            'zh' => '预约就诊',
        ];

        foreach ($translations as $code => $title) {
            $this->withSession(['locale' => $code])
                ->get(route('appointment'))
                ->assertOk()
                ->assertSee($title)
                ->assertSee(__('messages.appointment.name', [], $code), false)
                ->assertSee(__('messages.appointment.submit', [], $code), false);
        }
    }

    public function test_arabic_appointment_form_uses_right_to_left_document_direction(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get(route('appointment'))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('احجز موعدًا');
    }

    public function test_contact_page_labels_are_translated_in_every_supported_language(): void
    {
        $translations = [
            'ar' => 'اتصل بنا',
            'bn' => 'যোগাযোগ করুন',
            'de' => 'Kontakt',
            'en' => 'Contact Us',
            'es' => 'Contáctanos',
            'fr' => 'Contactez-nous',
            'it' => 'Contattaci',
            'pt' => 'Fale conosco',
            'ru' => 'Связаться с нами',
            'tr' => 'İletişim',
            'zh' => '联系我们',
        ];

        foreach ($translations as $code => $title) {
            $this->withSession(['locale' => $code])
                ->get(route('contact'))
                ->assertOk()
                ->assertSee($title)
                ->assertSee(__('messages.contact.send_message', [], $code), false)
                ->assertSee(__('messages.contact.location', [], $code), false)
                ->assertSee(__('messages.nav.home', [], $code), false);
        }
    }

    public function test_contact_validation_and_success_message_use_current_locale(): void
    {
        $this->withSession(['locale' => 'bn'])
            ->withHeaders(['Referer' => route('contact')])
            ->followingRedirects()
            ->post(route('contact.submit'), [])
            ->assertOk()
            ->assertSee('এই ঘরটি পূরণ করা আবশ্যক।', false);

        Mail::fake();
        $this->withSession(['locale' => 'es'])
            ->from(route('contact'))
            ->post(route('contact.submit'), [
                'name' => 'Patient',
                'email' => 'patient@example.test',
                'phone' => '5550100',
                'subject' => 'Question',
                'message' => 'Hello clinic',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success', 'Mensaje enviado correctamente.');
    }

    public function test_arabic_locale_sets_right_to_left_document_direction(): void
    {
        $this->withSession(['locale' => 'ar'])
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->withSession(['locale' => 'ar'])
            ->get(route('admin.languages.index'))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_switch_persists_to_user_profile(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        $this->actingAs($user)->get('/language/bn');
        $this->assertSame('bn', $user->fresh()->locale);

        // Profile wins even with a fresh session.
        $this->actingAs($user)->get('/')->assertSee('হোম', false);
    }

    public function test_validation_messages_render_in_bangla(): void
    {
        $this->withHeaders(['Accept-Language' => 'bn', 'Referer' => route('appointment')])
            ->followingRedirects()
            ->post('/appointment', [])
            ->assertSee('আবশ্যক', false);
    }

    public function test_admin_can_manage_languages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create.
        $this->actingAs($admin)->post(route('admin.languages.store'), [
            'name' => 'Test Language',
            'code' => 'fa',
            'is_active' => true,
        ])->assertRedirect(route('admin.languages.index'));
        $this->assertDatabaseHas('languages', ['code' => 'fa']);

        $testLanguage = Language::where('code', 'fa')->first();

        // Deactivate then try to make default -> blocked.
        $this->actingAs($admin)->post(route('admin.languages.toggle', $testLanguage))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.languages.default', $testLanguage))->assertStatus(422);

        // Reactivate and make default; old default loses the flag.
        $this->actingAs($admin)->post(route('admin.languages.toggle', $testLanguage->fresh()));
        $this->actingAs($admin)->post(route('admin.languages.default', $testLanguage->fresh()))->assertRedirect();
        $this->assertTrue($testLanguage->fresh()->is_default);
        $this->assertFalse(Language::where('code', 'en')->first()->is_default);

        // Default cannot be deleted or deactivated.
        $this->actingAs($admin)->delete(route('admin.languages.destroy', $testLanguage->fresh()))->assertRedirect();
        $this->assertDatabaseHas('languages', ['code' => 'fa']);
        $this->actingAs($admin)->post(route('admin.languages.toggle', $testLanguage->fresh()))->assertRedirect();
        $this->assertTrue($testLanguage->fresh()->is_active);

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
        $this->withSession(['locale' => 'bn'])->get('/')->assertSee('Home', false);
    }

    public function test_non_admin_cannot_manage_languages(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->get(route('admin.languages.index'))->assertForbidden();
        $this->actingAs($patient)->post(route('admin.languages.store'), [
            'name' => 'X',
            'code' => 'xx',
        ])->assertForbidden();
    }
}
