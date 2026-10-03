<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('doctors', 'order')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->integer('order')->default(0);
        });
    }
};
