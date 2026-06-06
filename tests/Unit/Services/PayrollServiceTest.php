<?php

use App\Models\Attendance;
use App\Models\DeductionRule;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\PayrollRun;
use App\Models\PayStructure;
use App\Models\WeeklyOffRule;
use App\Services\PayrollService;
use App\Services\TaxService;

// ─────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────

// Mon 12 – Fri 16 May 2025 — exactly 5 Mon-Fri working days, no holidays
function allWeek(): array
{
    return ['2025-05-12', '2025-05-13', '2025-05-14', '2025-05-15', '2025-05-16'];
}

// Employee created without events so the Employee::created observer
// (which calls initializeForEmployee) does not fire
function makePayrollSetup(array $salaryOverrides = [], array $structureOverrides = []): array
{
    $rule = WeeklyOffRule::factory()->default()->create();

    $structure = PayStructure::factory()->create(array_merge([
        'hra_percentage'        => 0,
        'ta_fixed'              => 0,
        'special_allowance_pct' => 0,
    ], $structureOverrides));

    $employee = Employee::withoutEvents(fn() =>
        Employee::factory()->create(array_merge([
            'basic_salary'     => 10000,
            'pay_structure_id' => $structure->id,
            'status'           => 'active',
            'tax_regime'       => 'new',
        ], $salaryOverrides))
    );

    $run = PayrollRun::factory()->create([
        'period_start' => '2025-05-12',
        'period_end'   => '2025-05-16',
    ]);

    return compact('rule', 'structure', 'employee', 'run');
}

function attendFor(Employee $employee, array $dates, string $status = 'present'): void
{
    foreach ($dates as $date) {
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date'        => $date,
            'status'      => $status,
        ]);
    }
}

// ─────────────────────────────────────────────────────────────
// DeductionRule::calculate
// ─────────────────────────────────────────────────────────────

describe('DeductionRule::calculate', function () {

    it('calculates a percentage of basic salary', function () {
        $rule = DeductionRule::factory()->create([
            'type' => 'percentage', 'value' => 12.0, 'applies_to' => 'basic',
        ]);
        expect($rule->calculate(50000, 75000))->toEqual(6000.0);
    });

    it('calculates a percentage of gross salary', function () {
        $rule = DeductionRule::factory()->create([
            'type' => 'percentage', 'value' => 0.75, 'applies_to' => 'gross',
        ]);
        // round(75000 * 0.0075, 2) = 562.5
        expect($rule->calculate(50000, 75000))->toEqual(562.5);
    });

    it('returns fixed amount regardless of salary level', function () {
        $rule = DeductionRule::factory()->create([
            'type' => 'fixed', 'value' => 200.0, 'applies_to' => 'gross',
        ]);
        expect($rule->calculate(10000, 15000))->toEqual(200.0);
        expect($rule->calculate(100000, 150000))->toEqual(200.0);
    });

    it('returns zero for unknown applies_to', function () {
        $rule = new \App\Models\DeductionRule([
            'type' => 'percentage', 'value' => 12.0, 'applies_to' => 'other',
        ]);
        expect($rule->calculate(50000, 75000))->toEqual(0.0);
    });

    it('returns zero for unknown type', function () {
        $rule = new \App\Models\DeductionRule([
            'type' => 'unknown', 'value' => 12.0, 'applies_to' => 'basic',
        ]);
        expect($rule->calculate(50000, 75000))->toEqual(0.0);
    });

});

// ─────────────────────────────────────────────────────────────
// TaxService — new regime (FY 2025-26)
// ─────────────────────────────────────────────────────────────

describe('TaxService — new regime', function () {

    it('returns zero TDS for annual gross under 7.75L (rebate u/s 87A applies)', function () {
        $emp = new Employee(['tax_regime' => 'new', 'basic_salary' => 50000]);
        // after SD (75k): 525k ≤ 700k → rebate
        expect(app(TaxService::class)->calculateMonthlyTDS($emp, 600000))->toEqual(0.0);
    });

    it('applies 5% and 10% slabs with 4% cess above the rebate threshold', function () {
        $emp = new Employee(['tax_regime' => 'new', 'basic_salary' => 75000]);
        // after SD: 825k | 300k-700k @ 5% = 20000 | 700k-825k @ 10% = 12500
        // total: 32500 * 1.04 = 33800 → monthly: round(33800/12, 2) = 2816.67
        expect(app(TaxService::class)->calculateMonthlyTDS($emp, 900000))->toEqual(2816.67);
    });

    it('reaches the 30% slab for high income', function () {
        $emp = new Employee(['tax_regime' => 'new', 'basic_salary' => 200000]);
        // after SD: 1925k
        // 20000 + 30000 + 30000 + 60000 + 127500 = 267500 * 1.04 = 278200 → monthly: 23183.33
        expect(app(TaxService::class)->calculateMonthlyTDS($emp, 2000000))->toEqual(23183.33);
    });

});

// ─────────────────────────────────────────────────────────────
// TaxService — old regime
// ─────────────────────────────────────────────────────────────

