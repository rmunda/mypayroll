<?php

namespace App\Filament\Resources\LeaveTransactions;

use App\Filament\Resources\LeaveTransactions\Pages\CreateLeaveTransaction;
use App\Filament\Resources\LeaveTransactions\Pages\EditLeaveTransaction;
use App\Filament\Resources\LeaveTransactions\Pages\ListLeaveTransactions;
use App\Filament\Resources\LeaveTransactions\Pages\ViewLeaveTransaction;
use App\Filament\Resources\LeaveTransactions\Schemas\LeaveTransactionForm;
use App\Filament\Resources\LeaveTransactions\Schemas\LeaveTransactionInfoList;
use App\Filament\Resources\LeaveTransactions\Tables\LeaveTransactionsTable;
use App\Models\LeaveTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveTransactionResource extends Resource
{
    protected static ?string $model = LeaveTransaction::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Leave Management';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-refresh-ccw';

     protected static ?string $navigationLabel = 'Leave Transactions';

    protected static ?int $navigationSort  = 7;

    public static function form(Schema $schema): Schema
    {
        return LeaveTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaveTransactionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeaveTransactionInfolist::configure($schema);
    }

    // scope by role
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // employee sees only their own transactions
        if (auth()->user()->hasRole('employee')) {
            return $query->whereHas('employee', fn($q) =>
                $q->where('user_id', auth()->id())
            );
        }

        // manager sees their department
        if (auth()->user()->hasRole('manager')) {
            return $query->whereHas('employee', fn($q) =>
                $q->whereHas('department', fn($d) =>
                    $d->whereHas('employees', fn($e) =>
                        $e->where('user_id', auth()->id())
                    )
                )
            );
        }

        return $query;
    }

    // read only — no create, edit or delete
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr']);
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
            'index' => ListLeaveTransactions::route('/'),
            //'create' => CreateLeaveTransaction::route('/create'),
            //'edit' => EditLeaveTransaction::route('/{record}/edit'),
            'view'  => ViewLeaveTransaction::route('/{record}'),
        ];
    }
}
