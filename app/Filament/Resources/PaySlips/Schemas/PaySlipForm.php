<?php

namespace App\Filament\Resources\PaySlips\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaySlipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('payroll_run_id')
                    ->required()
                    ->numeric(),
                TextInput::make('employee_id')
                    ->required()
                    ->numeric(),
                TextInput::make('working_days')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('present_days')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('leave_days')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('absent_days')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('basic')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('hra')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('transport_allowance')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('special_allowance')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('bonus')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('arrears')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('gross_earnings')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('pf_employee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('pf_employer')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('esi_employee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('esi_employer')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('professional_tax')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tds')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('other_deductions')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_deductions')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('net_pay')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid', 'sent' => 'Sent'])
                    ->default('draft')
                    ->required(),
                TextInput::make('pdf_path'),
                DateTimePicker::make('sent_at'),
                TextInput::make('deduction_snapshot'),
            ]);
    }
}
