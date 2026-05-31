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
        Schema::create('leave_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('financial_year_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('leave_balance_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('leave_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
            $table->foreignId('leave_type_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->enum('transaction_type', [
                'allocated',      // initial allocation at year start
                'accrued',        // monthly earned leave credit
                'carry_forward',  // brought from previous year
                'debit',          // leave taken
                'credit',         // leave reversed (cancelled/rejected)
                'adjustment',     // manual HR adjustment
                'encashment',     // leave encashed
                'lapsed',         // unused leave expired
            ]);

            $table->decimal('days', 5, 1);
            $table->decimal('balance_before', 6, 1);
            $table->decimal('balance_after', 6, 1);
            $table->string('remarks')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // indexes for faster queries
            $table->index(['employee_id', 'financial_year_id']);
            $table->index(['leave_balance_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_transactions');
    }
};
