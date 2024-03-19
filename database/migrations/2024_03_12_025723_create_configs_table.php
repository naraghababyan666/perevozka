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
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('tariff_name_1')->default('Грузовладелец');
            $table->integer('tariff_price_1')->default(1250);
            $table->string('tariff_name_2')->default('Перевозчик');
            $table->integer('tariff_price_2')->default(2500);
            $table->string('tariff_name_3')->default('Грузовладелец-перевозчик');
            $table->integer('tariff_price_3')->default(3500);
            $table->boolean('free_subscription')->default(false);
            $table->timestamp('free_subscription_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
