<?php

use App\Models\FinancialYear;
use App\Models\Holiday;
use Carbon\Carbon;

// ─────────────────────────────────────────────────────────────
// Holiday::isHoliday()
// ─────────────────────────────────────────────────────────────

describe('Holiday::isHoliday()', function () {

    it('returns true when a holiday exists on the given date', function () {
        Holiday::factory()->create(['date' => '2025-08-15']);
        expect(Holiday::isHoliday(Carbon::parse('2025-08-15')))->toBeTrue();
    });

    it('returns false when no holiday exists on the given date', function () {
        expect(Holiday::isHoliday(Carbon::parse('2025-08-16')))->toBeFalse();
    });

    it('is date-specific and does not match neighbouring dates', function () {
        Holiday::factory()->create(['date' => '2025-08-15']);
        expect(Holiday::isHoliday(Carbon::parse('2025-08-14')))->toBeFalse();
        expect(Holiday::isHoliday(Carbon::parse('2025-08-16')))->toBeFalse();
    });

});

// ─────────────────────────────────────────────────────────────
// Holiday::forPeriod()
// ─────────────────────────────────────────────────────────────

describe('Holiday::forPeriod()', function () {

    it('returns all holidays within the given date range', function () {
        $fy = FinancialYear::factory()->create();
        Holiday::factory()->create(['financial_year_id' => $fy->id, 'date' => '2025-05-12']);
        Holiday::factory()->create(['financial_year_id' => $fy->id, 'date' => '2025-05-14']);
        Holiday::factory()->create(['financial_year_id' => $fy->id, 'date' => '2025-05-20']); // outside range

        $holidays = Holiday::forPeriod('2025-05-12', '2025-05-16');

        expect($holidays->count())->toEqual(2);
    });

    it('includes holidays on the boundary dates', function () {
        $fy = FinancialYear::factory()->create();
        Holiday::factory()->create(['financial_year_id' => $fy->id, 'date' => '2025-05-12']);
        Holiday::factory()->create(['financial_year_id' => $fy->id, 'date' => '2025-05-16']);

        $holidays = Holiday::forPeriod('2025-05-12', '2025-05-16');

        expect($holidays->count())->toEqual(2);
    });

    it('returns an empty collection when no holidays exist in the period', function () {
        $holidays = Holiday::forPeriod('2025-05-12', '2025-05-16');
        expect($holidays->count())->toEqual(0);
    });

});
