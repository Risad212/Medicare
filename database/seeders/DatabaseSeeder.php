<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Language\Models\Language;
use App\Support\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (Module::enabled('bloodbank')) {
            $this->call(BloodGroupSeeder::class);
        }

        if (DemoDataSeeder::isAllowed()) {
            User::firstOrCreate(
                ['email' => 'test@example.com'],
                ['name' => 'Test User', 'password' => '123456']
            );

            $this->call(DemoDataSeeder::class);
        }

        if (Module::enabled('language')) {
            $languages = [
                ['name' => 'العربية', 'code' => 'ar'],
                ['name' => '中文', 'code' => 'zh'],
                ['name' => 'English', 'code' => 'en'],
                ['name' => 'Français', 'code' => 'fr'],
                ['name' => 'Deutsch', 'code' => 'de'],
                ['name' => 'Italiano', 'code' => 'it'],
                ['name' => 'Português', 'code' => 'pt'],
                ['name' => 'Русский', 'code' => 'ru'],
                ['name' => 'Español', 'code' => 'es'],
                ['name' => 'Türkçe', 'code' => 'tr'],
                ['name' => 'বাংলা', 'code' => 'bn'],
            ];

            $hasDefault = Language::where('is_default', true)->exists();
            foreach ($languages as $language) {
                Language::firstOrCreate(
                    ['code' => $language['code']],
                    [
                        'name' => $language['name'],
                        'is_default' => ! $hasDefault && $language['code'] === 'en',
                        'is_active' => true,
                    ]
                );
                $hasDefault = $hasDefault || Language::where('is_default', true)->exists();
            }
        }
    }
}
