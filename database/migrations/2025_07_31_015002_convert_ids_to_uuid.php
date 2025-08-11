<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign key constraints first
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['approved_by']);
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['approved_by']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        // Convert users table ID to UUID
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('id')->change();
        });

        // Convert employees table ID to UUID
        Schema::table('employees', function (Blueprint $table) {
            $table->uuid('id')->change();
        });

        // Convert payrolls table ID to UUID
        Schema::table('payrolls', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->uuid('employee_id')->change();
        });

        // Convert attendances table ID to UUID
        Schema::table('attendances', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->uuid('employee_id')->change();
        });

        // Convert leaves table ID to UUID
        Schema::table('leaves', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->uuid('employee_id')->change();
            $table->uuid('approved_by')->nullable()->change();
        });

        // Convert overtimes table ID to UUID
        Schema::table('overtimes', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->uuid('employee_id')->change();
            $table->uuid('approved_by')->nullable()->change();
        });

        // Recreate foreign key constraints with UUID references
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints first
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['approved_by']);
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['approved_by']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });

        // Revert users table
        Schema::table('users', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        // Revert employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        // Revert payrolls table
        Schema::table('payrolls', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
            $table->unsignedBigInteger('employee_id')->change();
        });

        // Revert attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
            $table->unsignedBigInteger('employee_id')->change();
        });

        // Revert leaves table
        Schema::table('leaves', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
            $table->unsignedBigInteger('employee_id')->change();
            $table->unsignedBigInteger('approved_by')->nullable()->change();
        });

        // Revert overtimes table
        Schema::table('overtimes', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
            $table->unsignedBigInteger('employee_id')->change();
            $table->unsignedBigInteger('approved_by')->nullable()->change();
        });

        // Recreate original foreign key constraints
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('overtimes', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
