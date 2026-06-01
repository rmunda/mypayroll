<?php

namespace App\Filament\Pages\Reports;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Attendance Report';

    protected static string|\BackedEnum|null $navigationIcon = 'lucide-clock';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.reports.attendance-report';

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->with(['employee.department'])
                    ->latest('date')
            )
            ->columns([
                TextColumn::make('employee.employee_code')
                    ->label('ID')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present'  => 'success',
                        'absent'   => 'danger',
                        'half_day' => 'warning',
                        'on_leave' => 'info',
                        'holiday'  => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present'  => 'Present',
                        'absent'   => 'Absent',
                        'half_day' => 'Half Day',
                        'on_leave' => 'On Leave',
                        'holiday'  => 'Holiday',
                        default    => $state,
                    }),

                TextColumn::make('check_in')
                    ->label('Check In')
                    ->time('h:i A')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('check_out')
                    ->label('Check Out')
                    ->time('h:i A')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('hours_worked')
                    ->label('Hours')
                    ->suffix(' hrs')
                    ->placeholder('—')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->placeholder('—')
                    ->limit(30)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'present'  => 'Present',
                        'absent'   => 'Absent',
                        'half_day' => 'Half Day',
                        'on_leave' => 'On Leave',
                        'holiday'  => 'Holiday',
                    ]),

                SelectFilter::make('department')
                    ->label('Department')
                    ->options(Department::pluck('name', 'id'))
                    ->query(fn ($query, $state) =>
                        $state['value']
                            ? $query->whereHas('employee', fn ($q) =>
                                $q->where('department_id', $state['value'])
                            )
                            : $query
                    ),

                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->options(
                        Employee::where('status', 'active')->pluck('name', 'id')
                    )
                    ->searchable(),

                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('from')->label('From date'),
                        DatePicker::make('to')->label('To date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) =>
                                $q->whereDate('date', '>=', $data['from'])
                            )
                            ->when($data['to'], fn ($q) =>
                                $q->whereDate('date', '<=', $data['to'])
                            );
                    }),
            ])
            ->striped()
            ->defaultSort('date', 'desc');
    }
}
