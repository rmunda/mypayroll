<?php

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayStructure;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use Livewire\Livewire;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// Helpers
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

function adminUser(): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('admin');
    return $user;
}

function employeeFormData(Department $dept, PayStructure $structure, array $overrides = []): array
{
    return array_merge([
        'name'             => 'John Doe',
        'email'            => 'john.doe@example.com',
        'department_id'    => $dept->id,
        'designation'      => 'Software Engineer',
        'pay_structure_id' => $structure->id,
        'date_of_joining'  => '2025-04-01',
        'basic_salary'     => 30000,
        'tax_regime'       => 'new',
        'status'           => 'active',
    ], $overrides);
}

beforeEach(fn () => Filament::setCurrentPanel(Filament::getPanel('admin')));

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ListEmployees
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

describe('ListEmployees', function () {

    it('renders the list page for an admin', function () {
        $this->actingAs(adminUser());

        Livewire::test(ListEmployees::class)->assertSuccessful();
    });

    it('shows employee records in the table', function () {
        Mail::fake();
        $this->actingAs(adminUser());

        $employee = Employee::factory()->create();

        Livewire::test(ListEmployees::class)
            ->assertCanSeeTableRecords([$employee]);
    });

    it('does not show soft-deleted employees in the default view', function () {
        Mail::fake();
        $this->actingAs(adminUser());

        $active  = Employee::factory()->create();
        $deleted = Employee::factory()->create();
        $deleted->delete();

        Livewire::test(ListEmployees::class)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$deleted]);
    });

});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// CreateEmployee
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

describe('CreateEmployee', function () {

    it('renders the create page for an admin', function () {
        $this->actingAs(adminUser());

        Livewire::test(CreateEmployee::class)->assertSuccessful();
    });

    it('creates an employee record with valid form data', function () {
        Mail::fake();
        $this->actingAs(adminUser());

        $dept      = Department::factory()->create();
        $structure = PayStructure::factory()->create();

        Livewire::test(CreateEmployee::class)
            ->fillForm(employeeFormData($dept, $structure))
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Employee::where('email', 'john.doe@example.com')->exists())->toBeTrue();
    });

    it('auto-creates a User account with the employee role', function () {
        Mail::fake();
        $this->actingAs(adminUser());

        $dept      = Department::factory()->create();
        $structure = PayStructure::factory()->create();

        Livewire::test(CreateEmployee::class)
            ->fillForm(employeeFormData($dept, $structure, ['email' => 'jane.doe@example.com']))
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'jane.doe@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->hasRole('employee'))->toBeTrue();
    });

    it('sends a welcome email to the new employee', function () {
        Mail::fake();
        $this->actingAs(adminUser());

        $dept      = Department::factory()->create();
        $structure = PayStructure::factory()->create();

        Livewire::test(CreateEmployee::class)
            ->fillForm(employeeFormData($dept, $structure, ['email' => 'bob.smith@example.com']))
            ->call('create')
            ->assertHasNoFormErrors();

        Mail::assertSent(\App\Mail\WelcomeMail::class, fn ($mail) =>
            $mail->hasTo('bob.smith@example.com')
        );
    });

    it('fails validation when required fields are missing', function () {
        $this->actingAs(adminUser());

        Livewire::test(CreateEmployee::class)
            ->fillForm(['name' => '', 'email' => ''])
            ->call('create')
            ->assertHasFormErrors(['name', 'email']);
    });

});

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// EditEmployee
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

describe('EditEmployee', function () {

    it('renders the edit page for an existing employee', function () {
        Mail::fake();
        $this->actingAs(adminUser());

        $employee = Employee::factory()->create();

        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->assertSuccessful();
    });

    it('updates the basic salary successfully', function () {
        Mail::fake();
        $this->actingAs(adminUser());

        $employee = Employee::factory()->create(['basic_salary' => 30000]);

        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->fillForm(['basic_salary' => 45000])
            ->call('save')
            ->assertHasNoFormErrors();

        expect((float) $employee->fresh()->basic_salary)->toEqual(45000.0);
    });

    it('fails validation when email is changed to a duplicate', function () {
        Mail::fake();
        $this->actingAs(adminUser());

        $other    = Employee::factory()->create();
        $employee = Employee::factory()->create(['email' => 'unique@example.com']);

        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->fillForm(['email' => $other->email])
            ->call('save')
            ->assertHasFormErrors(['email']);
    });

});

