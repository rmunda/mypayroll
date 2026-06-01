<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

use Filament\Schemas\Schema;

use App\Models\PayStructure;
use App\Models\Department;
use App\Models\WeeklyOffRule;




class EmployeeForm
{
    public static function configure(Schema $schema): Schema
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

                        Select::make('weekly_off_rule_id')
                            ->label('Weekly off rule')
                            ->options(
                                WeeklyOffRule::pluck('name', 'id')
                            )
                            ->searchable()
                            ->placeholder('Uses default rule if not set'),

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

                Section::make('Portal access')
                    ->visibleOn('edit')
                    ->schema([
                        Toggle::make('is_login_active')
                            ->label('Portal login enabled')
                            ->helperText('Turn off to prevent this employee from logging in')
                            ->dehydrated(false)
                            ->live()
                            ->formatStateUsing(
                                fn($record) => $record?->user?->is_active ?? false
                            ),
                    ]),   
            ]);
    }
}
