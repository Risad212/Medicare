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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            
            $table->string('slug')->unique();

            $table->text('degree')->nullable();

            $table->string('department')->nullable();

            $table->text('specialist')->nullable();

            $table->string('image')->nullable();

            $table->longText('services')->nullable();

            $table->string('availability')->nullable();

            $table->string('phone')->nullable();

            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
