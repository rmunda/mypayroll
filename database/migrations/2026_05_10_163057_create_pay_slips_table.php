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
        Schema::create('pay_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('working_days')->default(0);
            $table->decimal('present_days', 5, 1)->default(0);
            $table->integer('leave_days')->default(0);
            $table->decimal('absent_days', 5, 1)->default(0);
            // Earnings
            $table->decimal('basic', 12, 2)->default(0);
            $table->decimal('hra', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('special_allowance', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('arrears', 12, 2)->default(0);
            $table->decimal('gross_earnings', 12, 2)->default(0);
            // Deductions
            $table->decimal('pf_employee', 12, 2)->default(0);
            $table->decimal('pf_employer', 12, 2)->default(0);
            $table->decimal('esi_employee', 12, 2)->default(0);
            $table->decimal('esi_employer', 12, 2)->default(0);
            $table->decimal('professional_tax', 12, 2)->default(0);
            $table->decimal('tds', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->enum('status', ['draft','approved','paid','sent'])->default('draft');
            $table->string('pdf_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('deduction_snapshot')->nullable();
            $table->timestamps();
             $table->unique(['payroll_run_id','employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_slips');
    }
};
