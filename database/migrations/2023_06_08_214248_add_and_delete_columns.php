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
        Schema::table('ride_orders', function (Blueprint $table) {
            $table->dropColumn('order_title');
            $table->dropColumn('max_weight');
            $table->dropColumn('loading_type');
            $table->dropColumn('payment_type');
            $table->dropColumn('payment_nds');
            $table->dropColumn('ruble_per_tonn');
            $table->dropColumn('prepaid');
            $table->dropColumn('material_type');
            $table->dropColumn('material_info');
        });
        Schema::table('goods_orders', function (Blueprint $table) {
            $table->dropColumn('upload_loc_address');
            $table->dropColumn('max_weight');
            $table->dropColumn('material_type');
            $table->dropColumn('material_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
