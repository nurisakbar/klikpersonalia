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
        Schema::create('performances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('employee_id');
            $table->enum('performance_type', ['kpi', 'appraisal', 'goal', 'annual']);
            $table->date('period_start');
            $table->date('period_end');
            $table->json('kpi_data')->nullable();
            $table->json('appraisal_data')->nullable();
            $table->json('goals_data')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->enum('rating', ['excellent', 'good', 'average', 'below_average', 'poor'])->nullable();
            $table->enum('status', ['draft', 'pending', 'in_progress', 'completed', 'approved', 'rejected'])->default('draft');
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_review_date')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'performance_type']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performances');
    }
}; 