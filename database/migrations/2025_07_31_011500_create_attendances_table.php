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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('total_hours', 4, 2)->default(0);
            $table->decimal('overtime_hours', 4, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave', 'holiday'])->default('present');
            $table->text('notes')->nullable();
            $table->string('check_in_location')->nullable(); // GPS coordinates
            $table->string('check_out_location')->nullable(); // GPS coordinates
            $table->string('check_in_ip')->nullable();
            $table->string('check_out_ip')->nullable();
            $table->string('check_in_device')->nullable();
            $table->string('check_out_device')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['employee_id', 'date']);
            $table->index(['employee_id', 'date']);
            $table->index('status');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
