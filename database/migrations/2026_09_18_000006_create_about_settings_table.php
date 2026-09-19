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
        Schema::create('about_settings', function (Blueprint $table) {
            $table->id();

            // Intro block
            $table->string('subtitle')->nullable();
            $table->string('title')->nullable();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('image_one')->nullable();
            $table->string('image_two')->nullable();

            // Mission / Planning / Vision highlights
            $table->string('mission_title')->nullable();
            $table->text('mission_description')->nullable();
            $table->string('planning_title')->nullable();
            $table->text('planning_description')->nullable();
            $table->string('vision_title')->nullable();
            $table->text('vision_description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_settings');
    }
};
