<?php

namespace App\Filament\Resources\PayrollRuns\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayrollRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('period_label')
                    ->required(),
                DatePicker::make('period_start')
                    ->required(),
                DatePicker::make('period_end')
                    ->required(),
                Select::make('status')
                    ->options(['draft' => 'Draft', 'processing' => 'Processing', 'approved' => 'Approved', 'paid' => 'Paid'])
                    ->default('draft')
                    ->required(),
                TextInput::make('total_gross')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_deductions')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_net')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('processed_by')
                    ->numeric(),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
