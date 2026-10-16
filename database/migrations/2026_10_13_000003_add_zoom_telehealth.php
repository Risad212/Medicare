<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('consultation_mode')->default('in_person')->after('visit_type');
        });

        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('zoom_meeting_id')->nullable();
            $table->text('join_url')->nullable();
            $table->text('start_url')->nullable();
            $table->string('sync_status')->default('pending');
            $table->string('last_error_code')->nullable();
            $table->timestamp('patient_notified_at')->nullable();
            $table->string('patient_notification_key', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('consultation_mode');
        });
    }
};