describe('TaxService — old regime', function () {

    it('returns zero TDS when taxable income is at or below 2.5L', function () {
        $emp = new Employee(['tax_regime' => 'old', 'basic_salary' => 20000]);
        // pf = 28800 | deductions = min(78800, 150000) = 78800
        // taxable = 300000 - 78800 - 50000 = 171200 ≤ 250000 → 0
        expect(app(TaxService::class)->calculateMonthlyTDS($emp, 300000))->toEqual(0.0);
    });

    it('applies 80C deduction, standard deduction, and cess', function () {
        $emp = new Employee(['tax_regime' => 'old', 'basic_salary' => 40000]);
        // pf = 57600 | deductions = min(107600, 150000) = 107600
        // taxable = 800000 - 107600 - 50000 = 642400
        // 5% on 250k-500k = 12500 | 20% on 500k-642400 = 28480 | total = 40980 * 1.04 = 42619.2
        // monthly: round(42619.2 / 12, 2) = 3551.60
        expect(app(TaxService::class)->calculateMonthlyTDS($emp, 800000))->toEqual(3551.60);
    });

});

// ─────────────────────────────────────────────────────────────
// PayrollService::process — payslip creation
// ─────────────────────────────────────────────────────────────

describe('PayrollService::process — payslip creation', function () {

    it('creates one payslip per active employee', function () {
        WeeklyOffRule::factory()->default()->create();
        $run = PayrollRun::factory()->create(['period_start' => '2025-05-12', 'period_end' => '2025-05-16']);

        Employee::withoutEvents(fn() => Employee::factory()->count(2)->create(['status' => 'active']));

        app(PayrollService::class)->process($run);

        expect($run->paySlips()->count())->toEqual(2);
    });

    it('skips inactive employees', function () {
        WeeklyOffRule::factory()->default()->create();
        $run = PayrollRun::factory()->create(['period_start' => '2025-05-12', 'period_end' => '2025-05-16']);

        Employee::withoutEvents(fn() => Employee::factory()->create(['status' => 'active']));
        Employee::withoutEvents(fn() => Employee::factory()->create(['status' => 'inactive']));

        app(PayrollService::class)->process($run);

        expect($run->paySlips()->count())->toEqual(1);
    });

    it('does not create duplicate payslips when process is called twice', function () {
        WeeklyOffRule::factory()->default()->create();
        $run = PayrollRun::factory()->create(['period_start' => '2025-05-12', 'period_end' => '2025-05-16']);

        Employee::withoutEvents(fn() => Employee::factory()->create(['status' => 'active']));

        app(PayrollService::class)->process($run);
        app(PayrollService::class)->process($run);

        expect($run->paySlips()->count())->toEqual(1);
    });

    it('sets run status to processing', function () {
        WeeklyOffRule::factory()->default()->create();
        $run = PayrollRun::factory()->create(['period_start' => '2025-05-12', 'period_end' => '2025-05-16']);

        Employee::withoutEvents(fn() => Employee::factory()->create(['status' => 'active']));

        app(PayrollService::class)->process($run);

        expect($run->fresh()->status)->toEqual('processing');
    });

});

// ─────────────────────────────────────────────────────────────
// PayrollService::process — earnings calculation
// ─────────────────────────────────────────────────────────────

describe('PayrollService::process — earnings calculation', function () {

    it('computes gross correctly when all days are present (factor = 1)', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup(
            ['basic_salary' => 10000],
            ['hra_percentage' => 40, 'ta_fixed' => 500, 'special_allowance_pct' => 10]
        );

        attendFor($emp, allWeek());

        app(PayrollService::class)->process($run);

        $slip = $run->paySlips()->where('employee_id', $emp->id)->first();

        // factor = 5/5 = 1 → basic=10000, hra=4000, ta=500, special=1000, gross=15500
        expect((float) $slip->basic)->toEqual(10000.0);
        expect((float) $slip->hra)->toEqual(4000.0);
        expect((float) $slip->transport_allowance)->toEqual(500.0);
        expect((float) $slip->special_allowance)->toEqual(1000.0);
        expect((float) $slip->gross_earnings)->toEqual(15500.0);
    });

    it('pro-rates salary proportionally when employee has absent days', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup(['basic_salary' => 10000]);

        // 4 present out of 5 working days → factor 0.8
        attendFor($emp, ['2025-05-12', '2025-05-13', '2025-05-14', '2025-05-15']);

        app(PayrollService::class)->process($run);

        $slip = $run->paySlips()->where('employee_id', $emp->id)->first();

        expect((float) $slip->basic)->toEqual(8000.0);
        expect((float) $slip->present_days)->toEqual(4.0);
        expect((float) $slip->absent_days)->toEqual(1.0);
    });

    it('treats leave days as worked so salary is not reduced', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup(['basic_salary' => 10000]);

        attendFor($emp, ['2025-05-12', '2025-05-13', '2025-05-14', '2025-05-15']);
        Attendance::factory()->create(['employee_id' => $emp->id, 'date' => '2025-05-16', 'status' => 'on_leave']);

        app(PayrollService::class)->process($run);

        $slip = $run->paySlips()->where('employee_id', $emp->id)->first();

        // (4 present + 1 leave) / 5 = 1 → full salary, 0 absent days
        expect((float) $slip->basic)->toEqual(10000.0);
        expect((float) $slip->leave_days)->toEqual(1.0);
        expect((float) $slip->absent_days)->toEqual(0.0);
    });

    it('counts a half day as 0.5 present days', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup(['basic_salary' => 10000]);

        attendFor($emp, ['2025-05-12', '2025-05-13', '2025-05-14', '2025-05-15']);
        Attendance::factory()->create(['employee_id' => $emp->id, 'date' => '2025-05-16', 'status' => 'half_day']);

        app(PayrollService::class)->process($run);

        $slip = $run->paySlips()->where('employee_id', $emp->id)->first();

        // present_days = 4.5, factor = 0.9 → basic = 9000
        expect((float) $slip->present_days)->toEqual(4.5);
        expect((float) $slip->basic)->toEqual(9000.0);
    });

});

