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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();

            // Site Identity
            $table->string('site_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();

            // Header
            $table->text('address')->nullable();
            $table->text('working_hours')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();

            // Footer
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('footer_logo')->nullable();
            $table->text('footer_description')->nullable();
            $table->text('copyright')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
