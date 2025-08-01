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
        Schema::create('compliance_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('compliance_id');
            $table->enum('audit_type', [
                'internal',
                'external',
                'regulatory',
                'self_assessment'
            ]);
            $table->date('audit_date');
            $table->uuid('auditor_id')->nullable();
            $table->json('findings')->nullable();
            $table->json('recommendations')->nullable();
            $table->decimal('compliance_score', 5, 2)->nullable();
            $table->json('risk_assessment')->nullable();
            $table->json('corrective_actions')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'failed',
                'overdue'
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('compliance_id')->references('id')->on('compliances')->onDelete('cascade');
            $table->foreign('auditor_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['company_id', 'compliance_id']);
            $table->index(['company_id', 'audit_type']);
            $table->index(['company_id', 'status']);
            $table->index('audit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_audit_logs');
    }
}; 