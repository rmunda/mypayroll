<?php

namespace App\Filament\Resources\DeductionRules;

use App\Filament\Resources\DeductionRules\Pages\CreateDeductionRule;
use App\Filament\Resources\DeductionRules\Pages\EditDeductionRule;
use App\Filament\Resources\DeductionRules\Pages\ListDeductionRules;

use App\Models\DeductionRule;

use BackedEnum;

use Filament\Actions\EditAction;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Resources\Resource;

use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;

use Filament\Tables\Table;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class DeductionRuleResource extends Resource
{
    protected static ?string $model = DeductionRule::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Compliance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('value')
                    ->formatStateUsing(
                        fn ($state, DeductionRule $record) =>
                            $record->type === 'percentage'
                                ? $state . '%'
                                : 'INR ' . $state
                    ),

                TextColumn::make('applies_to'),

                TextColumn::make('deduction_side'),

                IconColumn::make('is_statutory')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->boolean(),
            ])

            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeductionRules::route('/'),
            'create' => CreateDeductionRule::route('/create'),
            'edit' => EditDeductionRule::route('/{record}/edit'),
        ];
    }
}