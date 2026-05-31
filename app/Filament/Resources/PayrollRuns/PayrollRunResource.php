<?php

namespace App\Filament\Resources\PayrollRuns;

use App\Filament\Resources\PayrollRuns\Pages\CreatePayrollRun;
use App\Filament\Resources\PayrollRuns\Pages\EditPayrollRun;
use App\Filament\Resources\PayrollRuns\Pages\ListPayrollRuns;
use App\Filament\Resources\PayrollRuns\Pages\ViewPayrollRun;
use App\Filament\Resources\PayrollRuns\Schemas\PayrollRunForm;
use App\Filament\Resources\PayrollRuns\Tables\PayrollRunsTable;
use App\Models\PayrollRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;



class PayrollRunResource extends Resource
{
    protected static ?string $model = PayrollRun::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-receipt-indian-rupee';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'period_label';

    public static function form(Schema $schema): Schema
    {
        return PayrollRunForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return payrollRunsTable::configure($table);
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
            'index' => ListPayrollRuns::route('/'),
            'create' => CreatePayrollRun::route('/create'),
            'edit' => EditPayrollRun::route('/{record}/edit'),
            'view' => ViewPayrollRun::route('/{record}'),
        ];
    }
}