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
            $table->dropColumn('upload_loc_info');
            $table->dropColumn('onload_loc_info');
            $table->string('upload_loc_address')->after('upload_loc_id')->nullable();
            $table->string('onload_loc_address')->after('onload_loc_id')->nullable();
            $table->integer('distance')->after('order_title')->nullable();
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
