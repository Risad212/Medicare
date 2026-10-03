<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('blood_requests')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('blood_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('donation_id')->nullable()->constrained('blood_donations')->onDelete('set null');
            $table->integer('quantity')->default(450);
            $table->string('unit', 10)->default('ml');
            $table->date('issue_date');
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('request_id');
            $table->index('patient_id');
            $table->index('blood_group_id');
            $table->index('donation_id');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_issues');
    }
};
