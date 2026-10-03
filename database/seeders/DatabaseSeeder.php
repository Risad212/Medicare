<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Language\Models\Language;
use App\Support\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => Hash::make('123456')]
        );

        if (Module::enabled('bloodbank')) {
            $this->call(BloodGroupSeeder::class);
        }

        $this->call(DemoDataSeeder::class);

        if (Module::enabled('language')) {
            Language::firstOrCreate(
                ['code' => 'en'],
                ['name' => 'English', 'is_default' => true, 'is_active' => true]
            );
            Language::firstOrCreate(
                ['code' => 'bn'],
                ['name' => 'Bangla', 'is_default' => false, 'is_active' => true]
            );
        }
    }
}
