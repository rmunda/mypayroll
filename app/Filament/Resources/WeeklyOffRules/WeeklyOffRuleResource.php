<?php

namespace App\Filament\Resources\WeeklyOffRules;

use App\Filament\Resources\WeeklyOffRules\Pages\CreateWeeklyOffRule;
use App\Filament\Resources\WeeklyOffRules\Pages\EditWeeklyOffRule;
use App\Filament\Resources\WeeklyOffRules\Pages\ListWeeklyOffRules;
use App\Filament\Resources\WeeklyOffRules\Schemas\WeeklyOffRuleForm;
use App\Filament\Resources\WeeklyOffRules\Tables\WeeklyOffRulesTable;
use App\Models\WeeklyOffRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WeeklyOffRuleResource extends Resource
{
    protected static ?string $model = WeeklyOffRule::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-notebook';

    protected static ?string $navigationLabel = 'Weekly Off Rules';

    protected static ?int    $navigationSort  = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WeeklyOffRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeeklyOffRulesTable::configure($table);
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
            'index' => ListWeeklyOffRules::route('/'),
            'create' => CreateWeeklyOffRule::route('/create'),
            'edit' => EditWeeklyOffRule::route('/{record}/edit'),
        ];
    }
}
