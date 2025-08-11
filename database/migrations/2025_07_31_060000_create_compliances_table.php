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
        Schema::create('compliances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->enum('compliance_type', [
                'tax_compliance',
                'labor_law',
                'bpjs_compliance',
                'data_protection',
                'financial_reporting',
                'employment_contracts',
                'workplace_safety',
                'anti_discrimination',
                'other'
            ]);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('regulation_reference')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'overdue',
                'exempt',
                'under_review'
            ])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->uuid('assigned_to')->nullable();
            $table->date('completion_date')->nullable();
            $table->decimal('compliance_score', 5, 2)->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->json('documentation_required')->nullable();
            $table->text('notes')->nullable();
            $table->date('last_audit_date')->nullable();
            $table->date('next_audit_date')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['company_id', 'compliance_type']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'priority']);
            $table->index(['company_id', 'risk_level']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliances');
    }
}; 