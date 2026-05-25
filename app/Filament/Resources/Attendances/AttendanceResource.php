<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\Attendances\Pages\AttendanceCalendar;

use App\Models\Employee;
use App\Models\Attendance;

use BackedEnum;

use Filament\Actions\BulkAction;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;

use Filament\Resources\Resource;

use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;

use Filament\Tables\Table;
use Filament\Actions\Action;

use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\UnitEnum|null $navigationGroup = 'People';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort  = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Employee')
                ->options(Employee::where('status','active')->pluck('name','id'))
                ->searchable()->required(),
            DatePicker::make('date')->required()->default(today()),
            Select::make('status')
                ->options([
                    'present'=>'Present','absent'=>'Absent',
                    'half_day'=>'Half Day','on_leave'=>'On Leave',
                    'holiday'=>'Holiday','weekend'=>'Weekend',
                ])->required(),
            TimePicker::make('check_in'),
            TimePicker::make('check_out'),
            TextInput::make('remarks'),
        ]);
    }

    public static function table(Table $table): Table
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'    => AttendanceCalendar::route('/'),
            'create'   => CreateAttendance::route('/create'),
            'list'     => ListAttendances::route('/list'),
        ];
    }
}
