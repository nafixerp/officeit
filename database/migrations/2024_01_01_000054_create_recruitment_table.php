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
        Schema::create('recruitment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('vacancy_title');
            $table->foreignId('department_id')->nullable()->constrained();
            $table->foreignId('designation_id')->nullable()->constrained();
            $table->integer('positions')->default(1);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->enum('status', ['OPEN', 'CLOSED', 'ON_HOLD'])->default('OPEN');
            $table->date('posted_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment');
    }
};
