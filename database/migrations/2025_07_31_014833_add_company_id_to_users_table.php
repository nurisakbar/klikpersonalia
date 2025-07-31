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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('company_id')->nullable()->after('id');
            $table->enum('role', ['super_admin', 'admin', 'hr', 'manager', 'employee'])->default('employee')->after('password');
            $table->boolean('is_company_owner')->default(false)->after('role');
            $table->string('phone')->nullable()->after('email');
            $table->string('position')->nullable()->after('phone');
            $table->string('department')->nullable()->after('position');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('department');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->string('last_login_device')->nullable()->after('last_login_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'company_id',
                'role',
                'is_company_owner',
                'phone',
                'position',
                'department',
                'status',
                'last_login_at',
                'last_login_ip',
                'last_login_device'
            ]);
        });
    }
};
