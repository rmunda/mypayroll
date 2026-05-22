<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;

use Filament\Schemas\Schema;

use Filament\Actions\CreateAction;

use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Data\EventData;

use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class AttendanceCalendarWidget extends FullCalendarWidget
{
    // Force the calendar widget to utilize the full page width
    protected int | string | array $columnSpan = 'full';

    // Optional: Make it show up at the very top if you have other dashboard widgets
    protected static ?int $sort = 1;

    public ?string $filterEmployee = null;

    public Model | string | null $model = Attendance::class;

    // REMOVE your old public function form() method completely!
    // ADD THIS DYNAMIC ACTION BLOCK INSTEAD
    protected function getCreateAction(): CreateAction
    {
        return CreateAction::make()
            ->model(Attendance::class)
            ->mountUsing(function (Schema $schema, array $arguments) {
                // This pre-fills the DatePicker with the date you actually clicked on!
                return $schema->state([
                    'date' => $arguments['date'] ?? today(),
                ]);
            })
            ->schema([
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(Employee::where('status', 'active')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                    
                DatePicker::make('date')
                    ->required(),
                    
                Select::make('status')
                    ->options([
                        'present'  => 'Present',
                        'absent'   => 'Absent',
                        'half_day' => 'Half Day',
                        'on_leave' => 'On Leave',
                        'holiday'  => 'Holiday',
                        'weekend'  => 'Weekend',
                    ])
                    ->required(),
                    
                TimePicker::make('check_in'),
                TimePicker::make('check_out'),
                TextInput::make('remarks')
                ->columnSpanFull(),
            ]);
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $colors = [
            'present' => '#1D9E75',
            'absent' => '#E24B4A',
            'half_day' => '#EF9F27',
            'on_leave' => '#378ADD',
            'holiday' => '#7F77DD',
            'weekend' => '#9E9E98',
        ];

        return Attendance::query()
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
            )
            ->get()
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
                    ->backgroundColor(
                        $colors[$attendance->status] ?? '#cccccc'
                    )
                    ->toArray()
            )
            ->toArray();
    }

    protected function handleEventCreate(array $data): Model
    {
        return Attendance::create($data);
    }

    protected function handleEventUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        return $record;
    }
}