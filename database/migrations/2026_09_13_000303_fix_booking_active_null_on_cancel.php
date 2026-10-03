<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair databases that already applied the original booking_active
     * generated column (which stored 0 for cancelled rows). Two cancelled
     * rows for the same slot then collided in the UNIQUE index and a second
     * cancellation failed with a raw QueryException.
     *
     * The fixed expression stores NULL for cancelled rows so cancelled slots
     * can be re-booked and cancelled again without a duplicate-key error.
     *
     * MySQL nuance: `appointments_doctor_id_foreign` (ON DELETE CASCADE) has
     * no dedicated column index — it is backed by the `appointments_booking_unique`
     * index, because doctor_id is its leading column. InnoDB therefore refuses to
     * drop the unique index (error 1553). The repair drops that foreign key first,
     * rebuilds the index, then restores it. Foreign keys on lab_orders /
     * prescriptions that reference appointments(id) are dropped and restored too,
     * guarding against a partially-applied earlier attempt leaving them missing.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('appointments', 'booking_active')) {
            return;
        }

        // Fresh databases built from the corrected 2026_09_12_000302 migration
        // already store NULL for cancelled rows — nothing to repair. Legacy
        // databases that applied the original expression fall through to the
        // rebuild below.
        if ($this->usesFixedExpression()) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            foreach (array_keys($this->childForeignKeys()) as $table) {
                if ($name = $this->childConstraint($table, 'appointment_id')) {
                    Schema::table($table, fn (Blueprint $table) => $table->dropForeign($name));
                }
            }

            if ($this->constraintExists('appointments', 'appointments_doctor_id_foreign')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->dropForeign('appointments_doctor_id_foreign');
                });
            }
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_booking_unique');
            $table->dropColumn('booking_active');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('booking_active')
                ->storedAs('CASE WHEN status <> 3 THEN 1 END')
                ->after('status');

            $table->unique(
                ['doctor_id', 'appointment_date', 'time_slot_id', 'booking_active'],
                'appointments_booking_unique'
            );
        });

        if (DB::getDriverName() === 'mysql') {
            if (! $this->constraintExists('appointments', 'appointments_doctor_id_foreign')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->foreign('doctor_id')
                        ->references('id')
                        ->on('doctors')
                        ->onDelete('cascade');
                });
            }

            foreach ($this->childForeignKeys() as $table => $onDelete) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'appointment_id')) {
                    continue; // created by a later migration — declares its own FK
                }

                if ($this->childConstraint($table, 'appointment_id')) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($onDelete) {
                    $blueprint->foreign('appointment_id')
                        ->references('id')
                        ->on('appointments')
                        ->onDelete($onDelete);
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('appointments', 'booking_active')) {
            return;
        }

        // up() is a no-op on databases that already use the fixed expression,
        // so down() must mirror that: leave 302.down() to clean up. Otherwise
        // migrate:rollback removes the column and index here and 302.down()
        // then fails with "no such index".
        if ($this->usesFixedExpression()) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            foreach (array_keys($this->childForeignKeys()) as $table) {
                if ($name = $this->childConstraint($table, 'appointment_id')) {
                    Schema::table($table, fn (Blueprint $table) => $table->dropForeign($name));
                }
            }

            if ($this->constraintExists('appointments', 'appointments_doctor_id_foreign')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->dropForeign('appointments_doctor_id_foreign');
                });
            }
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_booking_unique');
            $table->dropColumn('booking_active');
        });
    }

    private function usesFixedExpression(): bool
    {
        if (DB::getDriverName() === 'mysql') {
            $expr = DB::selectOne(
                'SELECT GENERATION_EXPRESSION AS expr
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                ['appointments', 'booking_active']
            );

            return $expr !== null && str_contains(strtolower($expr->expr), 'status <> 3');
        }

        // SQLite stores the table's CREATE statement (including stored-column
        // expressions) verbatim in sqlite_master.
        $row = DB::selectOne(
            "SELECT sql AS ddl FROM sqlite_master WHERE type = 'table' AND name = ?",
            ['appointments']
        );

        return $row !== null && str_contains(strtolower($row->ddl), 'status <> 3');
    }

    private function constraintExists(string $table, string $name): bool
    {
        return DB::selectOne(
            'SELECT 1
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $name]
        ) !== null;
    }

    /**
     * The on-delete actions each child table's appointments(id) foreign key uses.
     */
    private function childForeignKeys(): array
    {
        return [
            'lab_orders' => 'SET NULL',
            'prescriptions' => 'CASCADE',
        ];
    }

    /**
     * Name of the appointments(id) foreign key on $table, or null when absent.
     */
    private function childConstraint(string $table, string $column): ?string
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return null;
        }

        $row = DB::selectOne(
            'SELECT k.CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE k
             WHERE k.TABLE_SCHEMA = DATABASE() AND k.TABLE_NAME = ?
               AND k.COLUMN_NAME = ? AND k.REFERENCED_TABLE_NAME = ?',
            [$table, $column, 'appointments']
        );

        return $row?->name;
    }
};
