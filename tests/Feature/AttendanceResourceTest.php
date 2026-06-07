<?php

use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Models\WeeklyOffRule;
use Filament\Facades\Filament;

use Livewire\Livewire;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function attendanceAdminUser(): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('admin');
    return $user;
}

beforeEach(fn () => Filament::setCurrentPanel(Filament::getPanel('admin')));

// ---------------------------------------------------------------------------
// Duplicate guard: unique (employee_id, date)
// ---------------------------------------------------------------------------

describe('Attendance duplicate rule', function () {

    it('rejects a second attendance for the same employee and date', function () {
        $this->actingAs(attendanceAdminUser());

        $employee = Employee::factory()->create(['date_of_joining' => '2020-01-01']);

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'date'        => '2025-06-03',
            'status'      => 'present',
        ]);

        Livewire::test(CreateAttendance::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'date'        => '2025-06-03',
                'status'      => 'present',
            ])
            ->call('create')
            ->assertHasFormErrors(['date']);

        expect(Attendance::where('employee_id', $employee->id)->count())->toBe(1);
    });

    it('allows the same date for a different employee', function () {
        $this->actingAs(attendanceAdminUser());

        $taken = Employee::factory()->create(['date_of_joining' => '2020-01-01']);
        $other = Employee::factory()->create(['date_of_joining' => '2020-01-01']);

        Attendance::factory()->create([
            'employee_id' => $taken->id,
            'date'        => '2025-06-03',
            'status'      => 'present',
        ]);

        Livewire::test(CreateAttendance::class)
            ->fillForm([
                'employee_id' => $other->id,
                'date'        => '2025-06-03',
                'status'      => 'present',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Attendance::where('employee_id', $other->id)->where('date', '2025-06-03')->exists())->toBeTrue();
    });

});

// ---------------------------------------------------------------------------
// Joining-date guard: date cannot be before date_of_joining
// ---------------------------------------------------------------------------

describe('Attendance joining-date rule', function () {

    it('rejects an attendance dated before the joining date', function () {
        $this->actingAs(attendanceAdminUser());

        $employee = Employee::factory()->create(['date_of_joining' => '2025-06-01']);

        Livewire::test(CreateAttendance::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'date'        => '2025-05-15', // before joining
                'status'      => 'present',
            ])
            ->call('create')
            ->assertHasFormErrors(['date']);

        expect(Attendance::where('employee_id', $employee->id)->count())->toBe(0);
    });

    it('allows an attendance dated on or after the joining date', function () {
        $this->actingAs(attendanceAdminUser());

        $employee = Employee::factory()->create(['date_of_joining' => '2025-06-01']);

        Livewire::test(CreateAttendance::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'date'        => '2025-06-03', // Tuesday, after joining
                'status'      => 'present',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Attendance::where('employee_id', $employee->id)->where('date', '2025-06-03')->exists())->toBeTrue();
    });

});

// ---------------------------------------------------------------------------
// Weekend status guard: 'weekend' only valid on a non-working day
// ---------------------------------------------------------------------------

describe('Attendance weekend status rule', function () {

    it('rejects weekend status on a working weekday', function () {
        $this->actingAs(attendanceAdminUser());
        WeeklyOffRule::factory()->default()->create(); // Sat/Sun off

        $employee = Employee::factory()->create(['date_of_joining' => '2020-01-01']);

        // 2025-06-03 is a Tuesday -> 'weekend' is not an offered option
        Livewire::test(CreateAttendance::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'date'        => '2025-06-03',
                'status'      => 'weekend',
            ])
            ->call('create')
            ->assertHasFormErrors(['status']);

        expect(Attendance::where('employee_id', $employee->id)->count())->toBe(0);
    });

    it('allows weekend status on a weekend day', function () {
        $this->actingAs(attendanceAdminUser());
        WeeklyOffRule::factory()->default()->create(); // Sat/Sun off

        $employee = Employee::factory()->create(['date_of_joining' => '2020-01-01']);

        // 2025-06-01 is a Sunday -> 'weekend' is offered
        Livewire::test(CreateAttendance::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'date'        => '2025-06-01',
                'status'      => 'weekend',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Attendance::where('employee_id', $employee->id)->where('status', 'weekend')->exists())->toBeTrue();
    });

});
