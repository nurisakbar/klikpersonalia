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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('user_id')->nullable();
            $table->string('employee_id', 20);
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('join_date');
            $table->string('department', 100);
            $table->string('position', 100);
            $table->string('ptkp_status', 10)->default('TK/0');
            $table->text('tax_notes')->nullable();
            $table->string('bpjs_kesehatan_number')->nullable();
            $table->string('bpjs_ketenagakerjaan_number')->nullable();
            $table->boolean('bpjs_kesehatan_active')->default(true);
            $table->boolean('bpjs_ketenagakerjaan_active')->default(true);
            $table->date('bpjs_effective_date')->nullable();
            $table->text('bpjs_notes')->nullable();
            $table->decimal('basic_salary', 12, 2);
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->string('emergency_contact', 255)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('company_id');
            $table->index('user_id');
            $table->index(['company_id', 'employee_id']);
            $table->unique(['company_id', 'email'], 'employees_company_id_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
