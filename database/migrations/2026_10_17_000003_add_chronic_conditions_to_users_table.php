<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'chronic_conditions')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->text('chronic_conditions')->nullable()->after('allergies');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'chronic_conditions')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('chronic_conditions');
        });
    }
};
