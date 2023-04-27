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
            $table->integer('upload_loc_id')->change();
            $table->integer('onload_loc_id')->change();
        });
        Schema::table('ride_orders', function (Blueprint $table) {
            $table->integer('upload_loc_id')->change();
            $table->integer('onload_loc_id')->change();
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
