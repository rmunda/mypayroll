<?php
// app/Services/LeaveBalanceService.php

namespace App\Services;

use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
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

        $employees  = Employee::where('status', 'active')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                LeaveBalance::firstOrCreate(
                    [
                        'employee_id'       => $employee->id,
                        'financial_year_id' => $fy->id,
                        'leave_type_id'     => $leaveType->id,
                    ],
                    [
                        'allocated'        => $policy->getAllocationForType($leaveType->id),
                        'accrued'          => 0,
                        'used'             => 0,
                        'pending'          => 0,
                        'carried_forward'  => $this->getCarryForward($employee, $fy, $leaveType),
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

        // get all leave types that have accrual configured in the policy
        $accruingDetails = $policy->policyDetails()
                            ->where('accrual_per_month', '>', 0)
                            ->with('leaveType')
                            ->get();

        if ($accruingDetails->isEmpty()) return;

        $employees = Employee::where('status', 'active')->get();

        foreach ($employees as $employee) {
            foreach ($accruingDetails as $detail) {
                $balance = LeaveBalance::firstOrCreate(
                    [
                        'employee_id'       => $employee->id,
                        'financial_year_id' => $fy->id,
                        'leave_type_id'     => $detail->leave_type_id,
                    ],
                    ['allocated' => 0, 'accrued' => 0, 'used' => 0, 'pending' => 0]
                );

                $balance->increment('accrued', $detail->accrual_per_month);
            }
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
            ->where('leave_type_id', $leave->leave_type_id)
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
            ->where('leave_type_id', $leave->leave_type_id)
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
            ->where('leave_type_id', $leave->leave_type_id)
            ->first()
            ?->decrement('pending', $leave->days);
    }

    // -------------------------------------------
    // Carry forward earned leave to next year
    // -------------------------------------------
    private function getCarryForward(Employee $employee, FinancialYear $newFy, LeaveType $leaveType): float
    {
        $policy = LeavePolicy::where('financial_year_id', $newFy->id)
                    ->where('is_default', true)
                    ->first();

        if (!$policy || !$policy->allowsCarryForward($leaveType->id)) return 0;

        // get previous FY balance
        $prevFy = FinancialYear::where('end_date', '<', $newFy->start_date)
                    ->orderByDesc('end_date')
                    ->first();

        if (!$prevFy) return 0;

        $prevBalance = LeaveBalance::where('employee_id', $employee->id)
                        ->where('financial_year_id', $prevFy->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->first();

        if (!$prevBalance) return 0;

        // carry forward available balance up to max limit
        return min(
            $prevBalance->available,
            $policy->getMaxCarryForward($leaveType->id)
        );
    }

    // initialize balances for a single employee
    // called when new employee is onboarded mid year
    public function initializeForEmployee(Employee $employee): void
    {
        $fy = FinancialYear::current();
        if (!$fy) return;

        $policy = LeavePolicy::where('financial_year_id', $fy->id)
                    ->where('is_default', true)
                    ->first();

        if (!$policy) return;

        $leaveTypes = LeaveType::where('is_active', true)->get();

        // calculate how many months are remaining in the FY
        $monthsRemaining = now()->diffInMonths($fy->end_date) + 1;

        foreach ($leaveTypes as $leaveType) {
            $detail = LeavePolicyDetail::where('leave_policy_id', $policy->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->first();

            if (!$detail) continue;

            // pro-rate leave based on months remaining
            // e.g. joined in October = 6 months remaining in FY
            // casual leave = 12 days / 12 months * 6 = 6 days
            $proRatedDays = $detail->effectiveDaysForMonths($monthsRemaining);

            $balance = LeaveBalance::firstOrCreate(
                [
                    'employee_id'       => $employee->id,
                    'financial_year_id' => $fy->id,
                    'leave_type_id'     => $leaveType->id,
                ],
                [
                    'allocated'       => $proRatedDays,
                    'accrued'         => 0,
                    'used'            => 0,
                    'pending'         => 0,
                    'carried_forward' => 0,
                    'encashed'        => 0,
                    'lapsed'          => 0,
                ]
            );

            // log allocation
            if ($balance->wasRecentlyCreated && $proRatedDays > 0) {
                LeaveTransaction::record(
                    balance:         $balance,
                    transactionType: 'allocated',
                    days:            $proRatedDays,
                    balanceBefore:   0,
                    leaveId:         null,
                    remarks:         'Pro-rated allocation — joined ' . now()->format('d M Y')
                );
            }
        }
    }
}