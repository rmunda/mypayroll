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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('financial_year_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('leave_type_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // balance breakdown
            $table->decimal('allocated', 5, 1)->default(0);       // given at year start
            $table->decimal('accrued', 5, 1)->default(0);         // earned monthly
            $table->decimal('carried_forward', 5, 1)->default(0); // from last year
            $table->decimal('used', 5, 1)->default(0);            // approved and taken
            $table->decimal('pending', 5, 1)->default(0);         // requested not yet approved
            $table->decimal('encashed', 5, 1)->default(0);        // encashed leaves
            $table->decimal('lapsed', 5, 1)->default(0);          // expired unused leaves

            $table->timestamps();

            // one balance record per employee per year per leave type
            $table->unique([
                'employee_id',
                'financial_year_id',
                'leave_type_id',
            ], 'leave_bal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
