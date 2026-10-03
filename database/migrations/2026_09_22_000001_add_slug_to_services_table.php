<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('services', 'slug')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('slug')->nullable()->unique()->after('title');
            });
        }

        foreach (DB::table('services')->where(fn ($q) => $q->whereNull('slug')->orWhere('slug', ''))->get(['id', 'title']) as $row) {
            $slug = Str::slug($row->title ?: 'service');
            if (DB::table('services')->where('slug', $slug)->exists()) {
                $slug .= '-'.$row->id;
            }
            DB::table('services')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('services', 'slug')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
