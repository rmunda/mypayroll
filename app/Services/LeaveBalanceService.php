<?php
// app/Services/LeaveBalanceService.php

namespace App\Services;

use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\Leave;

class LeaveBalanceService
{
    // -------------------------------------------
    // Initialize balances for all employees
    // Run at start of financial year
    // -------------------------------------------
    public function initializeForYear(FinancialYear $fy): void
    {
        $policy    = LeavePolicy::where('financial_year_id', $fy->id)
                        ->where('is_default', true)
                        ->firstOrFail();

        $employees = Employee::where('status', 'active')->get();
        $types     = ['casual', 'sick', 'earned', 'maternity', 'unpaid'];

        foreach ($employees as $employee) {
            foreach ($types as $type) {
                LeaveBalance::firstOrCreate(
                    [
                        'employee_id'       => $employee->id,
                        'financial_year_id' => $fy->id,
                        'leave_type'        => $type,
                    ],
                    [
                        'allocated'        => $policy->getAllocation($type),
                        'accrued'          => 0,
                        'used'             => 0,
                        'pending'          => 0,
                        'carried_forward'  => $this->getCarryForward($employee, $fy, $type),
                    ]
                );
            }
        }
    }

    // -------------------------------------------
    // Monthly accrual for earned leave
    // Run on 1st of every month via scheduler
    // -------------------------------------------
    public function accrueMonthlyEarnedLeave(): void
    {
        $fy     = FinancialYear::current();
        $policy = LeavePolicy::where('financial_year_id', $fy->id)
                    ->where('is_default', true)
                    ->first();

        if (!$policy || !$policy->earned_leave_accrual) return;

        $employees = Employee::where('status', 'active')->get();

        foreach ($employees as $employee) {
            $balance = LeaveBalance::firstOrCreate(
                [
                    'employee_id'       => $employee->id,
                    'financial_year_id' => $fy->id,
                    'leave_type'        => 'earned',
                ],
                ['allocated' => 0, 'accrued' => 0, 'used' => 0, 'pending' => 0]
            );

            $balance->increment('accrued', $policy->earned_accrual_per_month);
        }
    }

    // -------------------------------------------
    // When leave is REQUESTED — add to pending
    // -------------------------------------------
    public function onLeaveRequested(Leave $leave): void
    {
        $fy = FinancialYear::forDate($leave->from_date);
        if (!$fy) return;

        LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('financial_year_id', $fy->id)
            ->where('leave_type', $leave->type)
            ->increment('pending', $leave->days);
    }

    // -------------------------------------------
    // When leave is APPROVED — move pending to used
    // -------------------------------------------
    public function onLeaveApproved(Leave $leave): void
    {
        $fy = FinancialYear::forDate($leave->from_date);
        if (!$fy) return;

        LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('financial_year_id', $fy->id)
            ->where('leave_type', $leave->type)
            ->first()
            ?->update([
                'pending' => \DB::raw("GREATEST(pending - {$leave->days}, 0)"),
                'used'    => \DB::raw("used + {$leave->days}"),
            ]);
    }

    // -------------------------------------------
    // When leave is REJECTED or CANCELLED
    // remove from pending
    // -------------------------------------------
    public function onLeaveCancelled(Leave $leave): void
    {
        $fy = FinancialYear::forDate($leave->from_date);
        if (!$fy) return;

        LeaveBalance::where('employee_id', $leave->employee_id)
            ->where('financial_year_id', $fy->id)
            ->where('leave_type', $leave->type)
            ->first()
            ?->decrement('pending', $leave->days);
    }

    // -------------------------------------------
    // Carry forward earned leave to next year
    // -------------------------------------------
    private function getCarryForward(Employee $employee, FinancialYear $newFy, string $type): float
    {
        if ($type !== 'earned') return 0;

        $policy = LeavePolicy::where('financial_year_id', $newFy->id)
                    ->where('is_default', true)
                    ->first();

        if (!$policy || !$policy->carry_forward_earned) return 0;

        // get previous FY balance
        $prevFy = FinancialYear::where('end_date', '<', $newFy->start_date)
                    ->orderByDesc('end_date')
                    ->first();

        if (!$prevFy) return 0;

        $prevBalance = LeaveBalance::where('employee_id', $employee->id)
                        ->where('financial_year_id', $prevFy->id)
                        ->where('leave_type', 'earned')
                        ->first();

        if (!$prevBalance) return 0;

        // carry forward available balance up to max limit
        return min(
            $prevBalance->available,
            $policy->max_carry_forward_days
        );
    }
}