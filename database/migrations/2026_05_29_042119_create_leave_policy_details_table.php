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
        Schema::create('leave_policy_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_policy_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('leave_type_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // allocation per year for this leave type
            $table->decimal('days_per_year', 5, 1)->default(0);

            // carry forward specific to this leave type
            $table->boolean('carry_forward')->default(false);
            $table->decimal('max_carry_forward', 5, 1)->default(0);

            // accrual specific to this leave type
            $table->decimal('accrual_per_month', 4, 2)->default(0);

            // encashment
            $table->boolean('allow_encashment')->default(false);
            $table->decimal('max_encashment_days', 5, 1)->default(0);

            $table->timestamps();

            // one entry per leave type per policy
            $table->unique(['leave_policy_id', 'leave_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policy_details');
    }
};
