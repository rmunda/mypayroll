<?php

namespace App\Filament\Resources\Leaves\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Models\Employee;
use Filament\Schemas\Schema;

class LeaveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('employee_id')
                    ->label('Employee')
                    ->options(
                        Employee::pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),

                Select::make('type')
                    ->options([
                        'casual' => 'Casual',
                        'sick' => 'Sick',
                        'earned' => 'Earned',
                        'maternity' => 'Maternity',
                        'unpaid' => 'Unpaid',
                    ])
                    ->required(),

                DatePicker::make('from_date')
                    ->required()
                    ->live(),

                DatePicker::make('to_date')
                    ->required()
                    ->afterOrEqual('from_date')
                    ->live(),

                TextInput::make('days')
                    ->numeric()
                    ->required(),

                Textarea::make('reason'),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ]);
    }
}
