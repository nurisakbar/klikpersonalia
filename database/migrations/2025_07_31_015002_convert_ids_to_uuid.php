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
        // Convert users table ID to UUID
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('id')->change();
        });

        // Convert employees table ID to UUID
        Schema::table('employees', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->uuid('user_id')->nullable()->change();
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert users table
        Schema::table('users', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        // Revert employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
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
    }
};
