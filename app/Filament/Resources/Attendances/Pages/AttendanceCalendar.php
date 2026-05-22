<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Widgets\AttendanceCalendarWidget;
use Filament\Resources\Pages\Page;

use Filament\Actions\Action;

class AttendanceCalendar extends Page
{
    protected static string $resource = AttendanceResource::class;

    protected string $view = 'filament.pages.attendance-calendar';

    protected static ?string $title = 'Attendance Calendar';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to List')
                ->icon('heroicon-o-list-bullet') // Clean direct string icon
                ->color('gray')                  // Gray keeps it distinct from the create button
                ->url(fn (): string => static::getResource()::getUrl('index')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceCalendarWidget::class,
        ];
    }
}