<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('category')->nullable()->after('author');
            $table->string('tags')->nullable()->after('category');
        });
    }

    public function down()
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['category', 'tags']);
        });
    }
};
