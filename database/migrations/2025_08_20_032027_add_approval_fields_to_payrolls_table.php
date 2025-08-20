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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->uuid('approved_by')->nullable()->after('generated_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->uuid('rejected_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->uuid('paid_by')->nullable()->after('rejected_at');
            $table->timestamp('paid_at')->nullable()->after('paid_by');
            
            // Add foreign key constraints
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
            
            // Add indexes
            $table->index('approved_by');
            $table->index('rejected_by');
            $table->index('paid_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['paid_by']);
            
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['rejected_by']);
            $table->dropIndex(['paid_by']);
            
            $table->dropColumn(['approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'paid_by', 'paid_at']);
        });
    }
};
