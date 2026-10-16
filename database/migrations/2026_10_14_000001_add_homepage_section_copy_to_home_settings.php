<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'services_subtitle',
        'services_title',
        'blog_subtitle',
        'blog_title',
        'brands_subtitle',
        'brands_title',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('home_settings', function (Blueprint $table) {
            foreach (self::COLUMNS as $column) {
                if (! Schema::hasColumn('home_settings', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_settings', function (Blueprint $table) {
            $drop = array_filter(
                self::COLUMNS,
                static fn (string $column): bool => Schema::hasColumn('home_settings', $column)
            );

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
