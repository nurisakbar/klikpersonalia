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
        // Update employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->uuid('company_id')->after('id');
            $table->dropUnique(['employee_id']);
            $table->unique(['company_id', 'employee_id']);
        });

        // Update payrolls table
        Schema::table('payrolls', function (Blueprint $table) {
            $table->uuid('company_id')->after('id');
        });

        // Update attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->uuid('company_id')->after('id');
            $table->dropUnique(['employee_id', 'date']);
            $table->unique(['company_id', 'employee_id', 'date']);
        });

        // Update leaves table
        Schema::table('leaves', function (Blueprint $table) {
            $table->uuid('company_id')->after('id');
        });

        // Update overtimes table
        Schema::table('overtimes', function (Blueprint $table) {
            $table->uuid('company_id')->after('id');
            $table->unique(['company_id', 'employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'employee_id']);
            $table->unique(['employee_id']);
            $table->dropColumn('company_id');
        });

        // Revert payrolls table
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });

        // Revert attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'employee_id', 'date']);
            $table->unique(['employee_id', 'date']);
            $table->dropColumn('company_id');
        });

        // Revert leaves table
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });

        // Revert overtimes table
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'employee_id', 'date']);
            $table->dropColumn('company_id');
        });
    }
};
