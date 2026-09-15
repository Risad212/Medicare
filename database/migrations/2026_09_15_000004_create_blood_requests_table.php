<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('blood_group_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(450);
            $table->string('unit', 10)->default('ml');
            $table->date('required_date');
            $table->enum('urgency', ['normal', 'urgent', 'emergency'])->default('normal');
            $table->string('department')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'partially_approved', 'fulfilled', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('requested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('blood_group_id');
            $table->index('doctor_id');
            $table->index('status');
            $table->index('required_date');
            $table->index('urgency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_requests');
    }
};
