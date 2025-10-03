<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                    ->unique()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn(string $context): bool => $context === 'create') // only required on create
                    ->rule('confirmed')
                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state)) // only save if user typed something
                    ->label('Password'),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required(fn(string $context): bool => $context === 'create') // only required on create
                    ->dehydrated(false) // never save confirm field
                    ->label('Confirm Password'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->required(),
                Select::make('role')
                    ->label('Role')
                    ->options(Role::AVAILABLES)
                    ->afterStateHydrated(fn ($set, $record) =>
                        $set('role', $record?->roles->first()?->name)
                    )
                        ->disabled(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord)

            ]);
    }
}
