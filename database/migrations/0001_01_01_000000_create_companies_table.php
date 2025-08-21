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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code');
            $table->string('country')->default('Indonesia');
            $table->string('website')->nullable();
            $table->string('tax_number')->nullable(); // NPWP
            $table->string('business_number')->nullable(); // SIUP/NIB
            $table->string('logo')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->enum('subscription_plan', ['free', 'basic', 'premium', 'enterprise'])->default('free');
            $table->date('subscription_start')->nullable();
            $table->date('subscription_end')->nullable();
            $table->integer('max_employees')->default(10);
            $table->boolean('is_trial')->default(true);
            $table->date('trial_ends_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('email');
            $table->index('status');
            $table->index('subscription_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
