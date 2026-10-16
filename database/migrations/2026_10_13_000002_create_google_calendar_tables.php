<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_calendar_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('google_email');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('google_calendar_events', function (Blueprint $table) {
            $table->id();
            // Keep this reference until the remote event is deleted.
            $table->unsignedBigInteger('appointment_id')->unique();
            $table->foreignId('connection_id')->constrained('google_calendar_connections')->cascadeOnDelete();
            $table->string('google_event_id')->nullable();
            $table->string('sync_status', 20)->default('pending');
            $table->string('last_error_code', 80)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_calendar_events');
        Schema::dropIfExists('google_calendar_connections');
    }
};
