<?php

namespace App\Filament\Resources\PaySlips;

use App\Filament\Resources\PaySlips\Pages\CreatePaySlip;
use App\Filament\Resources\PaySlips\Pages\EditPaySlip;
use App\Filament\Resources\PaySlips\Pages\ListPaySlips;
use App\Filament\Resources\PaySlips\Schemas\PaySlipForm;
use App\Filament\Resources\PaySlips\Tables\PaySlipsTable;
use App\Models\PaySlip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaySlipResource extends Resource
{
    protected static ?string $model = PaySlip::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort  = 2;

    public static function form(Schema $schema): Schema
    {
        return PaySlipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaySlipsTable::configure($table);
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
            'create' => CreatePaySlip::route('/create'),
            'edit' => EditPaySlip::route('/{record}/edit'),
        ];
    }
}
