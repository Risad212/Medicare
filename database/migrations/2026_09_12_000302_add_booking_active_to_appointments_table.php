<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a generated "booking_active" column and a unique index so the
     * database itself rejects a second booking for the same doctor/date/slot.
     *
     * Cancelled (status=3) rows must produce NULL: a NULL is allowed any
     * number of times in a UNIQUE index (SQLite & MySQL), so an already
     * cancelled slot can be re-booked — and cancelled again — without the
     * index flagging a duplicate (doctor_id, date, slot, NULL) tuple.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('booking_active')
                ->storedAs('CASE WHEN status <> 3 THEN 1 END')
                ->after('status');

            $table->unique(
                ['doctor_id', 'appointment_date', 'time_slot_id', 'booking_active'],
                'appointments_booking_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_booking_unique');
            $table->dropColumn('booking_active');
        });
    }
};
