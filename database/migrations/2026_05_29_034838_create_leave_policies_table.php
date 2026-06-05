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
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);

            // accrual settings
            $table->boolean('earned_leave_accrual')->default(true);
            $table->decimal('earned_accrual_per_month', 4, 2)->default(1.5);
            $table->enum('accrual_frequency', [
                'monthly',
                'quarterly',
            ])->default('monthly');

            // carry forward settings
            $table->boolean('carry_forward_earned')->default(true);
            $table->decimal('max_carry_forward_days', 5, 1)->default(30);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
