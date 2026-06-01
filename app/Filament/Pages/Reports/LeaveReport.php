<?php

namespace App\Filament\Pages\Reports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
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

class LeaveReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Leave Report';

    protected static string|\BackedEnum|null $navigationIcon = 'lucide-calendar-x';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.reports.leave-report';

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Leave::query()
                    ->with(['employee.department', 'leaveType', 'approvedBy'])
                    ->latest('from_date')
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

                TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->badge()
                    ->color(fn ($record) => $record->leaveType?->color ?? 'gray'),

                TextColumn::make('from_date')
                    ->label('From')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('to_date')
                    ->label('To')
                    ->date('d M Y'),

                TextColumn::make('days')
                    ->label('Days')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'request'   => 'gray',
                        'pending'   => 'warning',
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        'cancelled' => 'gray',
                        default     => 'gray',
                    }),

                TextColumn::make('approvedBy.name')
                    ->label('Approved By')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'request'   => 'Request',
                        'pending'   => 'Pending',
                        'approved'  => 'Approved',
                        'rejected'  => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->options(
                        LeaveType::where('is_active', true)->pluck('name', 'id')
                    ),

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
                                $q->whereDate('from_date', '>=', $data['from'])
                            )
                            ->when($data['to'], fn ($q) =>
                                $q->whereDate('to_date', '<=', $data['to'])
                            );
                    }),
            ])
            ->striped()
            ->defaultSort('from_date', 'desc');
    }
}