// ─────────────────────────────────────────────────────────────
// PayrollService::process — deductions
// ─────────────────────────────────────────────────────────────

describe('PayrollService::process — deductions', function () {

    it('applies PF (12% basic) and professional tax (fixed 200)', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup(
            ['basic_salary' => 10000, 'tax_regime' => 'new']
        );

        DeductionRule::factory()->pf()->create();
        DeductionRule::factory()->professionalTax()->create();

        attendFor($emp, allWeek());

        app(PayrollService::class)->process($run);

        $slip = $run->paySlips()->where('employee_id', $emp->id)->first();

        // basic=10000, pf=1200, pt=200, tds=0 (annual 120k → under rebate threshold)
        expect((float) $slip->pf_employee)->toEqual(1200.0);
        expect((float) $slip->professional_tax)->toEqual(200.0);
        expect((float) $slip->tds)->toEqual(0.0);
        expect((float) $slip->total_deductions)->toEqual(1400.0);
        expect((float) $slip->net_pay)->toEqual(8600.0);
    });

    it('applies TDS for employees with high annual income', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup(
            ['basic_salary' => 200000, 'tax_regime' => 'new']
        );

        attendFor($emp, allWeek());

        app(PayrollService::class)->process($run);

        $slip = $run->paySlips()->where('employee_id', $emp->id)->first();

        // gross=200000, annual=2.4M → well above 7.75L → TDS > 0
        expect((float) $slip->tds)->toBeGreaterThan(0.0);
    });

    it('stores a deduction snapshot keyed by rule name', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup(['basic_salary' => 10000]);

        DeductionRule::factory()->pf()->create();

        attendFor($emp, allWeek());

        app(PayrollService::class)->process($run);

        $slip = $run->paySlips()->where('employee_id', $emp->id)->first();

        expect($slip->deduction_snapshot)->toHaveKey('Provident Fund (Employee)');
        expect($slip->deduction_snapshot['Provident Fund (Employee)'])->toEqual(1200.0);
    });

});

// ─────────────────────────────────────────────────────────────
// PayrollService::process — working days and run totals
// ─────────────────────────────────────────────────────────────

describe('PayrollService::process — working days', function () {

    it('counts 5 working days in a Mon-Fri week', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup();

        app(PayrollService::class)->process($run);

        expect($run->paySlips()->where('employee_id', $emp->id)->value('working_days'))->toEqual(5);
    });

    it('excludes holidays from the working days count', function () {
        ['employee' => $emp, 'run' => $run] = makePayrollSetup();

        // Reuse the FY already created by PayrollRunFactory to avoid duplicate label
        Holiday::factory()->create([
            'financial_year_id' => $run->financial_year_id,
            'date'              => '2025-05-14', // Wednesday — normally a working day
        ]);

        app(PayrollService::class)->process($run);

        expect($run->paySlips()->where('employee_id', $emp->id)->value('working_days'))->toEqual(4);
    });

});

describe('PayrollService::process — run totals', function () {

    it('sums gross, deductions, and net across all employees', function () {
        $structure = PayStructure::factory()->create([
            'hra_percentage' => 0, 'ta_fixed' => 0, 'special_allowance_pct' => 0,
        ]);
        WeeklyOffRule::factory()->default()->create();
        $run = PayrollRun::factory()->create(['period_start' => '2025-05-12', 'period_end' => '2025-05-16']);

        $emp1 = Employee::withoutEvents(fn() => Employee::factory()->create([
            'basic_salary' => 10000, 'pay_structure_id' => $structure->id,
            'status' => 'active', 'tax_regime' => 'new',
        ]));
        $emp2 = Employee::withoutEvents(fn() => Employee::factory()->create([
            'basic_salary' => 20000, 'pay_structure_id' => $structure->id,
            'status' => 'active', 'tax_regime' => 'new',
        ]));

        attendFor($emp1, allWeek());
        attendFor($emp2, allWeek());

        app(PayrollService::class)->process($run);
        $run->refresh();

        // Both fully present, no deduction rules, TDS=0 for both (annual 120k / 240k → under rebate)
        expect((float) $run->total_gross)->toEqual(30000.0);
        expect((float) $run->total_deductions)->toEqual(0.0);
        expect((float) $run->total_net)->toEqual(30000.0);
    });

});
