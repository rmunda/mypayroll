<?php

namespace App\Filament\Resources\LeavePolicies\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use App\Models\FinancialYear;
use App\Models\LeaveType;
use Filament\Schemas\Schema;

class LeavePolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Policy details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('e.g. Standard Policy')
                        ->columnSpanFull(),

                    Select::make('financial_year_id')
                        ->label('Financial year')
                        ->options(
                            FinancialYear::orderByDesc('start_date')
                                ->pluck('label', 'id')
                        )
                        ->searchable()
                        ->required(),

                    Toggle::make('is_default')
                        ->label('Set as default policy')
                        ->helperText('Only one policy can be default per financial year')
                        ->default(false),
                ]),

            Section::make('Earned leave accrual')
                ->description('Configure how earned leave accumulates monthly')
                ->columns(2)
                ->schema([
                    Toggle::make('earned_leave_accrual')
                        ->label('Enable earned leave accrual')
                        ->default(true)
                        ->live()
                        ->helperText('Earned leave balance increases every month'),

                    Select::make('accrual_frequency')
                        ->label('Accrual frequency')
                        ->options([
                            'monthly'   => 'Monthly',
                            'quarterly' => 'Quarterly',
                        ])
                        ->default('monthly')
                        ->visible(
                            fn(callable $get) => $get('earned_leave_accrual') === true
                        ),

                    TextInput::make('earned_accrual_per_month')
                        ->label('Days accrued per month')
                        ->numeric()
                        ->default(1.5)
                        ->suffix('days')
                        ->helperText('e.g. 1.5 days per month = 18 days per year')
                        ->visible(
                            fn(callable $get) => $get('earned_leave_accrual') === true
                        ),
                ]),

            Section::make('Carry forward settings')
                ->columns(2)
                ->schema([
                    Toggle::make('carry_forward_earned')
                        ->label('Allow carry forward of earned leave')
                        ->default(true)
                        ->live()
                        ->helperText('Unused earned leave carries to next year'),

                    TextInput::make('max_carry_forward_days')
                        ->label('Maximum carry forward days')
                        ->numeric()
                        ->default(30)
                        ->suffix('days')
                        ->visible(
                            fn(callable $get) => $get('carry_forward_earned') === true
                        )
                        ->helperText('Maximum days that can be carried to next year'),
                ]),

            Section::make('Leave allocations')
                ->description('Configure days per year for each leave type')
                ->schema([
                    Repeater::make('policyDetails')
                        ->relationship('policyDetails')
                        ->schema([
                            Select::make('leave_type_id')
                                ->label('Leave type')
                                ->options(
                                    LeaveType::where('is_active', true)
                                        ->pluck('name', 'id')
                                )
                                ->required()
                                ->searchable()
                                ->distinct()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                            TextInput::make('days_per_year')
                                ->label('Days per year')
                                ->numeric()
                                ->default(0)
                                ->suffix('days')
                                ->helperText('0 = unlimited'),

                            TextInput::make('accrual_per_month')
                                ->label('Accrual per month')
                                ->numeric()
                                ->default(0)
                                ->suffix('days')
                                ->helperText('0 = no accrual'),

                            Toggle::make('carry_forward')
                                ->label('Allow carry forward')
                                ->default(false)
                                ->live(),

                            TextInput::make('max_carry_forward')
                                ->label('Max carry forward')
                                ->numeric()
                                ->default(0)
                                ->suffix('days')
                                ->visible(
                                    fn(callable $get) => $get('carry_forward') === true
                                ),

                            Toggle::make('allow_encashment')
                                ->label('Allow encashment')
                                ->default(false)
                                ->live(),

                            TextInput::make('max_encashment_days')
                                ->label('Max encashment days')
                                ->numeric()
                                ->default(0)
                                ->suffix('days')
                                ->visible(
                                    fn(callable $get) => $get('allow_encashment') === true
                                ),
                        ])
                        ->columns(3)
                        ->addActionLabel('Add leave type')
                        ->defaultItems(0)
                        ->reorderable(false)
                        ->collapsible()
                        ->itemLabel(fn(array $state) =>
                            LeaveType::find($state['leave_type_id'])?->name ?? 'New leave type'
                        ),
                ]),

        ]);
    }
}
