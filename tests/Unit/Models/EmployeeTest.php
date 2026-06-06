<?php

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\FinancialYear;
use App\Models\LeaveType;
use App\Models\PayStructure;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

// ─────────────────────────────────────────────────────────────
// Computed attributes: hra and grossSalary
// ─────────────────────────────────────────────────────────────

describe('Employee computed attributes', function () {

    it('computes hra as a percentage of basic salary', function () {
        $structure = PayStructure::factory()->create(['hra_percentage' => 40]);
        $employee  = Employee::withoutEvents(fn() =>
            Employee::factory()->create([
                'basic_salary'     => 50000,
                'pay_structure_id' => $structure->id,
            ])
        );

        expect($employee->hra)->toEqual(20000.0);
    });

    it('computes grossSalary as basic + hra + ta_fixed', function () {
        $structure = PayStructure::factory()->create([
            'hra_percentage' => 40,
            'ta_fixed'       => 1500,
        ]);
        $employee = Employee::withoutEvents(fn() =>
            Employee::factory()->create([
                'basic_salary'     => 50000,
                'pay_structure_id' => $structure->id,
            ])
        );

        // 50000 + 20000 (40%) + 1500 = 71500
        expect($employee->grossSalary)->toEqual(71500.0);
    });

});

// ─────────────────────────────────────────────────────────────
// Employee::created observer — user creation
// ─────────────────────────────────────────────────────────────

describe('Employee::created observer — user creation', function () {

    it('creates a User account with the same email when employee is created', function () {
        Mail::fake();

        $employee = Employee::factory()->create();

        expect(User::where('email', $employee->email)->exists())->toBeTrue();
    });

    it('links the created user back to the employee', function () {
        Mail::fake();

        $employee = Employee::factory()->create();

        expect($employee->fresh()->user_id)->not->toBeNull();
        expect($employee->fresh()->user->email)->toEqual($employee->email);
    });

    it('assigns the employee role to the created user', function () {
        Mail::fake();

        $employee = Employee::factory()->create();
        $user     = User::where('email', $employee->email)->first();

        expect($user->hasRole('employee'))->toBeTrue();
    });

    it('reuses an existing user if the email already exists', function () {
        Mail::fake();

        $existing = User::factory()->create();
        $employee = Employee::factory()->create(['email' => $existing->email]);

        // no duplicate user created
        expect(User::where('email', $existing->email)->count())->toEqual(1);
        expect($employee->fresh()->user_id)->toEqual($existing->id);
    });

    it('sends a welcome email to the new employee', function () {
        Mail::fake();

        $employee = Employee::factory()->create();

        Mail::assertSent(\App\Mail\WelcomeMail::class, fn ($mail) =>
            $mail->hasTo($employee->email)
        );
    });

});

// ─────────────────────────────────────────────────────────────
// Employee::created observer — leave balance initialization
// ─────────────────────────────────────────────────────────────

describe('Employee::created observer — leave balance initialization', function () {

    it('creates leave balances for all active leave types when a current FY and policy exist', function () {
        Mail::fake();

        $fy     = FinancialYear::factory()->current()->create();
        $type1  = LeaveType::factory()->casual()->create();
        $type2  = LeaveType::factory()->sick()->create();

        // policy created BEFORE employee so the observer can find it
        $policy = LeavePolicy::factory()->for($fy)->create();

        LeavePolicyDetail::factory()->create([
            'leave_policy_id'   => $policy->id,
            'leave_type_id'     => $type1->id,
            'days_per_year'     => 12,
            'accrual_per_month' => 0,
        ]);
        LeavePolicyDetail::factory()->create([
            'leave_policy_id'   => $policy->id,
            'leave_type_id'     => $type2->id,
            'days_per_year'     => 8,
            'accrual_per_month' => 0,
        ]);

        $employee = Employee::factory()->create();

        expect(LeaveBalance::where('employee_id', $employee->id)->count())->toEqual(2);
    });

    it('does not create leave balances when no current financial year exists', function () {
        Mail::fake();

        // no FY → initializeForEmployee returns early
        $employee = Employee::factory()->create();

        expect(LeaveBalance::where('employee_id', $employee->id)->count())->toEqual(0);
    });

    it('auto-generates employee code when none is provided', function () {
        Mail::fake();

        $employee = Employee::factory()->create(['employee_code' => '']);

        expect($employee->fresh()->employee_code)->toMatch('/^EMP-\d{3}$/');
    });

});
