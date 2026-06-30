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
         Schema::create('home_settings', function (Blueprint $table) {
            $table->id();

            // About Section
            $table->text('about_title')->nullable();
            $table->text('about_description')->nullable();
            $table->string('about_button_text')->nullable();

            $table->string('about_image_one')->nullable();
            $table->string('about_image_two')->nullable();
            $table->string('about_image_three')->nullable();

            // Counter Section
            $table->string('counter_one_text')->nullable();
            $table->integer('counter_one_number')->nullable();

            $table->string('counter_two_text')->nullable();
            $table->integer('counter_two_number')->nullable();

            $table->string('counter_three_text')->nullable();
            $table->integer('counter_three_number')->nullable();

            $table->string('counter_four_text')->nullable();
            $table->integer('counter_four_number')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_settings');
    }
};
