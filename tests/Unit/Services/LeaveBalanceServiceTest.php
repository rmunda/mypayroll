<?php

test('sanity check', fn () => expect(true)->toBeTrue());

use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;

// ─────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────

function makeBaseSetup(): array
{
    $fy       = FinancialYear::factory()->current()->create();
    $employee = Employee::factory()->create();
    $type     = LeaveType::factory()->casual()->create();
    $policy   = LeavePolicy::factory()->for($fy)->create();

    LeavePolicyDetail::factory()->create([
        'leave_policy_id' => $policy->id,
        'leave_type_id'   => $type->id,
        'days_per_year'   => 12,
        'accrual_per_month' => 0,
        'carry_forward'   => false,
    ]);

    $balance = LeaveBalance::factory()->create([
        'employee_id'       => $employee->id,
        'financial_year_id' => $fy->id,
        'leave_type_id'     => $type->id,
        'allocated'         => 12,
        'used'              => 0,
        'pending'           => 0,
    ]);

    return compact('fy', 'employee', 'type', 'policy', 'balance');
}

// ─────────────────────────────────────────────────────────────
// onLeaveRequested
// ─────────────────────────────────────────────────────────────

describe('onLeaveRequested', function () {

    it('increments pending when leave is requested', function () {
        ['employee' => $employee, 'fy' => $fy, 'type' => $type, 'balance' => $balance] = makeBaseSetup();

        $leave = Leave::factory()->create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $type->id,
            'from_date'     => '2025-06-01',
            'to_date'       => '2025-06-02',
            'days'          => 2,
            'status'        => 'request',
        ]);

        app(LeaveBalanceService::class)->onLeaveRequested($leave);

        expect($balance->fresh()->pending)->toEqual(2.0);
    });

    it('does not affect used balance when leave is requested', function () {
        ['employee' => $employee, 'fy' => $fy, 'type' => $type, 'balance' => $balance] = makeBaseSetup();

        $leave = Leave::factory()->create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $type->id,
            'from_date'     => '2025-06-01',
            'to_date'       => '2025-06-01',
            'days'          => 1,
            'status'        => 'request',
        ]);

        app(LeaveBalanceService::class)->onLeaveRequested($leave);

        expect($balance->fresh()->used)->toEqual(0.0);
    });

});

// ─────────────────────────────────────────────────────────────
// onLeaveApproved
// ─────────────────────────────────────────────────────────────

describe('onLeaveApproved', function () {

    it('moves pending to used when leave is approved', function () {
        ['employee' => $employee, 'fy' => $fy, 'type' => $type, 'balance' => $balance] = makeBaseSetup();

        $balance->update(['pending' => 3]);

        $leave = Leave::factory()->create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $type->id,
            'from_date'     => '2025-06-01',
            'to_date'       => '2025-06-03',
            'days'          => 3,
            'status'        => 'approved',
        ]);

        app(LeaveBalanceService::class)->onLeaveApproved($leave);

        $fresh = $balance->fresh();
        expect($fresh->pending)->toEqual(0.0);
        expect($fresh->used)->toEqual(3.0);
    });

    it('does not make pending negative if already zero', function () {
        ['employee' => $employee, 'fy' => $fy, 'type' => $type, 'balance' => $balance] = makeBaseSetup();

        $leave = Leave::factory()->create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $type->id,
            'from_date'     => '2025-06-01',
            'to_date'       => '2025-06-02',
            'days'          => 2,
            'status'        => 'approved',
        ]);

        app(LeaveBalanceService::class)->onLeaveApproved($leave);

        expect($balance->fresh()->pending)->toBeGreaterThanOrEqual(0.0);
    });

});

// ─────────────────────────────────────────────────────────────
// onLeaveCancelled
// ─────────────────────────────────────────────────────────────

