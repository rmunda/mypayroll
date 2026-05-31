<?php

namespace App\Filament\Resources\LeavePolicies;

use App\Filament\Resources\LeavePolicies\Pages\CreateLeavePolicy;
use App\Filament\Resources\LeavePolicies\Pages\EditLeavePolicy;
use App\Filament\Resources\LeavePolicies\Pages\ListLeavePolicies;
use App\Filament\Resources\LeavePolicies\Schemas\LeavePolicyForm;
use App\Filament\Resources\LeavePolicies\Tables\LeavePoliciesTable;
use App\Models\LeavePolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeavePolicyResource extends Resource
{
    protected static ?string $model = LeavePolicy::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Leave Management';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-file-text';

    protected static ?string $navigationLabel = 'Leave Policies';

    protected static ?int    $navigationSort  = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LeavePolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeavePoliciesTable::configure($table);
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
            'index' => ListLeavePolicies::route('/'),
            'create' => CreateLeavePolicy::route('/create'),
            'edit' => EditLeavePolicy::route('/{record}/edit'),
        ];
    }
}
