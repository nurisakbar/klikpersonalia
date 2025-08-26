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
        Schema::create('taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('employee_id');
            $table->uuid('payroll_id')->nullable();
            $table->string('tax_period'); // Format: YYYY-MM
            $table->decimal('taxable_income', 15, 2);
            $table->string('ptkp_status', 10); // TK/0, TK/1, K/0, etc.
            $table->decimal('ptkp_amount', 15, 2);
            $table->decimal('taxable_base', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->string('tax_bracket')->nullable();
            $table->decimal('tax_rate', 5, 2);
            $table->enum('status', ['pending', 'calculated', 'paid', 'verified'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('set null');

            $table->index(['company_id', 'employee_id', 'tax_period']);
            $table->index(['company_id', 'tax_period']);
            
            // Unique constraint to prevent duplicate tax calculations
            $table->unique(['company_id', 'employee_id', 'tax_period'], 'unique_tax_calculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
}; 