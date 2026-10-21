<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links a service to a department by its display name so the public
     * service detail page can surface "Doctors in this department" once an
     * admin assigns one. Nullable on purpose: no service is forced into a
     * unit, and the section stays hidden while the field is empty.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('department')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
};
