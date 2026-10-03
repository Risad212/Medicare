<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            if (! Schema::hasColumn('prescription_items', 'medicine_id')) {
                $table->foreignId('medicine_id')->nullable()->after('prescription_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('prescription_items', 'dispensed_quantity')) {
                $table->unsignedInteger('dispensed_quantity')->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('prescription_items', 'dispensed_at')) {
                $table->dateTime('dispensed_at')->nullable()->after('dispensed_quantity');
            }
            if (! Schema::hasColumn('prescription_items', 'dispensed_by')) {
                $table->foreignId('dispensed_by')->nullable()->after('dispensed_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_items', 'dispensed_by')) {
                $table->dropForeign(['dispensed_by']);
                $table->dropColumn('dispensed_by');
            }
            if (Schema::hasColumn('prescription_items', 'dispensed_at')) {
                $table->dropColumn('dispensed_at');
            }
            if (Schema::hasColumn('prescription_items', 'dispensed_quantity')) {
                $table->dropColumn('dispensed_quantity');
            }
            if (Schema::hasColumn('prescription_items', 'medicine_id')) {
                $table->dropForeign(['medicine_id']);
                $table->dropColumn('medicine_id');
            }
        });
    }
};
