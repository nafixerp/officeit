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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('asset_code')->nullable();
            $table->string('name');
            $table->string('category')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_value', 15, 2)->default(0);
            $table->enum('depreciation_method', ['STRAIGHT_LINE', 'REDUCING_BALANCE', 'NONE'])->default('STRAIGHT_LINE');
            $table->decimal('depreciation_rate', 5, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->string('location')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->enum('status', ['ACTIVE', 'DISPOSED', 'UNDER_MAINTENANCE'])->default('ACTIVE');
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_value', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
