<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PaySlip;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaySlipFactory extends Factory
{
    protected $model = PaySlip::class;

    public function definition(): array
    {
        $basic   = 50000;
        $hra     = 20000;
        $ta      = 1500;
        $special = 5000;
        $gross   = $basic + $hra + $ta + $special;
        $pf      = round($basic * 0.12, 2);
        $pt      = 200;
        $tds     = 0;
        $totalDeductions = $pf + $pt + $tds;

        return [
            'payroll_run_id'      => PayrollRun::factory(),
            'employee_id'         => Employee::factory(),
            'working_days'        => 26,
            'present_days'        => 26,
            'leave_days'          => 0,
            'absent_days'         => 0,
            'basic'               => $basic,
            'hra'                 => $hra,
            'transport_allowance' => $ta,
            'special_allowance'   => $special,
            'gross_earnings'      => $gross,
            'pf_employee'         => $pf,
            'pf_employer'         => $pf,
            'esi_employee'        => 0,
            'esi_employer'        => 0,
            'professional_tax'    => $pt,
            'tds'                 => $tds,
            'other_deductions'    => 0,
            'total_deductions'    => $totalDeductions,
            'net_pay'             => $gross - $totalDeductions,
            'status'              => 'draft',
            'deduction_snapshot'  => [],
        ];
    }
}
