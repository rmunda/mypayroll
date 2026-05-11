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
                    ->options(['percentage' => 'Percentage', 'fixed' => 'Fixed'])
                    ->required(),
                TextInput::make('value')
                    ->required()
                    ->numeric(),
                Select::make('applies_to')
                    ->options(['basic' => 'Basic', 'gross' => 'Gross'])
                    ->required(),
                Select::make('deduction_side')
                    ->options(['employee' => 'Employee', 'employer' => 'Employer', 'both' => 'Both'])
                    ->default('employee')
                    ->required(),
                Toggle::make('is_statutory')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
