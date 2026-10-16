<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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

        $hasDefault = DB::table('languages')->where('is_default', true)->exists();
        $now = now();

        foreach ($languages as $language) {
            if (DB::table('languages')->where('code', $language['code'])->exists()) {
                continue;
            }

            $isDefault = ! $hasDefault && $language['code'] === 'en';

            DB::table('languages')->insert([
                ...$language,
                'is_default' => $isDefault,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $hasDefault = $hasDefault || $isDefault;
        }
    }

    public function down(): void
    {
        // Keep language rows: they may have been customized or assigned to users.
    }
};
