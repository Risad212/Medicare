<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_donations', function (Blueprint $table) {
            $table->foreignId('reserved_for_request_id')
                ->nullable()
                ->after('status')
                ->constrained('blood_requests')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('blood_donations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reserved_for_request_id');
        });
    }
};
