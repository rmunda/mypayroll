<?php

namespace App\Filament\Resources\DeductionRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeductionRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->required(),

                Select::make('type')
                    ->options([
                        'percentage' => 'Percentage (%)',
                        'fixed' => 'Fixed amount (INR)',
                    ])
                    ->required()
                    ->live(),

                TextInput::make('value')
                    ->numeric()
                    ->required()
                    ->suffix(fn (callable $get) =>
                        $get('type') === 'percentage'
                            ? '%'
                            : 'INR'
                    ),

                Select::make('applies_to')
                    ->options([
                        'basic' => 'Basic salary',
                        'gross' => 'Gross salary',
                    ])
                    ->required(),

                Select::make('deduction_side')
                    ->options([
                        'employee' => 'Employee',
                        'employer' => 'Employer',
                        'both' => 'Both',
                    ]),

                Toggle::make('is_statutory')
                    ->label('Statutory deduction'),

                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
