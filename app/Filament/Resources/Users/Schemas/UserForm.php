<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                    ->required(),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label('Password Confirmation')
                    ->password()
                    ->required(),
                Checkbox::make('is_verified')
                    ->label('Verified Email')
                    ->default(true),
                Radio::make('role')
                    ->label('Role')
                        ->options(fn () => Role::CREATE_USER_ROLES)
                    ->required(),
            ]);
    }
}
