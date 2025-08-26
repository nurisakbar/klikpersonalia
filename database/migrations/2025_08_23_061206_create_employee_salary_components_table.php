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
        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('employee_id');
            $table->uuid('salary_component_id');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('calculation_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('percentage_value', 5, 2)->nullable(); // For percentage-based calculations
            $table->enum('reference_type', ['basic_salary', 'gross_salary', 'net_salary'])->nullable(); // For percentage calculations
            $table->boolean('is_active')->default(true);
            $table->date('effective_date')->nullable(); // When this component becomes effective
            $table->date('expiry_date')->nullable(); // When this component expires
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('salary_component_id')->references('id')->on('salary_components')->onDelete('cascade');

            // Unique constraint - one employee can only have one instance of each component
            $table->unique(['company_id', 'employee_id', 'salary_component_id'], 'unique_employee_component');

            // Indexes for performance
            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'salary_component_id']);
            $table->index(['effective_date', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_components');
    }
};
