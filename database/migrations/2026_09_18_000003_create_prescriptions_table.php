<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('patient_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Patient snapshot (walk-ins have no user account).
            $table->string('patient_name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->unsignedTinyInteger('gender')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            // Clinical content.
            $table->text('symptoms')->nullable();
            $table->text('diagnosis');
            $table->text('advice')->nullable();
            $table->date('follow_up_date')->nullable();

            $table->timestamps();

            $table->index('doctor_id');
            $table->index('patient_user_id');
            $table->index('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
