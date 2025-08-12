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
        Schema::create('data_archives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->enum('archive_type', [
                'employee',
                'payroll',
                'attendance',
                'leave',
                'overtime',
                'tax',
                'bpjs',
                'benefit',
                'performance',
                'compliance',
                'audit'
            ]);
            $table->string('table_name');
            $table->string('record_id');
            $table->json('original_data')->nullable();
            $table->json('archived_data')->nullable();
            $table->timestamp('archive_date');
            $table->integer('retention_period'); // in days, -1 for permanent
            $table->timestamp('expiry_date')->nullable();
            $table->string('archive_reason')->default('retention_policy');
            $table->uuid('archived_by')->nullable();
            $table->enum('status', ['active', 'expired', 'deleted', 'restored'])->default('active');
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('checksum')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['company_id', 'archive_type']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'table_name']);
            $table->index('expiry_date');
            $table->index('archive_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_archives');
    }
}; 