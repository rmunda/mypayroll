<?php

use App\Models\LeavePolicyDetail;

// ─────────────────────────────────────────────────────────────
// isUnlimited()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicyDetail::isUnlimited()', function () {

    it('returns true when days_per_year is zero', function () {
        $detail = LeavePolicyDetail::factory()->create(['days_per_year' => 0]);
        expect($detail->isUnlimited())->toBeTrue();
    });

    it('returns false when days_per_year is greater than zero', function () {
        $detail = LeavePolicyDetail::factory()->create(['days_per_year' => 12]);
        expect($detail->isUnlimited())->toBeFalse();
    });

});

// ─────────────────────────────────────────────────────────────
// accrues()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicyDetail::accrues()', function () {

    it('returns true when accrual_per_month is greater than zero', function () {
        $detail = LeavePolicyDetail::factory()->create(['accrual_per_month' => 1.5]);
        expect($detail->accrues())->toBeTrue();
    });

    it('returns false when accrual_per_month is zero', function () {
        $detail = LeavePolicyDetail::factory()->create(['accrual_per_month' => 0]);
        expect($detail->accrues())->toBeFalse();
    });

});

// ─────────────────────────────────────────────────────────────
// annualAccrualTotal()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicyDetail::annualAccrualTotal()', function () {

    it('returns accrual_per_month multiplied by 12', function () {
        $detail = LeavePolicyDetail::factory()->create(['accrual_per_month' => 1.5]);
        expect($detail->annualAccrualTotal())->toEqual(18.0);
    });

    it('returns zero when there is no monthly accrual', function () {
        $detail = LeavePolicyDetail::factory()->create(['accrual_per_month' => 0]);
        expect($detail->annualAccrualTotal())->toEqual(0.0);
    });

});

// ─────────────────────────────────────────────────────────────
// effectiveDaysForMonths()
// ─────────────────────────────────────────────────────────────

describe('LeavePolicyDetail::effectiveDaysForMonths()', function () {

    it('pro-rates accrual-based leave by months: 1.5/month × 6 = 9', function () {
        $detail = LeavePolicyDetail::factory()->create([
            'accrual_per_month' => 1.5,
            'days_per_year'     => 0,
        ]);
        expect($detail->effectiveDaysForMonths(6))->toEqual(9.0);
    });

    it('pro-rates accrual-based leave for a partial month: 1.5 × 1 = 1.5', function () {
        $detail = LeavePolicyDetail::factory()->create(['accrual_per_month' => 1.5]);
        expect($detail->effectiveDaysForMonths(1))->toEqual(1.5);
    });

    it('pro-rates non-accrual leave by months remaining in the year', function () {
        // 12 days/year, 6 months remaining → 6 days
        $detail = LeavePolicyDetail::factory()->create([
            'days_per_year'     => 12,
            'accrual_per_month' => 0,
        ]);
        expect($detail->effectiveDaysForMonths(6))->toEqual(6.0);
    });

    it('returns full year allocation when months = 12 for non-accrual leave', function () {
        $detail = LeavePolicyDetail::factory()->create([
            'days_per_year'     => 18,
            'accrual_per_month' => 0,
        ]);
        expect($detail->effectiveDaysForMonths(12))->toEqual(18.0);
    });

    it('returns zero for non-accrual unlimited leave (days_per_year = 0)', function () {
        $detail = LeavePolicyDetail::factory()->create([
            'days_per_year'     => 0,
            'accrual_per_month' => 0,
        ]);
        expect($detail->effectiveDaysForMonths(6))->toEqual(0.0);
    });

    it('returns zero when months is zero', function () {
        $detail = LeavePolicyDetail::factory()->create([
            'days_per_year'     => 12,
            'accrual_per_month' => 0,
        ]);
        expect($detail->effectiveDaysForMonths(0))->toEqual(0.0);
    });

    it('rounds the result to one decimal place', function () {
        // 10 days / 12 months × 1 month = 0.833... → rounds to 0.8
        $detail = LeavePolicyDetail::factory()->create([
            'days_per_year'     => 10,
            'accrual_per_month' => 0,
        ]);
        expect($detail->effectiveDaysForMonths(1))->toEqual(0.8);
    });

});
