<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Carbon;
use Closure;

use App\Models\Employee;
use App\Models\WeeklyOffRule;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Employee')
                ->options(Employee::where('status','active')->pluck('name','id'))
                ->searchable()
                ->live()
                ->required(),
            DatePicker::make('date')
                ->required()
                ->default(today())
                ->live()
                ->unique(
                    table: 'attendance',
                    modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('employee_id', $get('employee_id')),
                    ignoreRecord: true,
                )
                ->validationMessages([
                    'unique' => 'An attendance record already exists for this employee on this date.',
                ])
                ->rule(fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    $employee = Employee::find($get('employee_id'));

                    if ($employee && $value && Carbon::parse($value)->lt($employee->date_of_joining)) {
                        $fail('Attendance cannot be dated before the joining date ('
                            . Carbon::parse($employee->date_of_joining)->format('d M Y') . ').');
                    }
                }),
            Select::make('status')
                ->options(function (Get $get): array {
                    $options = [
                        'present'  => 'Present',
                        'absent'   => 'Absent',
                        'half_day' => 'Half Day',
                        'on_leave' => 'On Leave',
                        'holiday'  => 'Holiday',
                    ];

                    // Only offer "Weekend" when the chosen date is a non-working
                    // day under the employee's rule (falling back to the default).
                    $date = $get('date');

                    if ($date) {
                        $employee = Employee::with('weeklyOffRule')->find($get('employee_id'));
                        $rule     = $employee?->weeklyOffRule ?? WeeklyOffRule::default();

                        if ($rule && ! $rule->isWorkingDay(Carbon::parse($date))) {
                            $options['weekend'] = 'Weekend';
                        }
                    }

                    return $options;
                })
                ->required(),
            TimePicker::make('check_in'),
            TimePicker::make('check_out'),
            TextInput::make('remarks'),
        ]);
    }
}
