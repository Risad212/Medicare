<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BloodGroupSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('blood_groups')->delete();

        $names = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        foreach ($names as $name) {
            DB::table('blood_groups')->insert([
                'name' => $name,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('blood_inventory_settings')->updateOrInsert(
            ['id' => 1],
            ['low_stock_threshold' => 2, 'min_donation_days' => 90, 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
