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
        // Update employees table
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'company_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->uuid('company_id')->after('id');
            });
        }
        
        // Drop and recreate unique constraint for employees
        if (Schema::hasTable('employees')) {
            $indexes = DB::select("SHOW INDEX FROM employees WHERE Key_name = 'employees_employee_id_unique'");
            if (!empty($indexes)) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropUnique(['employee_id']);
                });
            }
            
            $indexes = DB::select("SHOW INDEX FROM employees WHERE Key_name = 'employees_company_id_employee_id_unique'");
            if (empty($indexes)) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->unique(['company_id', 'employee_id']);
                });
            }
        }

        // Update payrolls table
        if (Schema::hasTable('payrolls') && !Schema::hasColumn('payrolls', 'company_id')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->uuid('company_id')->after('id');
            });
        }

        // Update attendances table
        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances', 'company_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->uuid('company_id')->after('id');
            });
        }
        
        // Drop and recreate unique constraint for attendances
        if (Schema::hasTable('attendances')) {
            $indexes = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_employee_id_date_unique'");
            if (!empty($indexes)) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->dropUnique(['employee_id', 'date']);
                });
            }
            
            $indexes = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_company_id_employee_id_date_unique'");
            if (empty($indexes)) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->unique(['company_id', 'employee_id', 'date']);
                });
            }
        }

        // Update leaves table
        if (Schema::hasTable('leaves') && !Schema::hasColumn('leaves', 'company_id')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->uuid('company_id')->after('id');
            });
        }

        // Update overtimes table
        if (Schema::hasTable('overtimes') && !Schema::hasColumn('overtimes', 'company_id')) {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->uuid('company_id')->after('id');
            });
        }
        
        // Drop and recreate unique constraint for overtimes
        if (Schema::hasTable('overtimes')) {
            $indexes = DB::select("SHOW INDEX FROM overtimes WHERE Key_name = 'overtimes_employee_id_date_unique'");
            if (!empty($indexes)) {
                Schema::table('overtimes', function (Blueprint $table) {
                    $table->dropUnique(['employee_id', 'date']);
                });
            }
            
            $indexes = DB::select("SHOW INDEX FROM overtimes WHERE Key_name = 'overtimes_company_id_employee_id_date_unique'");
            if (empty($indexes)) {
                Schema::table('overtimes', function (Blueprint $table) {
                    $table->unique(['company_id', 'employee_id', 'date']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert employees table
        if (Schema::hasTable('employees')) {
            $indexes = DB::select("SHOW INDEX FROM employees WHERE Key_name = 'employees_company_id_employee_id_unique'");
            if (!empty($indexes)) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropUnique(['company_id', 'employee_id']);
                });
            }
            
            $indexes = DB::select("SHOW INDEX FROM employees WHERE Key_name = 'employees_employee_id_unique'");
            if (empty($indexes)) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->unique(['employee_id']);
                });
            }
            
            if (Schema::hasColumn('employees', 'company_id')) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
            }
        }

        // Revert payrolls table
        if (Schema::hasColumn('payrolls', 'company_id')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        // Revert attendances table
        if (Schema::hasTable('attendances')) {
            $indexes = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_company_id_employee_id_date_unique'");
            if (!empty($indexes)) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->dropUnique(['company_id', 'employee_id', 'date']);
                });
            }
            
            $indexes = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_employee_id_date_unique'");
            if (empty($indexes)) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->unique(['employee_id', 'date']);
                });
            }
            
            if (Schema::hasColumn('attendances', 'company_id')) {
                Schema::table('attendances', function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
            }
        }

        // Revert leaves table
        if (Schema::hasColumn('leaves', 'company_id')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        // Revert overtimes table
        if (Schema::hasTable('overtimes')) {
            $indexes = DB::select("SHOW INDEX FROM overtimes WHERE Key_name = 'overtimes_company_id_employee_id_date_unique'");
            if (!empty($indexes)) {
                Schema::table('overtimes', function (Blueprint $table) {
                    $table->dropUnique(['company_id', 'employee_id', 'date']);
                });
            }
            
            $indexes = DB::select("SHOW INDEX FROM overtimes WHERE Key_name = 'overtimes_employee_id_date_unique'");
            if (empty($indexes)) {
                Schema::table('overtimes', function (Blueprint $table) {
                    $table->unique(['employee_id', 'date']);
                });
            }
            
            if (Schema::hasColumn('overtimes', 'company_id')) {
                Schema::table('overtimes', function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
            }
        }
    }
};
