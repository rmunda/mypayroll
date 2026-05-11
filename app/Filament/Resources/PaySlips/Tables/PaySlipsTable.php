<?php

namespace App\Filament\Resources\PaySlips\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaySlipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payroll_run_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('employee_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('working_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('present_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('leave_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('absent_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('basic')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('hra')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('transport_allowance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('special_allowance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('bonus')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('arrears')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gross_earnings')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pf_employee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pf_employer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('esi_employee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('esi_employer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('professional_tax')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tds')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('other_deductions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_deductions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('net_pay')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('pdf_path')
                    ->searchable(),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
