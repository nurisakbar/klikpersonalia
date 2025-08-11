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
        Schema::create('salary_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('employee_id');
            $table->uuid('bank_account_id');
            $table->uuid('payroll_id');
            $table->date('transfer_date');
            $table->decimal('amount', 15, 2);
            $table->enum('transfer_method', ['bank_transfer', 'rtgs', 'clearing', 'instant_transfer', 'batch_transfer'])->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('confirmation_date')->nullable();
            $table->string('bank_statement_reference')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('processed_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['company_id', 'transfer_date']);
            $table->index(['employee_id', 'status']);
            $table->index(['payroll_id']);
            $table->index(['status', 'transfer_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_transfers');
    }
}; 