<?php

namespace App\Filament\Resources\LeaveTypes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LeaveTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Leave type details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('e.g. Casual Leave'),

                    TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder('e.g. CL')
                        ->helperText('Short code used across the system'),

                    Select::make('color')
                        ->options([
                            'info'    => 'Blue',
                            'success' => 'Green',
                            'warning' => 'Yellow',
                            'danger'  => 'Red',
                            'gray'    => 'Gray',
                            'purple'  => 'Purple',
                            'pink'    => 'Pink',
                        ])
                        ->default('info')
                        ->required(),

                    TextInput::make('min_notice_days')
                        ->numeric()
                        ->default(0)
                        ->suffix('days advance notice')
                        ->helperText('0 means no advance notice required'),

                    TextInput::make('max_days_per_year')
                        ->numeric()
                        ->default(0)
                        ->suffix('days per year')
                        ->helperText('0 means unlimited'),

                    TextInput::make('max_days_per_request')
                        ->numeric()
                        ->default(0)
                        ->suffix('days per request')
                        ->helperText('0 means no limit per request'),
                ]),

            Section::make('Settings')
                ->columns(2)
                ->schema([
                    Toggle::make('is_paid')
                        ->label('Paid leave')
                        ->default(true)
                        ->helperText('Salary is not deducted for paid leaves'),

                    Toggle::make('is_accrued')
                        ->label('Accrues monthly')
                        ->default(false)
                        ->helperText('Leave balance builds up each month'),

                    Toggle::make('requires_document')
                        ->label('Requires document')
                        ->default(false)
                        ->helperText('e.g. medical certificate for sick leave'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ]),

            Section::make('Description')
                ->schema([
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Brief description of this leave type'),
                ]),

        ]);
    }
}
