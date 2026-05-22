<?php

namespace App\Filament\Resources\FinancialYears;

use App\Filament\Resources\FinancialYears\Pages\CreateFinancialYear;
use App\Filament\Resources\FinancialYears\Pages\EditFinancialYear;
use App\Filament\Resources\FinancialYears\Pages\ListFinancialYears;
use App\Filament\Resources\FinancialYears\Schemas\FinancialYearForm;
use App\Filament\Resources\FinancialYears\Tables\FinancialYearsTable;
use App\Models\FinancialYear;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinancialYearResource extends Resource
{
    protected static ?string $model = FinancialYear::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    protected static ?int $navigationSort  = 1;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return FinancialYearForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialYearsTable::configure($table);
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
            'index' => ListFinancialYears::route('/'),
            'create' => CreateFinancialYear::route('/create'),
            'edit' => EditFinancialYear::route('/{record}/edit'),
        ];
    }
}
