<?php

namespace App\Filament\Resources\WeeklyOffRules\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WeeklyOffRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Rule details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('e.g. 5 Day Week')
                        ->columnSpanFull(),

                    Toggle::make('is_default')
                        ->label('Set as default rule')
                        ->helperText('Applied to employees with no specific rule assigned')
                        ->columnSpanFull(),
                ]),

            Section::make('Working days')
                ->description('Select which days are working days')
                ->columns(4)
                ->schema([
                    Toggle::make('monday')
                        ->label('Monday')
                        ->default(true),

                    Toggle::make('tuesday')
                        ->label('Tuesday')
                        ->default(true),

                    Toggle::make('wednesday')
                        ->label('Wednesday')
                        ->default(true),

                    Toggle::make('thursday')
                        ->label('Thursday')
                        ->default(true),

                    Toggle::make('friday')
                        ->label('Friday')
                        ->default(true),

                    Toggle::make('sunday')
                        ->label('Sunday')
                        ->default(false),
                ]),

            Section::make('Saturday configuration')
                ->columns(2)
                ->schema([
                    Toggle::make('saturday')
                        ->label('Saturday is working')
                        ->default(false)
                        ->live()
                        ->helperText('Enable to configure saturday working rules'),

                    Select::make('saturday_type')
                        ->label('Saturday type')
                        ->options([
                            'working'       => 'Full working day',
                            'half_day'      => 'Half day',
                            'alternate_1_3' => 'Alternate (1st & 3rd Saturday)',
                            'alternate_2_4' => 'Alternate (2nd & 4th Saturday)',
                            'non_working'   => 'Non working',
                        ])
                        ->default('non_working')
                        ->visible(
                            fn(callable $get) => $get('saturday') === true
                        )
                        ->required(
                            fn(callable $get) => $get('saturday') === true
                        ),
                ]),

        ]);
    }
}