describe('onLeaveCancelled', function () {

    it('decrements pending when leave is cancelled', function () {
        ['employee' => $employee, 'fy' => $fy, 'type' => $type, 'balance' => $balance] = makeBaseSetup();

        $balance->update(['pending' => 5]);

        $leave = Leave::factory()->create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $type->id,
            'from_date'     => '2025-06-01',
            'to_date'       => '2025-06-02',
            'days'          => 2,
            'status'        => 'cancelled',
        ]);

        app(LeaveBalanceService::class)->onLeaveCancelled($leave);

        expect($balance->fresh()->pending)->toEqual(3.0);
    });

    it('does not decrement used balance when leave is cancelled', function () {
        ['employee' => $employee, 'fy' => $fy, 'type' => $type, 'balance' => $balance] = makeBaseSetup();

        $balance->update(['pending' => 2, 'used' => 4]);

        $leave = Leave::factory()->create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $type->id,
            'from_date'     => '2025-06-01',
            'to_date'       => '2025-06-01',
            'days'          => 1,
            'status'        => 'cancelled',
        ]);

        app(LeaveBalanceService::class)->onLeaveCancelled($leave);

        expect($balance->fresh()->used)->toEqual(4.0);
    });

});

// ─────────────────────────────────────────────────────────────
// LeaveBalance computed attribute
// ─────────────────────────────────────────────────────────────

describe('LeaveBalance::available', function () {

    it('calculates available correctly', function () {
        $balance = LeaveBalance::factory()->create([
            'allocated'       => 12,
            'accrued'         => 3,
            'carried_forward' => 2,
            'used'            => 4,
            'pending'         => 1,
            'encashed'        => 0,
            'lapsed'          => 0,
        ]);

        // available = 12 + 3 + 2 - 4 - 1 = 12
        expect($balance->available)->toEqual(12.0);
    });

    it('never returns negative available', function () {
        $balance = LeaveBalance::factory()->create([
            'allocated' => 5,
            'used'      => 10,
            'pending'   => 0,
        ]);

        expect($balance->available)->toEqual(0.0);
    });

    it('hasSufficientBalance returns true when enough balance exists', function () {
        $balance = LeaveBalance::factory()->create([
            'allocated' => 10,
            'used'      => 2,
            'pending'   => 0,
        ]);

        expect($balance->hasSufficientBalance(5))->toBeTrue();
        expect($balance->hasSufficientBalance(8))->toBeTrue();
        expect($balance->hasSufficientBalance(9))->toBeFalse();
    });

});

// ─────────────────────────────────────────────────────────────
// initializeForEmployee
// ─────────────────────────────────────────────────────────────

describe('initializeForEmployee', function () {

    it('creates leave balances for all active leave types', function () {
        $fy      = FinancialYear::factory()->current()->create();
        $policy  = LeavePolicy::factory()->for($fy)->create();
        $type1   = LeaveType::factory()->casual()->create();
        $type2   = LeaveType::factory()->sick()->create();

        LeavePolicyDetail::factory()->create([
            'leave_policy_id'  => $policy->id,
            'leave_type_id'    => $type1->id,
            'days_per_year'    => 12,
            'accrual_per_month'=> 0,
        ]);

        LeavePolicyDetail::factory()->create([
            'leave_policy_id'  => $policy->id,
            'leave_type_id'    => $type2->id,
            'days_per_year'    => 8,
            'accrual_per_month'=> 0,
        ]);

        $employee = Employee::factory()->create();

        app(LeaveBalanceService::class)->initializeForEmployee($employee);

        expect(LeaveBalance::where('employee_id', $employee->id)->count())->toEqual(2);
    });

    it('does not create duplicate balances when called twice', function () {
        $fy     = FinancialYear::factory()->current()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->casual()->create();

        LeavePolicyDetail::factory()->create([
            'leave_policy_id'  => $policy->id,
            'leave_type_id'    => $type->id,
            'days_per_year'    => 12,
            'accrual_per_month'=> 0,
        ]);

        $employee = Employee::factory()->create();

        app(LeaveBalanceService::class)->initializeForEmployee($employee);
        app(LeaveBalanceService::class)->initializeForEmployee($employee);

        expect(LeaveBalance::where('employee_id', $employee->id)->count())->toEqual(1);
    });

    it('returns early without error when no current financial year exists', function () {
        $employee = Employee::factory()->create();

        // no FinancialYear created — current() returns null
        expect(fn () => app(LeaveBalanceService::class)->initializeForEmployee($employee))
            ->not->toThrow(Exception::class);
    });

});
