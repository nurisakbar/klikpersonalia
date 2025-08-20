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
        // Drop global unique constraints that are not suitable for multi-tenant
        if (Schema::hasTable('employees')) {
            // Drop global email unique constraint
            $indexes = DB::select("SHOW INDEX FROM employees WHERE Key_name = 'employees_email_unique'");
            if (!empty($indexes)) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropUnique(['email']);
                });
            }
            
            // Create company-scoped unique constraints
            Schema::table('employees', function (Blueprint $table) {
                // Email should be unique within a company
                $table->unique(['company_id', 'email'], 'employees_company_id_email_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees')) {
            // Drop company-scoped unique constraints
            $indexes = DB::select("SHOW INDEX FROM employees WHERE Key_name = 'employees_company_id_email_unique'");
            if (!empty($indexes)) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropUnique(['company_id', 'email']);
                });
            }
            
            // Restore global email unique constraint
            Schema::table('employees', function (Blueprint $table) {
                $table->unique(['email'], 'employees_email_unique');
            });
        }
    }
};
