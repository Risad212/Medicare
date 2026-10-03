<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vaccinations')) {
            return;
        }

        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('child_name')->nullable();
            $table->string('vaccine_name');
            $table->unsignedInteger('dose_number')->default(1);
            $table->date('date_given')->nullable();
            $table->date('next_due_date')->nullable();
            $table->string('administered_by')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['patient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
