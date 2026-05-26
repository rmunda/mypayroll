<?php

namespace App\Filament\Resources\PaySlips\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaySlipInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Employee')
                    ->columns(4)
                    ->schema([

                        TextEntry::make('employee.name'),

                        TextEntry::make('employee.employee_code')
                            ->label('Employee ID'),

                        TextEntry::make('employee.designation'),

                        TextEntry::make('employee.department.name')
                            ->label('Department'),
                    ]),

                Section::make('Earnings')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('basic')
                            ->money('INR'),

                        TextEntry::make('hra')
                            ->label('HRA')
                            ->money('INR'),

                        TextEntry::make('transport_allowance')
                            ->money('INR'),

                        TextEntry::make('special_allowance')
                            ->money('INR'),

                        TextEntry::make('bonus')
                            ->money('INR'),

                        TextEntry::make('gross_earnings')
                            ->money('INR')
                            ->weight('bold'),
                    ]),

                Section::make('Deductions')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('pf_employee')
                            ->label('PF Employee')
                            ->money('INR'),

                        TextEntry::make('esi_employee')
                            ->label('ESI Employee')
                            ->money('INR'),

                        TextEntry::make('professional_tax')
                            ->money('INR'),

                        TextEntry::make('tds')
                            ->label('TDS')
                            ->money('INR'),

                        TextEntry::make('total_deductions')
                            ->money('INR')
                            ->weight('bold'),
                    ]),

                Section::make('Net Pay')
                    ->schema([

                        TextEntry::make('net_pay')
                            ->money('INR')
                            ->weight('bold')
                            ->size('xl'),
                    ]),
            ]);
    }
}