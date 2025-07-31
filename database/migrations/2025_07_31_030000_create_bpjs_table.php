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
        Schema::create('bpjs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('employee_id');
            $table->uuid('payroll_id')->nullable();
            $table->string('bpjs_period'); // Format: YYYY-MM
            $table->enum('bpjs_type', ['kesehatan', 'ketenagakerjaan']);
            $table->decimal('employee_contribution', 15, 2);
            $table->decimal('company_contribution', 15, 2);
            $table->decimal('total_contribution', 15, 2);
            $table->decimal('base_salary', 15, 2);
            $table->decimal('contribution_rate_employee', 6, 4);
            $table->decimal('contribution_rate_company', 6, 4);
            $table->enum('status', ['pending', 'calculated', 'paid', 'verified'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('set null');

            // Indexes
            $table->index(['company_id', 'employee_id', 'bpjs_period']);
            $table->index(['company_id', 'bpjs_period']);
            $table->index(['company_id', 'bpjs_type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpjs');
    }
}; 