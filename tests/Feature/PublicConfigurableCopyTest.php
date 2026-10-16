<?php

namespace Tests\Feature;

use App\Models\HomeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicConfigurableCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_admin_managed_section_copy(): void
    {
        HomeSetting::create([
            'services_title' => 'Care shaped by specialists',
            'services_subtitle' => 'What we treat',
            'blog_title' => 'Stories from the ward',
            'blog_subtitle' => 'Journal',
            'brands_title' => 'Partners we trust',
            'brands_subtitle' => 'Network',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Care shaped by specialists')
            ->assertSee('Stories from the ward')
            ->assertSee('Partners we trust')
            ->assertDontSee('We Care Our Patients')
            ->assertDontSee('Recent Articles and News')
            ->assertDontSee('Our Brands');
    }

    public function test_homepage_falls_back_to_localized_defaults_when_unset(): void
    {
        $this->assertNull(HomeSetting::first());

        $this->withSession(['locale' => 'es'])
            ->get('/')
            ->assertOk()
            ->assertSee('Cuidamos a nuestros pacientes', false)
            ->assertSee('Artículos y noticias recientes', false)
            ->assertSee('Nuestras marcas', false);
    }

    public function test_admin_can_update_section_copy(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('settings.home.update'), [
                'services_title' => 'Specialist care, same day',
                'blog_title' => 'From the blog',
                'brands_title' => 'Our network',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('home_settings', ['services_title' => 'Specialist care, same day']);

        $this->get('/')->assertSee('Specialist care, same day');
    }

    public function test_doctor_and_blog_titles_follow_the_locale(): void
    {
        $this->withSession(['locale' => 'bn'])
            ->get(route('doctor'))
            ->assertOk()
            ->assertSee('ডাক্তারগণ', false);

        $this->withSession(['locale' => 'es'])
            ->get(route('blog'))
            ->assertOk()
            ->assertSee('Nuestro blog', false);
    }
}
