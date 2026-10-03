<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_settings', function (Blueprint $table) {
            $table->id();

            // Emergency block
            $table->string('emergency_subtitle')->nullable();
            $table->string('emergency_title')->nullable();
            $table->text('emergency_description')->nullable();
            $table->string('emergency_image')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_email')->nullable();

            // Prevention section heading
            $table->string('prevention_subtitle')->nullable();
            $table->string('prevention_title')->nullable();

            // Prevention items
            for ($i = 1; $i <= 8; $i++) {
                $table->string("prevention_{$i}_title")->nullable();
                $table->text("prevention_{$i}_desc")->nullable();
            }

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_settings');
    }
};
