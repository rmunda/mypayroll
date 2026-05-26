<?php

namespace App\Filament\Resources\Leaves;

use App\Filament\Resources\Leaves\Pages\CreateLeave;
use App\Filament\Resources\Leaves\Pages\EditLeave;
use App\Filament\Resources\Leaves\Pages\ListLeaves;

use App\Models\Employee;
use App\Models\Leave;

use BackedEnum;

use Filament\Actions\Action;
use Filament\Actions\EditAction;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

use Filament\Resources\Resource;

use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;

use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;

use Illuminate\Database\Eloquent\Builder;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static string|\UnitEnum|null $navigationGroup = 'People';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('employee_id')
                    ->label('Employee')
                    ->options(
                        Employee::pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),

                Select::make('type')
                    ->options([
                        'casual' => 'Casual',
                        'sick' => 'Sick',
                        'earned' => 'Earned',
                        'maternity' => 'Maternity',
                        'unpaid' => 'Unpaid',
                    ])
                    ->required(),

                DatePicker::make('from_date')
                    ->required()
                    ->live(),

                DatePicker::make('to_date')
                    ->required()
                    ->afterOrEqual('from_date')
                    ->live(),

                TextInput::make('days')
                    ->numeric()
                    ->required(),

                Textarea::make('reason'),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('employee.name')
                    ->searchable(),

                TextColumn::make('type')
                    ->badge()
                    ->color('info'),

                TextColumn::make('from_date')
                    ->date('d M Y'),

                TextColumn::make('to_date')
                    ->date('d M Y'),

                TextColumn::make('days'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])

            ->actions([

                Action::make('approve')
                    ->color('success')
                    ->icon('heroicon-o-check')

                    ->visible(fn (Leave $record) =>
                        $record->status === 'pending'
                    )

                    ->action(fn (Leave $record) =>
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ])
                    ),

                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')

                    ->visible(fn (Leave $record) =>
                        $record->status === 'pending'
                    )

                    ->action(fn (Leave $record) =>
                        $record->update([
                            'status' => 'rejected',
                        ])
                    ),

                EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder  // just add this method for scoping
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
            'index' => ListLeaves::route('/'),
            'create' => CreateLeave::route('/create'),
            'edit' => EditLeave::route('/{record}/edit'),
        ];
    }
}