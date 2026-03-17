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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('journal_number');
            $table->date('date');
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('narration')->nullable();
            $table->enum('type', ['MANUAL', 'ADJUSTMENT', 'ACCRUAL', 'DEPRECIATION', 'EXCHANGE_DIFF', 'CLOSING', 'COST_ALLOCATION', 'RECURRING'])->default('MANUAL');
            $table->enum('status', ['DRAFT', 'APPROVED', 'CANCELLED'])->default('DRAFT');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_auto_generated')->default(false);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
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
        Schema::dropIfExists('journal_entries');
    }
};
