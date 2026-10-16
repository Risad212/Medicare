<?php

namespace Tests\Feature;

use App\Modules\Language\Models\Language;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_seeds_languages_when_module_is_enabled(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('languages', ['code' => 'en']);
        $this->assertDatabaseHas('languages', ['code' => 'bn']);
    }

    public function test_database_seeder_skips_languages_when_module_is_disabled(): void
    {
        $languageCount = Language::count();
        config()->set('modules.language', false);
        config()->set('modules.lab', false);
        config()->set('modules.bloodbank', false);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('languages', $languageCount);
        $this->assertDatabaseCount('lab_tests', 0);
        $this->assertDatabaseCount('blood_groups', 0);
    }

    public function test_database_seeder_does_not_create_demo_accounts_or_records_in_production(): void
    {
        $this->app['env'] = 'production';
        config()->set('app.demo_data_enabled', true);
        config()->set('modules.bloodbank', false);

        $this->app->make(DatabaseSeeder::class)->run();
        $this->app->make(DemoDataSeeder::class)->run();

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'admin@medicare.test']);
        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_local_and_testing_environments_can_seed_demo_records(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@medicare.test']);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }
}
