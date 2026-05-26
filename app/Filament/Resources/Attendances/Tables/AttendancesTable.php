<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date','desc')
            ->columns([
                TextColumn::make('employee.name')->searchable()->sortable(),
                TextColumn::make('employee.department.name')->label('Dept'),
                TextColumn::make('date')->date('d M Y')->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'present' => 'success',
                    'absent' => 'danger',
                    'half_day' => 'warning',
                    'on_leave' => 'info',
                    'holiday', 'weekend' => 'gray',
                    default => 'gray',
                }),
                TextColumn::make('check_in')->time('h:i A'),
                TextColumn::make('check_out')->time('h:i A'),
                TextColumn::make('remarks'),
            ])

            ->filters([
                SelectFilter::make('employee')->relationship('employee','name'),
                SelectFilter::make('status')->options([
                    'present'=>'Present','absent'=>'Absent',
                    'half_day'=>'Half Day','on_leave'=>'On Leave',
                ]),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when(
                            $data['from'] ?? null,
                            fn ($query, $date) => $query->whereDate('date', '>=', $date)
                        )
                        ->when(
                            $data['until'] ?? null,
                            fn ($query, $date) => $query->whereDate('date', '<=', $date)
                        )
                    ),
            ])

            // ->headerActions([
            //     Action::make('calendar')
            //         ->label('Calendar View')
            //         ->icon('heroicon-o-calendar-days')
            //         ->url(fn (): string => static::getUrl('index')),
            // ])
            
            ->bulkActions([
                BulkAction::make('mark_present')
                    ->action(fn ($records) =>
                        $records->each->update([
                            'status' => 'present',
                        ])
                    ),

                BulkAction::make('mark_absent')
                    ->action(fn ($records) =>
                        $records->each->update([
                            'status' => 'absent',
                        ])
                    ),
            ]);
    }
}
