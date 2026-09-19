<?php

namespace Tests\Feature;

use App\Models\AboutSetting;
use App\Models\GeneralSetting;
use App\Models\SeoSetting;
use App\Models\ServiceSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_all_settings_pages_load(): void
    {
        $client = $this->actingAs($this->makeAdmin());

        $client->get(route('settings.general'))->assertOk();
        $client->get(route('settings.home'))->assertOk();
        $client->get(route('settings.about'))->assertOk();
        $client->get(route('settings.service'))->assertOk();
        $client->get(route('settings.doctor'))->assertOk();
        $client->get(route('settings.blog'))->assertOk();
        $client->get(route('settings.contact'))->assertOk();
        $client->get(route('settings.appointment'))->assertOk();
    }

    public function test_general_settings_save_map_embed_url(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('settings.general'))
            ->post(route('settings.general.update'), [
                'site_name' => 'MediCare',
                'map_embed_url' => 'https://maps.example.com/embed?q=hospital',
            ])->assertRedirect();

        $this->assertDatabaseHas('general_settings', [
            'map_embed_url' => 'https://maps.example.com/embed?q=hospital',
        ]);
    }

    public function test_appointment_seo_settings_can_be_saved(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.seo-settings.update'), [
                'page' => 'appointment',
                'meta_title' => 'Book an Appointment',
            ])->assertRedirect();

        $this->assertDatabaseHas('seo_settings', [
            'page' => 'appointment',
            'meta_title' => 'Book an Appointment',
        ]);
    }

    public function test_general_settings_are_created_and_updated(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->makeAdmin())
            ->from(route('settings.general'))
            ->post(route('settings.general.update'), [
                'site_name' => 'MediCare',
                'address' => '12 Hospital Road',
                'working_hours' => '9AM - 9PM',
                'email' => 'hospital@gmail.com',
                'phone' => '01711-000000',
                'copyright' => '2026 MediCare',
                'logo' => UploadedFile::fake()->image('logo.png', 200, 80),
                'footer_description' => 'Trusted care.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('general_settings', [
            'site_name' => 'MediCare',
            'email' => 'hospital@gmail.com',
        ]);

        $setting = GeneralSetting::first();
        $this->assertNotNull($setting->logo);
        Storage::disk('public')->assertExists($setting->logo);

        $this->actingAs($this->makeAdmin())
            ->post(route('settings.general.update'), [
                'site_name' => 'MediCare Plus',
                'email' => 'hospital@gmail.com',
            ])->assertRedirect();

        $this->assertDatabaseHas('general_settings', [
            'site_name' => 'MediCare Plus',
        ]);
        $this->assertSame(1, GeneralSetting::count());
    }

    public function test_about_settings_are_saved(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeAdmin())
            ->from(route('settings.about'))
            ->post(route('settings.about.update'), [
                'subtitle' => 'Who we are',
                'title' => 'About Title XYZ',
                'mission_title' => 'Our Mission',
                'mission_description' => 'Mission text.',
                'image_one' => UploadedFile::fake()->image('about1.jpg', 600, 400),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('about_settings', [
            'title' => 'About Title XYZ',
            'mission_title' => 'Our Mission',
        ]);

        $about = AboutSetting::first();
        $this->assertNotNull($about->image_one);
        Storage::disk('public')->assertExists($about->image_one);
    }

    public function test_service_settings_are_saved(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeAdmin())
            ->from(route('settings.service'))
            ->post(route('settings.service.update'), [
                'emergency_title' => 'Emergency Now',
                'emergency_phone' => '+8801700000000',
                'prevention_1_title' => 'Wash hands',
                'prevention_1_desc' => 'Use soap.',
                'emergency_image' => UploadedFile::fake()->image('emergency.jpg', 600, 400),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('service_settings', [
            'emergency_title' => 'Emergency Now',
            'prevention_1_title' => 'Wash hands',
        ]);

        $setting = ServiceSetting::first();
        $this->assertNotNull($setting->emergency_image);
        Storage::disk('public')->assertExists($setting->emergency_image);
    }

    public function test_general_settings_validate_urls(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('settings.general'))
            ->post(route('settings.general.update'), [
                'facebook' => 'not-a-url',
            ])->assertSessionHasErrors('facebook');
    }

    public function test_general_settings_validate_email(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('settings.general.update'), [
                'email' => 'not-an-email',
            ])->assertSessionHasErrors('email');
    }

    public function test_home_settings_are_saved(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeAdmin())
            ->from(route('settings.home'))
            ->post(route('settings.home.update'), [
                'about_title' => 'About MediCare',
                'about_description' => 'We care for you.',
                'about_button_text' => 'Learn more',
                'counter_one_text' => 'Doctors',
                'counter_one_number' => 25,
                'counter_two_text' => 'Patients',
                'counter_two_number' => 5000,
                'about_image_one' => UploadedFile::fake()->image('about1.jpg', 800, 500),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('home_settings', [
            'about_title' => 'About MediCare',
            'counter_one_number' => 25,
        ]);
    }

    public function test_home_settings_validate_counter_numbers(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('settings.home.update'), [
                'counter_one_number' => 'not-a-number',
            ])->assertSessionHasErrors('counter_one_number');
    }

    public function test_seo_settings_are_upserted_per_page(): void
    {
        $this->actingAs($this->makeAdmin())
            ->from(route('settings.home'))
            ->post(route('admin.seo-settings.update'), [
                'page' => 'home',
                'meta_title' => 'MediCare Home',
                'meta_description' => 'Best hospital services',
                'meta_keywords' => 'hospital, care',
            ])
            ->assertRedirect()
            ->assertSessionHas('seo_success', 'SEO settings updated successfully.');

        $this->assertDatabaseHas('seo_settings', [
            'page' => 'home',
            'meta_title' => 'MediCare Home',
            'meta_keywords' => 'hospital, care',
        ]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.seo-settings.update'), [
                'page' => 'home',
                'meta_title' => 'MediCare Home v2',
            ])
            ->assertSessionHas('seo_success', 'SEO settings updated successfully.');

        $this->assertSame(1, SeoSetting::where('page', 'home')->count());
        $this->assertDatabaseHas('seo_settings', ['page' => 'home', 'meta_title' => 'MediCare Home v2']);
    }
}
