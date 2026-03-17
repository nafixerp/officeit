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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso_code', 2)->unique();
            $table->string('iso3_code', 3)->nullable();
            $table->string('phone_code')->nullable();
            $table->unsignedBigInteger('default_currency_id')->nullable();
            $table->string('tax_type')->nullable();
            $table->decimal('tax_percentage', 5, 2)->nullable();
            $table->string('tax_registration_format')->nullable();
            $table->string('invoice_number_format')->nullable();
            $table->string('date_format')->default('Y-m-d');
            $table->string('financial_year_format')->nullable();
            $table->string('address_style')->nullable();
            $table->string('accounting_rules_profile')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
