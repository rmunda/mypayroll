<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;

use Filament\Schemas\Schema;

use Illuminate\Database\Eloquent\Model;


use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;

use Filament\Schemas\Components\Grid;

use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AttendanceCalendarWidget extends FullCalendarWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public Model | string | null $model = Attendance::class;

    public ?string $filterEmployee = null;

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager', 'employee']);
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $user = auth()->user();
        $employee = $user->employee;

        $query =  Attendance::query()
            ->with('employee')
            ->whereBetween('date', [
                $fetchInfo['start'],
                $fetchInfo['end'],
            ])
            ->when(
                $this->filterEmployee,
                fn ($query) => $query->where(
                    'employee_id',
                    $this->filterEmployee
                )
            );
        
        // employee can only see their own attendance
        if ($user->hasRole('employee') && $employee) {
            $query->where('employee_id', $employee->id);
        }

        // manager sees only their department
        if ($user->hasRole('manager') && $employee) {
            $query->whereHas('employee', function ($q) use ($employee) {
                $q->where('department_id', $employee->department_id);
            });
        }    
            
        return $query->get()
            ->map(
                fn (Attendance $attendance) => EventData::make()
                    ->id($attendance->id)
                    ->title(
                        $attendance->employee->name .
                        ' - ' .
                        ucfirst(str_replace('_', ' ', $attendance->status))
                    )
                    ->start($attendance->date)
                    ->end($attendance->date)
                    ->backgroundColor($this->getColor($attendance->status))
                    ->toArray()
            )
            ->toArray();    
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make(2)
                ->schema([

                    Select::make('employee_id')
                        ->label('Employee')
                        ->relationship(name: 'employee', titleAttribute: 'name', modifyQueryUsing: fn ($query) => $query->where('status', 'active'))
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->required(),

                    DatePicker::make('date')
                        ->required(),

                    Select::make('status')
                        ->options([
                            'present' => 'Present',
                            'absent' => 'Absent',
                            'half_day' => 'Half Day',
                            'on_leave' => 'On Leave',
                            'holiday' => 'Holiday',
                            'weekend' => 'Weekend',
                        ])
                        ->required(),

                    TimePicker::make('check_in'),

                    TimePicker::make('check_out'),

                    Textarea::make('remarks')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->mountUsing(
                    function (Schema $schema, array $arguments) {
                        // fill() properly hydrates every field (incl. the searchable
                        // employee select). Using state() here skips hydration and
                        // makes the select submit empty -> "required" error.
                        $schema->fill([
                            'date' => $arguments['start'] ?? today(),
                        ]);
                    }
                ),
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    private function getColor(string $status): string
    {
        return match($status) {
            'present'  => '#1D9E75',
            'absent'   => '#E24B4A',
            'half_day' => '#EF9F27',
            'on_leave' => '#378ADD',
            'holiday'  => '#7F77DD',
            'weekend'  => '#9E9E98',
            default    => '#cccccc',
        };
    }
}