<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone_number');
            $table->enum('role_id', [Company::IS_OWNER, Company::IS_DRIVER, Company::IS_OWNER_AND_DRIVER])->nullable();
            $table->enum('is_admin', [0,1])->nullable();
            $table->string('company_name');
            $table->string('inn')->nullable();
            $table->string('ogrn')->nullable();
            $table->string('legal_address');
            $table->string('postal_address');
            $table->string('logo_url')->nullable();
            $table->text('favorites')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
