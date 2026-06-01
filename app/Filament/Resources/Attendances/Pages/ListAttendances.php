<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Widgets\AttendanceStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

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

            // Add your new Calendar View button right next to it
            Action::make('back')
                ->label('Calendar View')
                ->icon('heroicon-o-calendar-days')                
                ->url(fn (): string => static::getResource()::getUrl('index')), // Points to root '/' which is now the calendar
        ];
    }
}
