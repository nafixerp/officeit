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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->enum('role', [
                'SUPER_ADMIN',
                'ADMIN',
                'ACCOUNTS_MANAGER',
                'ACCOUNTANT',
                'SALES_MANAGER',
                'SALES_EXECUTIVE',
                'PURCHASE_MANAGER',
                'PURCHASE_OFFICER',
                'HR_MANAGER',
                'PAYROLL_OFFICER',
                'BRANCH_MANAGER',
                'AUDITOR',
                'USER',
            ])->default('USER');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'company_id',
                'role',
                'branch_id',
                'department_id',
                'is_active',
                'phone',
                'avatar',
            ]);
        });
    }
};
