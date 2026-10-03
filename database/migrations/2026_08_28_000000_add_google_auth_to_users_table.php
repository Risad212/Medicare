<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('email')->index();
            }
            if (! Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('google_id');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('provider');
            }
        });

        // Make password nullable for OAuth users WITHOUT doctrine/dbal
        // (->change() would require it and fail on MySQL prod).
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL');
            }
        } catch (Throwable $e) {
            // SQLite :memory: (tests) enforces nothing here — OAuth flow
            // always sets a random hashed password, so skip rather than fail.
        }
    }

    public function down(): void
    {
        // Mirror up(): only drop what exists so rollback on a
        // partially-applied DB (or re-run) does not fail.
        if (Schema::hasColumn('users', 'google_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropIndex(['google_id']);
                });
            } catch (Throwable $e) {
                // Index already gone (e.g. SQLite rebuild) — continue to columns.
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $drop = [];
            foreach (['google_id', 'provider', 'avatar'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $drop[] = $column;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
            // Revert password to NOT NULL - sqlite can't easily; keep nullable
        });
    }
};
