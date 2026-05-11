<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('department_id')
                    ->required()
                    ->numeric(),
                TextInput::make('pay_structure_id')
                    ->required()
                    ->numeric(),
                TextInput::make('designation')
                    ->required(),
                TextInput::make('basic_salary')
                    ->required()
                    ->numeric(),
                Select::make('pay_frequency')
                    ->options(['monthly' => 'Monthly', 'biweekly' => 'Biweekly', 'weekly' => 'Weekly'])
                    ->default('monthly')
                    ->required(),
                TextInput::make('bank_name'),
                TextInput::make('bank_account'),
                TextInput::make('ifsc_code'),
                TextInput::make('pan_number'),
                TextInput::make('uan_number'),
                TextInput::make('esic_number'),
                DatePicker::make('date_of_joining')
                    ->required(),
                DatePicker::make('date_of_leaving'),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On leave'])
                    ->default('active')
                    ->required(),
                Select::make('tax_regime')
                    ->options(['new' => 'New', 'old' => 'Old'])
                    ->default('new')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
