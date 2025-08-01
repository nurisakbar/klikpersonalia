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
        Schema::create('integration_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('external_integration_id');
            $table->enum('sync_type', ['employee', 'payroll', 'attendance', 'tax', 'bpjs', 'leave', 'overtime']);
            $table->enum('status', ['running', 'success', 'failed', 'partial'])->default('running');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('records_processed')->default(0);
            $table->integer('records_success')->default(0);
            $table->integer('records_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('sync_duration')->nullable(); // in seconds
            $table->uuid('triggered_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('external_integration_id')->references('id')->on('external_integrations')->onDelete('cascade');
            $table->foreign('triggered_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['company_id', 'external_integration_id']);
            $table->index(['company_id', 'sync_type']);
            $table->index(['company_id', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_sync_logs');
    }
}; 