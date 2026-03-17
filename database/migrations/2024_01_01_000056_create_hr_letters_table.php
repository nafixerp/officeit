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
        Schema::create('hr_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained();
            $table->foreignId('candidate_id')->nullable()->constrained();
            $table->enum('letter_type', ['OFFER', 'APPOINTMENT', 'CONFIRMATION', 'INCREMENT', 'PROMOTION', 'WARNING', 'EXPERIENCE', 'RELIEVING']);
            $table->longText('content')->nullable();
            $table->date('generated_date');
            $table->enum('status', ['DRAFT', 'SENT', 'ACCEPTED', 'DECLINED'])->default('DRAFT');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_letters');
    }
};
