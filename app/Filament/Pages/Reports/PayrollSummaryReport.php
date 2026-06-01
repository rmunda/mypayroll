<?php

namespace App\Filament\Pages\Reports;

use App\Models\PayrollRun;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class PayrollSummaryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Payroll Summary';

    protected static ?string $navigationIcon = 'lucide-file-bar-chart';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.reports.payroll-summary-report';

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PayrollRun::query()
                    ->withCount('paySlips')
                    ->latest('period_start')
            )
            ->columns([
                TextColumn::make('period_label')
                    ->label('Pay Period')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('period_start')
                    ->label('From')
                    ->date('d M Y'),

                TextColumn::make('period_end')
                    ->label('To')
                    ->date('d M Y'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'      => 'gray',
                        'processing' => 'warning',
                        'approved'   => 'info',
                        'paid'       => 'success',
                        default      => 'gray',
                    }),

                TextColumn::make('pay_slips_count')
                    ->label('Employees')
                    ->alignCenter(),

                TextColumn::make('total_gross')
                    ->label('Gross')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->alignRight(),

                TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('danger')
                    ->alignRight(),

                TextColumn::make('total_net')
                    ->label('Net Pay')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('success')
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'      => 'Draft',
                        'processing' => 'Processing',
                        'approved'   => 'Approved',
                        'paid'       => 'Paid',
                    ]),
            ])
            ->actions([
                Action::make('view_payslips')
                    ->label('View Payslips')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn (PayrollRun $record) =>
                        route('filament.admin.resources.pay-slips.index', [
                            'tableFilters[payroll_run_id][value]' => $record->id,
                        ])
                    ),
            ])
            ->striped()
            ->defaultSort('period_start', 'desc');
    }
}
