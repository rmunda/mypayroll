<?php

namespace App\Filament\Resources\LeaveBalances\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveType;
use Filament\Schemas\Schema;

class LeaveBalanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Balance details')
                ->columns(2)
                ->schema([
                    Select::make('employee_id')
                        ->label('Employee')
                        ->options(
                            Employee::where('status', 'active')
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->required(),

                    Select::make('financial_year_id')
                        ->label('Financial year')
                        ->options(
                            FinancialYear::orderByDesc('start_date')
                                ->pluck('label', 'id')
                        )
                        ->required(),

                    Select::make('leave_type_id')
                        ->label('Leave type')
                        ->options(
                            LeaveType::where('is_active', true)
                                ->pluck('name', 'id')
                        )
                        ->required(),
                ]),

            Section::make('Balance breakdown')
                ->description('All values in days')
                ->columns(3)
                ->schema([
                    TextInput::make('allocated')
                        ->label('Allocated')
                        ->numeric()
                        ->default(0)
                        ->suffix('days')
                        ->helperText('Given at start of year'),

                    TextInput::make('accrued')
                        ->label('Accrued')
                        ->numeric()
                        ->default(0)
                        ->suffix('days')
                        ->helperText('Earned monthly'),

                    TextInput::make('carried_forward')
                        ->label('Carried forward')
                        ->numeric()
                        ->default(0)
                        ->suffix('days')
                        ->helperText('Brought from last year'),

                    TextInput::make('used')
                        ->label('Used')
                        ->numeric()
                        ->default(0)
                        ->suffix('days')
                        ->helperText('Approved and taken'),

                    TextInput::make('pending')
                        ->label('Pending')
                        ->numeric()
                        ->default(0)
                        ->suffix('days')
                        ->helperText('Requested not yet approved'),

                    TextInput::make('encashed')
                        ->label('Encashed')
                        ->numeric()
                        ->default(0)
                        ->suffix('days')
                        ->helperText('Leave encashed'),

                    TextInput::make('lapsed')
                        ->label('Lapsed')
                        ->numeric()
                        ->default(0)
                        ->suffix('days')
                        ->helperText('Expired unused leaves'),
                ]),

        ]);
    }
}
