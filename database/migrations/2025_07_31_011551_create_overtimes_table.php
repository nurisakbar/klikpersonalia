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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('employee_id');
            $table->enum('overtime_type', ['regular', 'holiday', 'weekend', 'emergency']);
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('total_hours');
            $table->decimal('hours', 4, 2)->default(0);
            $table->enum('type', ['weekday', 'weekend', 'holiday'])->default('weekday');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->decimal('rate_multiplier', 3, 2)->default(1.5); // 1.5x, 2x, 3x
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('attachment')->nullable(); // File attachment
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('company_id');
            $table->index(['employee_id', 'date']);
            $table->index('type');
            $table->index('status');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
