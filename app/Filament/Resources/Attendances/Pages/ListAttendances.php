<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Exports\AttendanceExport;
use App\Filament\Resources\Attendances\Actions\ImportAttendanceAction;
use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Widgets\AttendanceStatsWidget;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Maatwebsite\Excel\Facades\Excel;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAttendanceAction::make(),

            Action::make('export_attendance')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->form([
                    Select::make('period')
                        ->label('Period')
                        ->options([
                            'daily'   => 'Daily (Today)',
                            'weekly'  => 'Weekly (This Week)',
                            'monthly' => 'Monthly (This Month)',
                            'custom'  => 'Custom Range',
                        ])
                        ->default('monthly')
                        ->required()
                        ->live(),

                    DatePicker::make('from')
                        ->label('From Date')
                        ->required()
                        ->visible(fn (Get $get) => $get('period') === 'custom'),

                    DatePicker::make('to')
                        ->label('To Date')
                        ->required()
                        ->afterOrEqual('from')
                        ->visible(fn (Get $get) => $get('period') === 'custom'),
                ])
                ->action(function (array $data) {
                    [$from, $to] = match ($data['period']) {
                        'daily'   => [today(), today()],
                        'weekly'  => [now()->startOfWeek(), now()->endOfWeek()],
                        'monthly' => [now()->startOfMonth(), now()->endOfMonth()],
                        'custom'  => [Carbon::parse($data['from']), Carbon::parse($data['to'])],
                    };

                    $filename = 'attendance-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.xlsx';

                    return Excel::download(new AttendanceExport($from, $to), $filename);
                }),

            Action::make('back')
                ->label('Calendar View')
                ->icon('heroicon-o-calendar-days')
                ->url(fn (): string => static::getResource()::getUrl('index')),
        ];
    }
}
