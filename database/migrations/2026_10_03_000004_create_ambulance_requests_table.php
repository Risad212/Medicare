<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ambulance_requests')) {
            return;
        }

        Schema::create('ambulance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requester_name');
            $table->string('requester_phone', 30);
            $table->text('pickup_address');
            $table->string('destination')->nullable();
            $table->string('emergency_type', 500)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambulance_requests');
    }
};
