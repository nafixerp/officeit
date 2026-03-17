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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('payment_number');
            $table->date('date');
            $table->enum('paid_to_type', ['SUPPLIER', 'EMPLOYEE', 'EXPENSE', 'OTHER']);
            $table->unsignedBigInteger('paid_to_id')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('payment_mode', ['CASH', 'BANK', 'CHEQUE', 'TRANSFER', 'ONLINE']);
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->decimal('amount', 15, 2);
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cash_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->text('narration')->nullable();
            $table->enum('status', ['DRAFT', 'APPROVED', 'CANCELLED'])->default('DRAFT');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
