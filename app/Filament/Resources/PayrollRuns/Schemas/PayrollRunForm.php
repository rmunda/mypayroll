<?php

namespace App\Filament\Resources\PayrollRuns\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Pay period')
                    ->columns(3)
                    ->schema([

                        TextInput::make('period_label')
                            ->required()
                            ->placeholder('e.g. April 2026'),

                        DatePicker::make('period_start')
                            ->required(),

                        DatePicker::make('period_end')
                            ->required(),
                    ]),

                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'processing' => 'Processing',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                    ])
                    ->default('draft')
                    ->disabled(),
            ]);
    }
}
