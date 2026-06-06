<?php

use App\Filament\Resources\Leaves\Pages\CreateLeave;
use App\Filament\Resources\Leaves\Pages\ListLeaves;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use Livewire\Livewire;

// --------------------------------------------------------------------------------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------------------------------------------------------------------------

function leaveAdminUser(): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('admin');
    return $user;
}

beforeEach(fn () => Filament::setCurrentPanel(Filament::getPanel('admin')));

// ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// ListLeaves
// ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

describe('ListLeaves', function () {

    it('renders the list page for an admin', function () {
        $this->actingAs(leaveAdminUser());

        Livewire::test(ListLeaves::class)->assertSuccessful();
    });

    it('shows leave records in the table', function () {
        Mail::fake();
        $this->actingAs(leaveAdminUser());

        $leave = Leave::factory()->create();

        Livewire::test(ListLeaves::class)
            ->assertCanSeeTableRecords([$leave]);
    });

});

// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// Leave approval workflow
// -----------------------------------------------------------------------------------------------------------------------------------------

describe('Leave approval workflow', function () {

    it('admin can acknowledge a request the status moves to pending', function () {
        Mail::fake();
        $this->actingAs(leaveAdminUser());

        $leave = Leave::factory()->create(['status' => 'request']);

        Livewire::test(ListLeaves::class)
            ->callTableAction('acknowledge', $leave);

        expect($leave->fresh()->status)->toEqual('pending');
    });

    it('admin can approve a pending request the status approved and balance updated', function () {
        Mail::fake();
        $this->actingAs(leaveAdminUser());

        $fy       = FinancialYear::factory()->current()->create();
        $employee = Employee::factory()->create();
        $type     = LeaveType::factory()->casual()->create();

        LeaveBalance::factory()->create([
            'employee_id'       => $employee->id,
            'leave_type_id'     => $type->id,
            'financial_year_id' => $fy->id,
            'pending'           => 5.0,
            'used'              => 0.0,
        ]);

        $leave = Leave::factory()->create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $type->id,
            'status'        => 'pending',
            'days'          => 2,
            'from_date'     => '2025-07-01',
            'to_date'       => '2025-07-02',
        ]);

        Livewire::test(ListLeaves::class)
            ->callTableAction('approve', $leave);

        expect($leave->fresh()->status)->toEqual('approved');

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $type->id)
            ->first();

        expect((float) $balance->pending)->toEqual(3.0);
        expect((float) $balance->used)->toEqual(2.0);
    });

    it('admin can reject a request the status moves to rejected', function () {
        Mail::fake();
        $this->actingAs(leaveAdminUser());

        $leave = Leave::factory()->create(['status' => 'request']);

        Livewire::test(ListLeaves::class)
            ->callTableAction('reject', $leave);

        expect($leave->fresh()->status)->toEqual('rejected');
    });

    it('approved_by and approved_at are recorded when approving', function () {
        Mail::fake();
        $admin = leaveAdminUser();
        $this->actingAs($admin);

        $leave = Leave::factory()->create(['status' => 'pending']);

        Livewire::test(ListLeaves::class)
            ->callTableAction('approve', $leave);

        $fresh = $leave->fresh();
        expect($fresh->approved_by)->toEqual($admin->id);
        expect($fresh->approved_at)->not->toBeNull();
    });

});

// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// CreateLeave
// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

describe('CreateLeave', function () {

    it('admin can create a leave request for an employee', function () {
        Mail::fake();
        $this->actingAs(leaveAdminUser());

        $employee = Employee::factory()->create();
        $type     = LeaveType::factory()->casual()->create();

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'employee_id'   => $employee->id,
                'leave_type_id' => $type->id,
                'from_date'     => '2025-07-01',
                'to_date'       => '2025-07-01',
                'days'          => 1,
                'reason'        => 'Personal work',
                'status'        => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Leave::where('employee_id', $employee->id)->exists())->toBeTrue();
    });

    it('employee can create a leave request for themselves', function () {
        Mail::fake();

        $employee = Employee::factory()->create();
        $user     = $employee->fresh()->user;
        $type     = LeaveType::factory()->casual()->create();

        $this->actingAs($user);

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'employee_id'   => $employee->id,
                'leave_type_id' => $type->id,
                'from_date'     => '2025-07-01',
                'to_date'       => '2025-07-01',
                'days'          => 1,
                'status'        => 'request',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Leave::where('employee_id', $employee->id)->exists())->toBeTrue();
    });

});

