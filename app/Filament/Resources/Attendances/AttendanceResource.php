<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\Attendances\Pages\AttendanceCalendar;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Filament\Resources\Attendances\Tables\AttendancesTable;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|\UnitEnum|null $navigationGroup = 'People';

    protected static string|BackedEnum|null $navigationIcon = 'lucide-calendar-days';

    protected static ?int $navigationSort  = 2;

    public static function form(Schema $schema): Schema
    {
        return AttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendancesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder  // add this method for scoping the employee role to see list of personal attendance
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('employee')) {
            return $query->whereHas('employee', fn ($q) =>
                $q->where('user_id', auth()->id())
            );
        }

        return $query;
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
