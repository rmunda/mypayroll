<?php

use App\Models\WeeklyOffRule;
use Carbon\Carbon;

// May 2025 Saturdays for weekOfMonth tests:
//   1st = May 3 | 2nd = May 10 | 3rd = May 17 | 4th = May 24 | 5th = May 31

// ─────────────────────────────────────────────────────────────
// isWorkingDay — weekdays
// ─────────────────────────────────────────────────────────────

describe('WeeklyOffRule::isWorkingDay — weekdays', function () {

    it('returns true for a weekday enabled in the rule', function () {
        $rule = WeeklyOffRule::factory()->create(['monday' => true]);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-12')))->toBeTrue(); // Monday
    });

    it('returns false for a weekday disabled in the rule', function () {
        $rule = WeeklyOffRule::factory()->create(['friday' => false]);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-16')))->toBeFalse(); // Friday
    });

    it('returns false for Sunday when sunday is off', function () {
        $rule = WeeklyOffRule::factory()->create(['sunday' => false]);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-18')))->toBeFalse(); // Sunday
    });

    it('returns true for Sunday when sunday is on', function () {
        $rule = WeeklyOffRule::factory()->create(['sunday' => true]);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-18')))->toBeTrue();
    });

});

// ─────────────────────────────────────────────────────────────
// isWorkingDay — Saturday variants
// ─────────────────────────────────────────────────────────────

describe('WeeklyOffRule::isWorkingDay — Saturday', function () {

    it('returns false immediately when saturday=false regardless of saturday_type', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => false, 'saturday_type' => 'working']);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-17')))->toBeFalse();
    });

    it('returns true when saturday_type is working', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'working']);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-17')))->toBeTrue();
    });

    it('returns true when saturday_type is half_day', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'half_day']);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-17')))->toBeTrue();
    });

    it('returns false when saturday_type is non_working', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'non_working']);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-17')))->toBeFalse();
    });

    it('returns true for 1st and 3rd Saturdays with alternate_1_3 rule', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'alternate_1_3']);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-03')))->toBeTrue();  // 1st
        expect($rule->isWorkingDay(Carbon::parse('2025-05-17')))->toBeTrue();  // 3rd
    });

    it('returns false for 2nd and 4th Saturdays with alternate_1_3 rule', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'alternate_1_3']);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-10')))->toBeFalse(); // 2nd
        expect($rule->isWorkingDay(Carbon::parse('2025-05-24')))->toBeFalse(); // 4th
    });

    it('returns true for 2nd and 4th Saturdays with alternate_2_4 rule', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'alternate_2_4']);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-10')))->toBeTrue();  // 2nd
        expect($rule->isWorkingDay(Carbon::parse('2025-05-24')))->toBeTrue();  // 4th
    });

    it('returns false for 1st and 3rd Saturdays with alternate_2_4 rule', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'alternate_2_4']);
        expect($rule->isWorkingDay(Carbon::parse('2025-05-03')))->toBeFalse();  // 1st
        expect($rule->isWorkingDay(Carbon::parse('2025-05-17')))->toBeFalse();  // 3rd
    });

});

// ─────────────────────────────────────────────────────────────
// isHalfDay
// ─────────────────────────────────────────────────────────────

describe('WeeklyOffRule::isHalfDay', function () {

    it('returns true for Saturday when saturday_type is half_day', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'half_day']);
        expect($rule->isHalfDay(Carbon::parse('2025-05-17')))->toBeTrue();
    });

    it('returns false for Saturday when saturday_type is working', function () {
        $rule = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'working']);
        expect($rule->isHalfDay(Carbon::parse('2025-05-17')))->toBeFalse();
    });

    it('returns false for any weekday', function () {
        $rule = WeeklyOffRule::factory()->create();
        expect($rule->isHalfDay(Carbon::parse('2025-05-12')))->toBeFalse(); // Monday
        expect($rule->isHalfDay(Carbon::parse('2025-05-14')))->toBeFalse(); // Wednesday
    });

});

// ─────────────────────────────────────────────────────────────
// countWorkingDays
// ─────────────────────────────────────────────────────────────

describe('WeeklyOffRule::countWorkingDays', function () {

    it('counts 5 working days in a Mon-Fri week with Mon-Fri rule', function () {
        $rule = WeeklyOffRule::factory()->create(); // Mon-Fri on, Sat-Sun off
        $count = $rule->countWorkingDays(Carbon::parse('2025-05-12'), Carbon::parse('2025-05-16'));
        expect($count)->toEqual(5);
    });

    it('excludes Saturday and Sunday from a full calendar week', function () {
        $rule  = WeeklyOffRule::factory()->create(); // Sat-Sun off
        $count = $rule->countWorkingDays(Carbon::parse('2025-05-12'), Carbon::parse('2025-05-18'));
        expect($count)->toEqual(5); // Mon-Fri only
    });

    it('includes Saturday when saturday_type is working', function () {
        $rule  = WeeklyOffRule::factory()->create(['saturday' => true, 'saturday_type' => 'working']);
        $count = $rule->countWorkingDays(Carbon::parse('2025-05-12'), Carbon::parse('2025-05-17'));
        expect($count)->toEqual(6); // Mon-Fri + Sat
    });

    it('excludes a date in the holidays array', function () {
        $rule  = WeeklyOffRule::factory()->create();
        $count = $rule->countWorkingDays(
            Carbon::parse('2025-05-12'),
            Carbon::parse('2025-05-16'),
            ['2025-05-14'] // Wednesday holiday
        );
        expect($count)->toEqual(4);
    });

    it('returns zero for a single-day range that is a weekend', function () {
        $rule  = WeeklyOffRule::factory()->create(); // Sat-Sun off
        $count = $rule->countWorkingDays(Carbon::parse('2025-05-17'), Carbon::parse('2025-05-17'));
        expect($count)->toEqual(0); // Saturday
    });

    it('returns one for a single-day range that is a weekday', function () {
        $rule  = WeeklyOffRule::factory()->create();
        $count = $rule->countWorkingDays(Carbon::parse('2025-05-12'), Carbon::parse('2025-05-12'));
        expect($count)->toEqual(1); // Monday
    });

});
