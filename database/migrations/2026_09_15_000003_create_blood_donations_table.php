<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('blood_donors')->onDelete('cascade');
            $table->foreignId('blood_group_id')->constrained()->onDelete('cascade');
            $table->date('donation_date');
            $table->integer('quantity')->default(450);
            $table->string('unit', 10)->default('ml');
            $table->string('bag_number')->nullable();
            $table->string('collection_location')->nullable();
            $table->date('expiry_date');
            $table->enum('status', ['collected', 'testing', 'available', 'reserved', 'issued', 'expired', 'rejected'])->default('collected');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('donor_id');
            $table->index('blood_group_id');
            $table->index('status');
            $table->index('donation_date');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_donations');
    }
};
