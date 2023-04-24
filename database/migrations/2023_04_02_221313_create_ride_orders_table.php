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
        Schema::create('ride_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('upload_loc_id');
            $table->string('onload_loc_id');
            $table->string('kuzov_type');
            $table->string('loading_type');
            $table->integer('max_weight');
            $table->integer('max_volume');
            $table->string('payment_type');
            $table->decimal('ruble_per_kg', 10, 2);
            $table->string('phone_number');
            $table->string('company_name');
            $table->enum('is_disabled', [0, 1])->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_orders');
    }
};
