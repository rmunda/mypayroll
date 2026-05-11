<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('date')
                    ->required(),
                Select::make('status')
                    ->options([
            'present' => 'Present',
            'absent' => 'Absent',
            'half_day' => 'Half day',
            'on_leave' => 'On leave',
            'holiday' => 'Holiday',
            'weekend' => 'Weekend',
        ])
                    ->required(),
                TimePicker::make('check_in'),
                TimePicker::make('check_out'),
                TextInput::make('hours_worked')
                    ->numeric(),
                TextInput::make('remarks'),
            ]);
    }
}
