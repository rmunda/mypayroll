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
                    $date    = $data['date'];
                    $carbon  = Carbon::parse($date);

                    // one query — check if public holiday for all
                    $isHoliday   = Holiday::isHoliday($carbon);
                    $defaultRule = WeeklyOffRule::where('is_default', true)->first();

                    // load active employees with their weekly off rule
                    $employees = Employee::where('status', 'active')
                                    ->with('weeklyOffRule')
                                    ->get();

                    // load approved leaves covering this date (one query)
                    $onLeaveIds = Leave::where('status', 'approved')
                                    ->whereDate('from_date', '<=', $date)
                                    ->whereDate('to_date', '>=', $date)
                                    ->pluck('employee_id')
                                    ->toArray();

                    // employees already having a record for this date
                    $existing = Attendance::whereDate('date', $date)
                                    ->whereIn('employee_id', $employees->pluck('id'))
                                    ->pluck('employee_id')
                                    ->toArray();

                    $skipped = 0;
                    $counts  = ['present' => 0, 'holiday' => 0, 'weekend' => 0, 'on_leave' => 0];

                    foreach ($employees as $employee) {

                        // skip if record already exists
                        if (in_array($employee->id, $existing)) {
                            $skipped++;
                            continue;
                        }

                        // determine status in priority order
                        $rule = $employee->weeklyOffRule ?? $defaultRule;

                        $status = match (true) {
                            $isHoliday                              => 'holiday',
                            $rule && !$rule->isWorkingDay($carbon)  => 'weekend',
                            in_array($employee->id, $onLeaveIds)    => 'on_leave',
                            default                                 => 'present',
                        };

                        Attendance::create([
                            'employee_id' => $employee->id,
                            'date'        => $date,
                            'status'      => $status,
                        ]);

                        $counts[$status]++;
                    }

                    // build summary message
                    $parts = [];
                    if ($counts['present'])  $parts[] = "{$counts['present']} present";
                    if ($counts['on_leave']) $parts[] = "{$counts['on_leave']} on leave";
                    if ($counts['holiday'])  $parts[] = "{$counts['holiday']} holiday";
                    if ($counts['weekend'])  $parts[] = "{$counts['weekend']} weekend";
                    if ($skipped)            $parts[] = "{$skipped} skipped (already existed)";

                    Notification::make()
                        ->title('Daily attendance created')
                        ->body(implode(', ', $parts) . '.')
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