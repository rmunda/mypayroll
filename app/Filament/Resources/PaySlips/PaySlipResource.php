<?php

namespace App\Filament\Resources\PaySlips;

use App\Filament\Resources\PaySlips\Pages\ListPaySlips;
use App\Filament\Resources\PaySlips\Pages\ViewPaySlip;
use App\Filament\Resources\Payslips\Schemas\PaySlipInfoList;
use App\Filament\Resources\Payslips\Tables\PaySlipsTable;
use App\Models\PaySlip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

class PaySlipResource extends Resource
{
    protected static ?string $model = PaySlip::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-receipt-text';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
         return PaySlipsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaySlipInfoList::configure($schema);
    }

    public static function getEloquentQuery(): Builder  // add this method for scoping the employee role
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('employee')) {
            return $query->whereHas('employee', fn ($q) =>
                $q->where('user_id', auth()->id())
            );
        }

        return $query;
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
            'index' => ListPaySlips::route('/'),
            'view' => ViewPaySlip::route('/{record}'),
        ];
    }
}