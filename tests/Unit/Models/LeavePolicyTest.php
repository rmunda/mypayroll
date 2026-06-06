<?php

use App\Models\FinancialYear;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeaveType;

// ─────────────────────────────────────────────────────────────
// LeavePolicy::getAllocationForType()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicy::getAllocationForType()', function () {

    it('returns days_per_year from the policy detail for the given leave type', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->casual()->create();

        LeavePolicyDetail::factory()->create([
            'leave_policy_id' => $policy->id,
            'leave_type_id'   => $type->id,
            'days_per_year'   => 15,
        ]);

        expect($policy->getAllocationForType($type->id))->toEqual(15.0);
    });

    it('returns 0 when no policy detail exists for the leave type', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->casual()->create();

        // no policy detail created
        expect($policy->getAllocationForType($type->id))->toEqual(0.0);
    });

});

// ─────────────────────────────────────────────────────────────
// LeavePolicy::getAccrualForType()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicy::getAccrualForType()', function () {

    it('returns accrual_per_month from the policy detail', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->earned()->create();

        LeavePolicyDetail::factory()->create([
            'leave_policy_id'   => $policy->id,
            'leave_type_id'     => $type->id,
            'accrual_per_month' => 1.5,
        ]);

        expect($policy->getAccrualForType($type->id))->toEqual(1.5);
    });

    it('returns 0 when no policy detail exists', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->earned()->create();

        expect($policy->getAccrualForType($type->id))->toEqual(0.0);
    });

});

// ─────────────────────────────────────────────────────────────
// LeavePolicy::allowsCarryForward()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicy::allowsCarryForward()', function () {

    it('returns true when policy detail has carry_forward enabled', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->earned()->create();

        LeavePolicyDetail::factory()->create([
            'leave_policy_id' => $policy->id,
            'leave_type_id'   => $type->id,
            'carry_forward'   => true,
        ]);

        expect($policy->allowsCarryForward($type->id))->toBeTrue();
    });

    it('returns false when carry_forward is disabled', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->casual()->create();

        LeavePolicyDetail::factory()->create([
            'leave_policy_id' => $policy->id,
            'leave_type_id'   => $type->id,
            'carry_forward'   => false,
        ]);

        expect($policy->allowsCarryForward($type->id))->toBeFalse();
    });

    it('returns false when no policy detail exists for the type', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->casual()->create();

        expect($policy->allowsCarryForward($type->id))->toBeFalse();
    });

});

// ─────────────────────────────────────────────────────────────
// LeavePolicy::getMaxCarryForward()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicy::getMaxCarryForward()', function () {

    it('returns max_carry_forward from the policy detail', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->earned()->create();

        LeavePolicyDetail::factory()->create([
            'leave_policy_id'  => $policy->id,
            'leave_type_id'    => $type->id,
            'carry_forward'    => true,
            'max_carry_forward'=> 30,
        ]);

        expect($policy->getMaxCarryForward($type->id))->toEqual(30.0);
    });

    it('returns 0 when no policy detail exists', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create();
        $type   = LeaveType::factory()->earned()->create();

        expect($policy->getMaxCarryForward($type->id))->toEqual(0.0);
    });

});

// ─────────────────────────────────────────────────────────────
// LeavePolicy::markAsDefault()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicy::markAsDefault()', function () {

    it('sets the policy as default', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create(['is_default' => false]);

        $policy->markAsDefault();

        expect($policy->fresh()->is_default)->toBeTrue();
    });

    it('unsets is_default on all other policies for the same FY', function () {
        $fy      = FinancialYear::factory()->create();
        $current = LeavePolicy::factory()->for($fy)->create(['is_default' => true]);
        $new     = LeavePolicy::factory()->for($fy)->create(['is_default' => false]);

        $new->markAsDefault();

        expect($current->fresh()->is_default)->toBeFalse();
        expect($new->fresh()->is_default)->toBeTrue();
    });

    it('does not affect policies for a different FY', function () {
        $fy1 = FinancialYear::factory()->create(['label' => 'FY 2025-26', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31']);
        $fy2 = FinancialYear::factory()->create(['label' => 'FY 2026-27', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31']);

        $policyFy1 = LeavePolicy::factory()->for($fy1)->create(['is_default' => true]);
        $policyFy2 = LeavePolicy::factory()->for($fy2)->create(['is_default' => false]);

        $policyFy2->markAsDefault();

        // FY1's policy should be unaffected
        expect($policyFy1->fresh()->is_default)->toBeTrue();
        expect($policyFy2->fresh()->is_default)->toBeTrue();
    });

});

// ─────────────────────────────────────────────────────────────
// LeavePolicy::current() and forYear()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicy::current()', function () {

    it('returns the default policy for the current financial year', function () {
        $fy     = FinancialYear::factory()->current()->create();
        $policy = LeavePolicy::factory()->for($fy)->create(['is_default' => true]);

        expect(LeavePolicy::current()?->id)->toEqual($policy->id);
    });

    it('returns null when no current FY exists', function () {
        FinancialYear::factory()->create(['is_current' => false]);
        expect(LeavePolicy::current())->toBeNull();
    });

    it('returns null when current FY has no default policy', function () {
        $fy = FinancialYear::factory()->current()->create();
        LeavePolicy::factory()->for($fy)->create(['is_default' => false]);

        expect(LeavePolicy::current())->toBeNull();
    });

});

describe('LeavePolicy::forYear()', function () {

    it('returns the default policy for the given FY', function () {
        $fy     = FinancialYear::factory()->create();
        $policy = LeavePolicy::factory()->for($fy)->create(['is_default' => true]);

        expect(LeavePolicy::forYear($fy)?->id)->toEqual($policy->id);
    });

    it('returns null when no default policy exists for the FY', function () {
        $fy = FinancialYear::factory()->create();
        LeavePolicy::factory()->for($fy)->create(['is_default' => false]);

        expect(LeavePolicy::forYear($fy))->toBeNull();
    });

});
