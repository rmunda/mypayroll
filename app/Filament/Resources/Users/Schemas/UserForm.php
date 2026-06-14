<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('avatar'),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->default('password@123')
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText(
                        fn (string $operation) =>
                            $operation === 'create'
                                ? 'Default password is password@123.'
                                : 'Leave blank to keep current password.'
                    ),
                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name', fn ($query) =>
                        auth()->user()?->hasRole('super_admin')
                            ? $query
                            : $query->where('name', '!=', 'super_admin')
                    )
                    ->preload(),
            ]);
    }
}
