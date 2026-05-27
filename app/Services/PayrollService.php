<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PaySlip;
use App\Models\DeductionRule;
use App\Models\Attendance;
use App\Models\Holiday;
use Carbon\Carbon;

class PayrollService
{
    public function __construct(protected TaxService $taxService) {}

    public function process(PayrollRun $run): void
    {
        $run->update(['status' => 'processing', 'processed_by' => auth()->id()]);

        $employees      = Employee::where('status','active')->with('payStructure')->get();
        $deductionRules = DeductionRule::where('is_active',true)->get();
        $workingDays    = $this->getWorkingDays($run->period_start, $run->period_end);

        foreach ($employees as $employee) {
            $this->calculatePaySlip($run, $employee, $deductionRules, $workingDays);
        }

        $run->refresh();
        $run->update([
            'total_gross'      => $run->paySlips()->sum('gross_earnings'),
            'total_deductions' => $run->paySlips()->sum('total_deductions'),
            'total_net'        => $run->paySlips()->sum('net_pay'),
        ]);
    }

    protected function calculatePaySlip(PayrollRun $run, Employee $emp, $rules, int $workingDays): PaySlip
    {
        // Attendance for this period
        $attendance  = Attendance::where('employee_id', $emp->id)
                        ->whereBetween('date', [$run->period_start, $run->period_end])
                        ->get();

        $presentDays = $attendance->where('status','present')->count()
                     + ($attendance->where('status','half_day')->count() * 0.5);
        $leaveDays   = $attendance->where('status','on_leave')->count();
        $absentDays  = max(0, $workingDays - $presentDays - $leaveDays);

        // Pro-rata factor (absent days reduce salary)
        $factor = $workingDays > 0 ? ($presentDays + $leaveDays) / $workingDays : 1;

        // Earnings
        $basic   = round($emp->basic_salary * $factor, 2);
        $hra     = round($basic * ($emp->payStructure->hra_percentage / 100), 2);
        $ta      = round($emp->payStructure->ta_fixed * $factor, 2);
        $special = round($basic * ($emp->payStructure->special_allowance_pct / 100), 2);
        $gross   = $basic + $hra + $ta + $special;

        // Deductions
        $pfEmp = $pfEr = $esiEmp = $esiEr = $pt = $other = 0;
        $snapshot = [];

        foreach ($rules as $rule) {
            $amount = $rule->calculate($basic, $gross);
            $snapshot[$rule->name] = $amount;

            if (str_contains($rule->name, 'Provident Fund (Employee)')) $pfEmp  += $amount;
            elseif (str_contains($rule->name, 'Provident Fund (Employer)')) $pfEr += $amount;
            elseif (str_contains($rule->name, 'ESI (Employee)'))            $esiEmp += $amount;
            elseif (str_contains($rule->name, 'ESI (Employer)'))            $esiEr += $amount;
            elseif (str_contains($rule->name, 'Professional Tax'))          $pt += $amount;
            else                                                             $other += $amount;
        }

        $tds             = $this->taxService->calculateMonthlyTDS($emp, $gross * 12);
        $totalDeductions = $pfEmp + $esiEmp + $pt + $tds + $other;
        $netPay          = round($gross - $totalDeductions, 2);

        return PaySlip::updateOrCreate(
            ['payroll_run_id' => $run->id, 'employee_id' => $emp->id],
            [
                'working_days'        => $workingDays,
                'present_days'        => $presentDays,
                'leave_days'          => $leaveDays,
                'absent_days'         => $absentDays,
                'basic'               => $basic,
                'hra'                 => $hra,
                'transport_allowance' => $ta,
                'special_allowance'   => $special,
                'gross_earnings'      => $gross,
                'pf_employee'         => $pfEmp,
                'pf_employer'         => $pfEr,
                'esi_employee'        => $esiEmp,
                'esi_employer'        => $esiEr,
                'professional_tax'    => $pt,
                'tds'                 => $tds,
                'other_deductions'    => $other,
                'total_deductions'    => $totalDeductions,
                'net_pay'             => $netPay,
                'deduction_snapshot'  => $snapshot,
                'status'              => 'draft',
            ]
        );
    }

    protected function getWorkingDays(Carbon $start, Carbon $end): int
    {
        // Get all holidays in this period
        $holidays = Holiday::forPeriod($start, $end)
                    ->pluck('date')
                    ->map(fn($d) => $d->format('Y-m-d'))
                    ->toArray(); 

        $days = 0;
        $cur  = $start->copy();
        while ($cur->lte($end)) {
            // Skip weekends AND holidays
            if (!$cur->isWeekend() && !in_array($cur->format('Y-m-d'), $holidays)) $days++;
            $cur->addDay();
        }
        return $days;
    }
}