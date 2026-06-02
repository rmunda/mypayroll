<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Widgets\AttendanceCalendarWidget;
use App\Filament\Widgets\AttendanceStatsWidget;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\WeeklyOffRule;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class AttendanceCalendar extends Page
{
    protected static string $resource = AttendanceResource::class;

    protected string $view = 'filament.pages.attendance-calendar';

    protected static ?string $title = 'Attendance Calendar';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_present')
                ->label('Mark All Present')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    DatePicker::make('date')
                        ->label('Date')
                        ->default(today())
                        ->required()
                        ->maxDate(today()),
                ])
                ->modalHeading('Mark All Employees Present')
                ->modalDescription('Creates attendance records for all active employees. Existing records for the selected date will not be overwritten.')
                ->modalSubmitActionLabel('Mark All Present')
                ->action(function (array $data) {
                    $date      = $data['date'];
                    $employees = Employee::where('status', 'active')->pluck('id');

                    // get employees who already have a record for this date
                    $existing = Attendance::whereDate('date', $date)
                        ->whereIn('employee_id', $employees)
                        ->pluck('employee_id')
                        ->toArray();

                    // only create for employees without a record
                    $toCreate = $employees->reject(fn ($id) => in_array($id, $existing));

                    $created = 0;
                    foreach ($toCreate as $employeeId) {
                        Attendance::create([
                            'employee_id' => $employeeId,
                            'date'        => $date,
                            'status'      => 'present',
                        ]);
                        $created++;
                    }

                    $skipped = count($existing);

                    Notification::make()
                        ->title('Daily attendance created')
                        ->body("{$created} marked present" . ($skipped > 0 ? ", {$skipped} already had records (skipped)." : '.'))
                        ->success()
                        ->send();
                }),

            Action::make('back')
                ->label('Back to List')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('list')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceStatsWidget::class,
            AttendanceCalendarWidget::class,
        ];
    }
}