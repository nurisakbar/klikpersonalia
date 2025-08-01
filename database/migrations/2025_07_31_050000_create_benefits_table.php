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
        Schema::create('benefits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('benefit_type', [
                'health_insurance',
                'life_insurance', 
                'disability_insurance',
                'retirement_plan',
                'education_assistance',
                'meal_allowance',
                'transport_allowance',
                'housing_allowance',
                'other'
            ]);
            $table->enum('cost_type', ['fixed', 'percentage', 'mixed'])->default('fixed');
            $table->decimal('cost_amount', 12, 2)->nullable();
            $table->decimal('cost_percentage', 5, 2)->nullable();
            $table->string('provider')->nullable();
            $table->string('policy_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('eligibility_criteria')->nullable();
            $table->json('coverage_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->index(['company_id', 'benefit_type']);
            $table->index(['company_id', 'is_active']);
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benefits');
    }
}; 