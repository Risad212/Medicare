<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('medicines')) {
            return;
        }

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('generic_name')->nullable();
            $table->string('unit', 30)->default('tablet');
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(10);
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
