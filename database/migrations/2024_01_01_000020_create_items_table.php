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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('item_categories')->nullOnDelete();
            $table->string('unit')->nullable();
            $table->enum('item_type', ['STOCK', 'NON_STOCK', 'SERVICE', 'CONSUMABLE', 'FIXED_ASSET'])->default('STOCK');
            $table->decimal('purchase_rate', 15, 2)->default(0);
            $table->decimal('sales_rate', 15, 2)->default(0);
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();
            $table->decimal('minimum_stock', 15, 2)->default(0);
            $table->decimal('reorder_level', 15, 2)->default(0);
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('purchase_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('sales_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('inventory_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
