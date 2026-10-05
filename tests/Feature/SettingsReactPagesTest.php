<?php

namespace Tests\Feature;

use App\Models\AboutSetting;
use App\Models\GeneralSetting;
use App\Models\HomeSetting;
use App\Models\SeoSetting;
use App\Models\ServiceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_settings_pages_render_the_react_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([
            '/admin/settings/general' => 'General Settings',
            '/admin/settings/home' => 'Home Settings',
            '/admin/settings/about' => 'About Settings',
            '/admin/settings/service' => 'Service Settings',
            '/admin/settings/doctor' => 'Doctor SEO',
            '/admin/settings/blog' => 'Blog SEO',
            '/admin/settings/contact' => 'Contact SEO',
            '/admin/settings/appointment' => 'Appointment SEO',
        ] as $url => $title) {
            $this->actingAs($admin)
                ->get($url)
                ->assertInertia(fn ($page) => $page
                    ->component('Admin/Settings/Form')
                    ->where('title', $title)
                    ->has('routes.settingsGeneral')
                    ->has('routes.settingsAppointmentSeo')
                );
        }
    }

    public function test_admin_can_update_website_content_and_page_seo(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Storage::fake('public');

        $this->actingAs($admin)->post('/admin/settings/general', [
            'site_name' => 'MediCare Community Hospital',
            'email' => 'hello@example.test',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect();
        $generalSetting = GeneralSetting::firstOrFail();
        $this->assertSame('MediCare Community Hospital', $generalSetting->site_name);
        Storage::disk('public')->assertExists($generalSetting->logo);

        $this->post('/admin/settings/home', [
            'about_title' => 'Care for every generation',
            'counter_one_text' => 'Specialists',
            'counter_one_number' => 12,
        ])->assertRedirect();
        $this->assertSame('Care for every generation', HomeSetting::firstOrFail()->about_title);

        $this->post('/admin/settings/about', [
            'title' => 'Care without compromise',
            'mission_title' => 'Our mission',
        ])->assertRedirect();
        $this->assertSame('Care without compromise', AboutSetting::firstOrFail()->title);

        $this->post('/admin/settings/service', [
            'emergency_title' => 'Urgent care, day and night',
            'prevention_1_title' => 'Keep moving',
        ])->assertRedirect();
        $this->assertSame('Urgent care, day and night', ServiceSetting::firstOrFail()->emergency_title);

        $this->post('/admin/seo-settings', [
            'page' => 'doctor',
            'meta_title' => 'Meet our care team',
            'meta_description' => 'Find a doctor for your needs.',
            'meta_keywords' => 'doctor, care',
        ])->assertRedirect();
        $this->assertSame('Meet our care team', SeoSetting::where('page', 'doctor')->firstOrFail()->meta_title);
    }
}
