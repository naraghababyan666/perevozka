<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('goods_orders', function (Blueprint $table) {
            $table->renameColumn('ruble_per_tonn', 'ruble_per_tonn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_orders', function (Blueprint $table) {
            $table->renameColumn('ruble_per_tonn', 'ruble_per_tonn');
        });
    }
};
