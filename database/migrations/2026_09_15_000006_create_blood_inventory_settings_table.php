<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_inventory_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('low_stock_threshold')->default(2);
            $table->integer('min_donation_days')->default(90);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_inventory_settings');
    }
};
