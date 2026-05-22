<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;

use App\Models\Department;
use App\Models\Employee;
use App\Models\PayStructure;

use BackedEnum;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Resources\Resource;

use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;

use Filament\Tables\Table;

use Filament\Actions\Action;
use Filament\Actions\EditAction;

use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Filters\SelectFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|\UnitEnum|null $navigationGroup = 'PEOPLE';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Personal details')
                    ->columns(2)
                    ->schema([

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->tel(),

                        TextInput::make('employee_code')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                    ]),

                Section::make('Job details')
                    ->columns(2)
                    ->schema([

                        Select::make('department_id')
                            ->label('Department')
                            ->options(
                                Department::pluck('name', 'id')
                            )
                            ->searchable()
                            ->required(),

                        TextInput::make('designation')
                            ->required(),

                        Select::make('pay_structure_id')
                            ->label('Pay structure')
                            ->options(
                                PayStructure::pluck('name', 'id')
                            )
                            ->required(),

                        DatePicker::make('date_of_joining')
                            ->required(),

                        Select::make('pay_frequency')
                            ->options([
                                'monthly' => 'Monthly',
                                'biweekly' => 'Bi-weekly',
                                'weekly' => 'Weekly',
                            ])
                            ->default('monthly'),

                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'on_leave' => 'On Leave',
                            ])
                            ->default('active'),
                    ]),

                Section::make('Salary & tax')
                    ->columns(2)
                    ->schema([

                        TextInput::make('basic_salary')
                            ->numeric()
                            ->prefix('INR')
                            ->required(),

                        Select::make('tax_regime')
                            ->options([
                                'new' => 'New regime (default)',
                                'old' => 'Old regime',
                            ])
                            ->default('new'),
                    ]),

                Section::make('Bank & statutory')
                    ->columns(2)
                    ->schema([

                        TextInput::make('bank_name'),

                        TextInput::make('bank_account'),

                        TextInput::make('ifsc_code')
                            ->label('IFSC code'),

                        TextInput::make('pan_number')
                            ->label('PAN number'),

                        TextInput::make('uan_number')
                            ->label('UAN (PF)'),

                        TextInput::make('esic_number')
                            ->label('ESIC number'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('employee_code')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->fontFamily('mono'),

                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('department.name')
                    ->label('Dept')
                    ->sortable(),

                TextColumn::make('designation')
                    ->searchable(),

                TextColumn::make('basic_salary')
                    ->label('Basic')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('date_of_joining')
                    ->date('M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'on_leave' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
            ])

            ->filters([

                SelectFilter::make('department')
                    ->relationship('department', 'name'),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on_leave' => 'On Leave',
                    ]),
            ])

            ->actions([

                EditAction::make(),

                Action::make('payslips')
                    ->icon('heroicon-o-document-text')
                    ->label('Pay slips')
                    ->url(fn (Employee $record) =>
                        PaySlipResource::getUrl(
                            'index',
                            [
                                'tableFilters[employee][value]' => $record->id,
                            ]
                        )
                    ),
            ])

            ->bulkActions([

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}