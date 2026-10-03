<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
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
        config()->set('modules.language', false);
        config()->set('modules.lab', false);
        config()->set('modules.bloodbank', false);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('languages', ['code' => 'en']);
        $this->assertDatabaseMissing('languages', ['code' => 'bn']);
        $this->assertDatabaseCount('lab_tests', 0);
        $this->assertDatabaseCount('blood_groups', 0);
    }
}
