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
        // Update existing payrolls that don't have generated_by and generated_at
        $payrolls = DB::table('payrolls')
            ->whereNull('generated_by')
            ->orWhereNull('generated_at')
            ->get();

        foreach ($payrolls as $payroll) {
            // Get the company's admin user or first user
            $user = DB::table('users')
                ->where('company_id', $payroll->company_id)
                ->whereIn('role', ['admin', 'hr'])
                ->first();

            if (!$user) {
                // If no admin/hr user, get any user from the company
                $user = DB::table('users')
                    ->where('company_id', $payroll->company_id)
                    ->first();
            }

            if ($user) {
                DB::table('payrolls')
                    ->where('id', $payroll->id)
                    ->update([
                        'generated_by' => $user->id,
                        'generated_at' => $payroll->created_at ?? now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's just data update
    }
};
