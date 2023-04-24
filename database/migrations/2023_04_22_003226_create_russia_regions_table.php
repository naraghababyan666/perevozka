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
        Schema::create('russia_regions', function (Blueprint $table) {
            $table->id();
            $table->string('id2')->nullable();
            $table->integer('CityId')->nullable();
            $table->integer('RegionId')->nullable();
            $table->integer('CountryId')->nullable();
            $table->string('FullName')->nullable();
            $table->integer('CitySize')->nullable();
            $table->decimal('Longitude', 10,7);
            $table->decimal('Latitude', 10,7);
            $table->string('CityName')->nullable();
            $table->string('CityNameEng')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('russia_regions');
    }
};
