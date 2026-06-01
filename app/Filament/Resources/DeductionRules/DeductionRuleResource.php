<?php

namespace App\Filament\Resources\DeductionRules;

use App\Filament\Resources\DeductionRules\Pages\CreateDeductionRule;
use App\Filament\Resources\DeductionRules\Pages\EditDeductionRule;
use App\Filament\Resources\DeductionRules\Pages\ListDeductionRules;
use App\Filament\Resources\DeductionRules\Schemas\DeductionRuleForm;
use App\Filament\Resources\DeductionRules\Tables\DeductionRulesTable;
use App\Models\DeductionRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeductionRuleResource extends Resource
{
    protected static ?string $model = DeductionRule::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-calculator';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DeductionRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeductionRulesTable::configure($table);
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