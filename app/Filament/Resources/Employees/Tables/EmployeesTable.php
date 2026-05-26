<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Tables\Table;

use App\Models\Employee;

use App\Filament\Resources\PaySlips\PaySlipResource;

use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;


class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('employee_code')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->fontFamily('mono'),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('department.name')
                    ->label('Dept')
                    ->sortable(),

                TextColumn::make('designation')
                    ->searchable(),

                TextColumn::make('basic_salary')
                    ->label('Basic')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('date_of_joining')
                    ->date('M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'on_leave' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
            ])

            ->filters([

                SelectFilter::make('department')
                    ->relationship('department', 'name'),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on_leave' => 'On Leave',
                    ]),
            ])

            ->actions([

                EditAction::make(),

                Action::make('payslips')
                    ->icon('heroicon-o-document-text')
                    ->label('Pay slips')
                    ->url(fn (Employee $record) =>
                        PaySlipResource::getUrl(
                            'index',
                            [
                                'tableFilters[employee][value]' => $record->id,
                            ]
                        )
                    ),
            ])

            ->bulkActions([

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
