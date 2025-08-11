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
        $this->dropForeignKeys();
        
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
        
        // Recreate foreign key constraints
        $this->createForeignKeys();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints first
        $this->dropForeignKeys();
        
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
        
        // Recreate foreign key constraints
        $this->createForeignKeys();
    }
    
    /**
     * Drop all foreign key constraints
     */
    private function dropForeignKeys(): void
    {
        $tables = ['employees', 'payrolls', 'attendances', 'leaves', 'overtimes'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '{$table}' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                foreach ($foreignKeys as $foreignKey) {
                    Schema::table($table, function (Blueprint $table) use ($foreignKey) {
                        $table->dropForeign($foreignKey->CONSTRAINT_NAME);
                    });
                }
            }
        }
    }
    
    /**
     * Create all foreign key constraints
     */
    private function createForeignKeys(): void
    {
        // Payrolls foreign keys
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            });
        }
        
        // Attendances foreign keys
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            });
        }
        
        // Leaves foreign keys
        if (Schema::hasTable('leaves')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        }
        
        // Overtimes foreign keys
        if (Schema::hasTable('overtimes')) {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }
};
