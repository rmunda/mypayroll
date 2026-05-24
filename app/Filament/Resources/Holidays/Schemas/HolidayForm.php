<?php

namespace App\Filament\Resources\Holidays\Schemas;

use App\Models\FinancialYear;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('financial_year_id')
                    ->label('Financial Year')
                    ->options(fn() => FinancialYear::all()->mapWithKeys(fn($fy) => [
                        $fy->id => $fy->label . ($fy->is_current ? ' (active)' : '')
                    ])->toArray())
                    ->default(fn() => FinancialYear::latest()->value('id'))
                    ->required(),
                TextInput::make('name')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                Select::make('type')
                    ->options([
            'national' => 'National',
            'regional' => 'Regional',
            'optional' => 'Optional',
            'company' => 'Company',
        ])
                    ->default('national')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_paid')
                    ->required(),
            ]);
    }
}
