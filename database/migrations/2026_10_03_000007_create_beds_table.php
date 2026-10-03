<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('beds')) {
            return;
        }

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->string('bed_number', 50);
            $table->tinyInteger('status')->default(0);
            $table->foreignId('current_patient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('admitted_at')->nullable();
            $table->timestamps();

            $table->unique(['room_id', 'bed_number']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
