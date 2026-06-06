<?php

use App\Filament\Resources\Leaves\Pages\ListLeaves;
use App\Filament\Resources\PayrollRuns\Pages\ListPayrollRuns;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\PayrollRun;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use Livewire\Livewire;

beforeEach(fn () => Filament::setCurrentPanel(Filament::getPanel('admin')));

// -------------------------------------------------------------
// Unauthenticated access
// -------------------------------------------------------------

describe('Unauthenticated access', function () {

    it('redirects unauthenticated users away from the admin panel', function () {
        $this->get('/admin')->assertRedirect();
    });

    it('redirects unauthenticated users away from the employees page', function () {
        $this->get('/admin/employees')->assertRedirect();
    });

    it('redirects unauthenticated users away from the leaves page', function () {
        $this->get('/admin/leaves')->assertRedirect();
    });

    it('redirects unauthenticated users away from the payroll runs page', function () {
        $this->get('/admin/payroll-runs')->assertRedirect();
    });

});

// -------------------------------------------------------------
// Inactive user
// -------------------------------------------------------------

describe('Inactive user', function () {

    it('prevents an inactive user from accessing the panel', function () {
        $user = User::factory()->create(['is_active' => false]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $this->get('/admin')->assertForbidden();
    });

    it('allows an active user to access the panel', function () {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');

        $this->actingAs($user);

        $this->get('/admin')->assertSuccessful();
    });

});

// -------------------------------------------------------------
// Employee role - leave action visibility
// -------------------------------------------------------------

describe('Employee role - leave action visibility', function () {

    it('employee cannot see the approve action on a leave', function () {
        Mail::fake();

        $employee = Employee::factory()->create();
        $this->actingAs($employee->fresh()->user);

        $leave = Leave::factory()->create(['employee_id' => $employee->id, 'status' => 'request']);

        Livewire::test(ListLeaves::class)
            ->assertTableActionHidden('approve', $leave);
    });

    it('employee cannot see the reject action on a leave', function () {
        Mail::fake();

        $employee = Employee::factory()->create();
        $this->actingAs($employee->fresh()->user);

        $leave = Leave::factory()->create(['employee_id' => $employee->id, 'status' => 'request']);

        Livewire::test(ListLeaves::class)
            ->assertTableActionHidden('reject', $leave);
    });

    it('employee cannot see the acknowledge action on a leave', function () {
        Mail::fake();

        $employee = Employee::factory()->create();
        $this->actingAs($employee->fresh()->user);

        $leave = Leave::factory()->create(['employee_id' => $employee->id, 'status' => 'request']);

        Livewire::test(ListLeaves::class)
            ->assertTableActionHidden('acknowledge', $leave);
    });

    it('employee can see the cancel action on their own pending request', function () {
        Mail::fake();

        $employee = Employee::factory()->create();
        $this->actingAs($employee->fresh()->user);

        $leave = Leave::factory()->create([
            'employee_id' => $employee->id,
            'status'      => 'request',
        ]);

        Livewire::test(ListLeaves::class)
            ->assertTableActionVisible('cancel', $leave);
    });

});

// -------------------------------------------------------------
// Employee role - payroll access (employee has no ViewAny:PayrollRun)
// -------------------------------------------------------------

describe('Employee role - payroll access', function () {

    it('employee cannot access the payroll runs page', function () {
        Mail::fake();

        $employee = Employee::factory()->create();
        $this->actingAs($employee->fresh()->user);

        $this->get('/admin/payroll-runs')->assertForbidden();
    });

    it('employee cannot access the payroll run create page', function () {
        Mail::fake();

        $employee = Employee::factory()->create();
        $this->actingAs($employee->fresh()->user);

        $this->get('/admin/payroll-runs/create')->assertForbidden();
    });

});

// -------------------------------------------------------------
// Admin and HR role - full access
// -------------------------------------------------------------

describe('Admin and HR role - resource access', function () {

    it('admin can access the employees list', function () {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');
        $this->actingAs($user);

        Livewire::test(ListEmployees::class)->assertSuccessful();
    });

    it('admin can see the approve action on a pending leave', function () {
        Mail::fake();

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');
        $this->actingAs($user);

        $leave = Leave::factory()->create(['status' => 'pending']);

        Livewire::test(ListLeaves::class)
            ->assertTableActionVisible('approve', $leave);
    });

    it('hr can see the acknowledge action on a requested leave', function () {
        Mail::fake();

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('hr');
        $this->actingAs($user);

        $leave = Leave::factory()->create(['status' => 'request']);

        Livewire::test(ListLeaves::class)
            ->assertTableActionVisible('acknowledge', $leave);
    });

    it('admin can see the process action on a draft payroll run', function () {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');
        $this->actingAs($user);

        $run = PayrollRun::factory()->create(['status' => 'draft']);

        Livewire::test(ListPayrollRuns::class)
            ->assertTableActionVisible('process', $run);
    });

});
