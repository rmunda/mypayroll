<?php

use App\Filament\Resources\PayrollRuns\Pages\CreatePayrollRun;
use App\Filament\Resources\PayrollRuns\Pages\ListPayrollRuns;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\PayrollRun;
use App\Models\PaySlip;
use App\Models\PayStructure;
use App\Models\User;
use App\Models\WeeklyOffRule;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use Livewire\Livewire;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Helpers
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

function payrollAdminUser(): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('admin');
    return $user;
}

function makeActiveEmployee(): Employee
{
    $structure = PayStructure::factory()->create([
        'hra_percentage'        => 0,
        'ta_fixed'              => 0,
        'special_allowance_pct' => 0,
    ]);

    WeeklyOffRule::factory()->create(['is_default' => true]);

    return Employee::withoutEvents(fn () =>
        Employee::factory()->create([
            'basic_salary'     => 20000,
            'pay_structure_id' => $structure->id,
            'status'           => 'active',
            'tax_regime'       => 'new',
        ])
    );
}

beforeEach(fn () => Filament::setCurrentPanel(Filament::getPanel('admin')));

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ListPayrollRuns
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

describe('ListPayrollRuns', function () {

    it('renders the list page for an admin', function () {
        $this->actingAs(payrollAdminUser());

        Livewire::test(ListPayrollRuns::class)->assertSuccessful();
    });

    it('shows payroll run records in the table', function () {
        $this->actingAs(payrollAdminUser());

        $run = PayrollRun::factory()->create();

        Livewire::test(ListPayrollRuns::class)
            ->assertCanSeeTableRecords([$run]);
    });

});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// CreatePayrollRun
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

describe('CreatePayrollRun', function () {

    it('renders the create page', function () {
        $this->actingAs(payrollAdminUser());

        Livewire::test(CreatePayrollRun::class)->assertSuccessful();
    });

    it('creates a draft payroll run with valid form data', function () {
        $this->actingAs(payrollAdminUser());

        $fy = FinancialYear::factory()->create();

        Livewire::test(CreatePayrollRun::class)
            ->fillForm([
                'financial_year_id' => $fy->id,
                'period_label'      => 'May 2025',
                'period_start'      => '2025-05-01',
                'period_end'        => '2025-05-31',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(PayrollRun::where('period_label', 'May 2025')->exists())->toBeTrue();
    });

});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Payroll workflow actions
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

describe('Payroll workflow actions', function () {

    it('processing a draft run changes status to processing', function () {
        $this->actingAs(payrollAdminUser());

        makeActiveEmployee();

        $run = PayrollRun::factory()->create([
            'period_start' => '2025-05-12',
            'period_end'   => '2025-05-16',
        ]);

        Livewire::test(ListPayrollRuns::class)
            ->callTableAction('process', $run);

        expect($run->fresh()->status)->toEqual('processing');
    });

    it('processing a run creates pay slips for all active employees', function () {
        $this->actingAs(payrollAdminUser());

        $employee = makeActiveEmployee();

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date'        => '2025-05-12',
            'status'      => 'present',
        ]);

        $run = PayrollRun::factory()->create([
            'period_start' => '2025-05-12',
            'period_end'   => '2025-05-12',
        ]);

        Livewire::test(ListPayrollRuns::class)
            ->callTableAction('process', $run);

        expect(PaySlip::where('payroll_run_id', $run->id)->count())->toEqual(1);
    });

    it('approving a processed run changes status to approved', function () {
        $this->actingAs(payrollAdminUser());

        $run = PayrollRun::factory()->create(['status' => 'processing']);

        Livewire::test(ListPayrollRuns::class)
            ->callTableAction('approve', $run);

        expect($run->fresh()->status)->toEqual('approved');
    });

    it('a draft run cannot be approved directly', function () {
        $this->actingAs(payrollAdminUser());

        $run = PayrollRun::factory()->create(['status' => 'draft']);

        Livewire::test(ListPayrollRuns::class)
            ->assertTableActionHidden('approve', $run);
    });

});

