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
            $table->integer('upload_region_id')->after('upload_loc_id')->nullable();
//            $table->foreign('upload_region_id')->references('id')->on('russia_only_regions')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->integer('onload_region_id')->after('onload_loc_id')->nullable();
//            $table->foreign('onload_region_id')->references('id')->on('russia_only_regions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('region_id_column_in_goods_orders');
    }
};
