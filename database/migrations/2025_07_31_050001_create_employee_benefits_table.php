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
        Schema::create('employee_benefits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('employee_id');
            $table->uuid('benefit_id');
            $table->date('enrollment_date');
            $table->date('termination_date')->nullable();
            $table->decimal('monthly_cost', 12, 2)->nullable();
            $table->decimal('employer_contribution', 12, 2)->nullable();
            $table->decimal('employee_contribution', 12, 2)->nullable();
            $table->decimal('coverage_amount', 12, 2)->nullable();
            $table->string('policy_number')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending', 'terminated', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('benefit_id')->references('id')->on('benefits')->onDelete('cascade');
            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'benefit_id']);
            $table->index(['company_id', 'status']);
            $table->index('enrollment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_benefits');
    }
}; 