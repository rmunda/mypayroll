<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

use App\Models\Employee;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Employee')
                ->options(Employee::where('status','active')->pluck('name','id'))
                ->searchable()->required(),
            DatePicker::make('date')->required()->default(today()),
            Select::make('status')
                ->options([
                    'present'=>'Present','absent'=>'Absent',
                    'half_day'=>'Half Day','on_leave'=>'On Leave',
                    'holiday'=>'Holiday','weekend'=>'Weekend',
                ])->required(),
            TimePicker::make('check_in'),
            TimePicker::make('check_out'),
            TextInput::make('remarks'),
        ]);
    }
}
