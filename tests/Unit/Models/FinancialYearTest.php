<?php

use App\Models\FinancialYear;
use App\Models\Holiday;
use App\Models\PayrollRun;

// FY factory default: 2025-04-01 → 2026-03-31

// ─────────────────────────────────────────────────────────────
// FinancialYear::current()
// ─────────────────────────────────────────────────────────────

describe('FinancialYear::current()', function () {

    it('returns the financial year marked as current', function () {
        $fy = FinancialYear::factory()->current()->create();
        expect(FinancialYear::current()?->id)->toEqual($fy->id);
    });

    it('returns null when no financial year is marked as current', function () {
        FinancialYear::factory()->create(['is_current' => false]);
        expect(FinancialYear::current())->toBeNull();
    });

    it('returns the first current FY when multiple exist', function () {
        $first  = FinancialYear::factory()->current()->create();
        FinancialYear::factory()->current()->create([
            'label'      => 'FY 2026-27',
            'start_date' => '2026-04-01',
            'end_date'   => '2027-03-31',
        ]);
        expect(FinancialYear::current()?->id)->toEqual($first->id);
    });

});

// ─────────────────────────────────────────────────────────────
// FinancialYear::forDate()
// ─────────────────────────────────────────────────────────────

describe('FinancialYear::forDate()', function () {

    it('returns the FY when the date falls within its range', function () {
        $fy = FinancialYear::factory()->create();
        expect(FinancialYear::forDate(\Carbon\Carbon::parse('2025-06-15'))?->id)->toEqual($fy->id);
    });

    it('returns the FY when the date is on start_date', function () {
        $fy = FinancialYear::factory()->create();
        expect(FinancialYear::forDate(\Carbon\Carbon::parse('2025-04-01'))?->id)->toEqual($fy->id);
    });

    it('returns the FY when the date is on end_date', function () {
        $fy = FinancialYear::factory()->create();
        expect(FinancialYear::forDate(\Carbon\Carbon::parse('2026-03-31'))?->id)->toEqual($fy->id);
    });

    it('returns null when the date is before the FY start', function () {
        FinancialYear::factory()->create();
        expect(FinancialYear::forDate(\Carbon\Carbon::parse('2025-03-31')))->toBeNull();
    });

    it('returns null when the date is after the FY end', function () {
        FinancialYear::factory()->create();
        expect(FinancialYear::forDate(\Carbon\Carbon::parse('2026-04-01')))->toBeNull();
    });

    it('returns null when no FY exists', function () {
        expect(FinancialYear::forDate(\Carbon\Carbon::parse('2025-06-01')))->toBeNull();
    });

});

// ─────────────────────────────────────────────────────────────
// FinancialYear::markAsCurrent()
// ─────────────────────────────────────────────────────────────

describe('FinancialYear::markAsCurrent()', function () {

    it('sets is_current to true on the target FY', function () {
        $fy = FinancialYear::factory()->create(['is_current' => false]);
        $fy->markAsCurrent();
        expect($fy->fresh()->is_current)->toBeTrue();
    });

    it('unsets is_current on all other FYs', function () {
        $old = FinancialYear::factory()->current()->create();
        $new = FinancialYear::factory()->create([
            'label'      => 'FY 2026-27',
            'start_date' => '2026-04-01',
            'end_date'   => '2027-03-31',
            'is_current' => false,
        ]);

        $new->markAsCurrent();

        expect($old->fresh()->is_current)->toBeFalse();
        expect($new->fresh()->is_current)->toBeTrue();
    });

});

// ─────────────────────────────────────────────────────────────
// FinancialYear::canBeEdited() and canBeDeleted()
// ─────────────────────────────────────────────────────────────

describe('FinancialYear::canBeEdited()', function () {

    it('returns true when no payroll runs or holidays are linked', function () {
        $fy = FinancialYear::factory()->create();
        expect($fy->canBeEdited())->toBeTrue();
    });

    it('returns false when a payroll run is linked', function () {
        $fy = FinancialYear::factory()->create();
        PayrollRun::factory()->create(['financial_year_id' => $fy->id]);
        expect($fy->canBeEdited())->toBeFalse();
    });

    it('returns false when a holiday is linked', function () {
        $fy = FinancialYear::factory()->create();
        Holiday::factory()->create(['financial_year_id' => $fy->id]);
        expect($fy->canBeEdited())->toBeFalse();
    });

});

describe('FinancialYear::canBeDeleted()', function () {

    it('returns true when no payroll runs or holidays are linked', function () {
        $fy = FinancialYear::factory()->create();
        expect($fy->canBeDeleted())->toBeTrue();
    });

    it('returns false when a payroll run is linked', function () {
        $fy = FinancialYear::factory()->create();
        PayrollRun::factory()->create(['financial_year_id' => $fy->id]);
        expect($fy->canBeDeleted())->toBeFalse();
    });

    it('returns false when a holiday is linked', function () {
        $fy = FinancialYear::factory()->create();
        Holiday::factory()->create(['financial_year_id' => $fy->id]);
        expect($fy->canBeDeleted())->toBeFalse();
    });

});
