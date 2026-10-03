<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clinic patient register, separate from login accounts.
     * Admin-created patients live here only — never in `users`.
     * Existing patient accounts are backfilled as records (accounts kept).
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            $rows = DB::table('users')
                ->where('role', 'patient')
                ->get(['name', 'email', 'phone', 'gender', 'date_of_birth', 'blood_group', 'address', 'created_at', 'updated_at']);

            foreach ($rows as $row) {
                DB::table('patients')->insert((array) $row);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
