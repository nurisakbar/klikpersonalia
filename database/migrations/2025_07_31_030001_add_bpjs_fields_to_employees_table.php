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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('bpjs_kesehatan_number')->nullable()->after('position');
            $table->string('bpjs_ketenagakerjaan_number')->nullable()->after('bpjs_kesehatan_number');
            $table->boolean('bpjs_kesehatan_active')->default(true)->after('bpjs_ketenagakerjaan_number');
            $table->boolean('bpjs_ketenagakerjaan_active')->default(true)->after('bpjs_kesehatan_active');
            $table->date('bpjs_effective_date')->nullable()->after('bpjs_ketenagakerjaan_active');
            $table->text('bpjs_notes')->nullable()->after('bpjs_effective_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'bpjs_kesehatan_number',
                'bpjs_ketenagakerjaan_number',
                'bpjs_kesehatan_active',
                'bpjs_ketenagakerjaan_active',
                'bpjs_effective_date',
                'bpjs_notes'
            ]);
        });
    }
}; 