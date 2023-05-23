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
            $table->string('upload_loc_info')->after('upload_loc_id')->nullable();
            $table->string('onload_loc_info')->after('onload_loc_id')->nullable();
            $table->string('order_title')->after('onload_loc_info')->nullable();
            $table->dropColumn('loading_date');
            $table->timestamp('start_date')->after('loading_type')->nullable();
            $table->timestamp('end_date')->after('start_date')->nullable();
            $table->string('payment_nds')->after('payment_type')->nullable();
            $table->enum('prepaid', [0,1])->default(0);
            $table->string('description')->after('is_disabled')->nullable();

            $table->unsignedBigInteger('manager_id');
            $table->foreign('manager_id')->references('id')->on('managers')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->string('material_type')->after('manager_id');
            $table->string('material_info')->after('material_type')->nullable();
        });

        Schema::table('ride_orders', function (Blueprint $table){
            $table->string('order_title')->after('onload_loc_id')->nullable();
            $table->dropColumn('loading_date');
            $table->timestamp('start_date')->after('loading_type')->nullable();
            $table->timestamp('end_date')->after('start_date')->nullable();
            $table->string('payment_nds')->after('payment_type')->nullable();
            $table->enum('prepaid', [0,1])->default(0);
            $table->string('description')->after('is_disabled')->nullable();
            $table->unsignedBigInteger('manager_id');
            $table->foreign('manager_id')->references('id')->on('managers')->onUpdate('CASCADE')->onDelete('CASCADE');

            $table->string('material_type')->after('manager_id');
            $table->string('material_info')->after('material_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_orders', function (Blueprint $table) {
            //
        });
        Schema::table('ride_orders', function (Blueprint $table) {
            //
        });
    }
};
