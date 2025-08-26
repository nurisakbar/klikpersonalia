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
        Schema::create('salary_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_value', 15, 2)->default(0);
            $table->enum('type', ['earning', 'deduction'])->default('earning');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_bpjs_calculated')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};
