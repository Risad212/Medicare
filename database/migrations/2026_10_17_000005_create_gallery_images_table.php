<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gallery_images')) {
            return;
        }

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};
