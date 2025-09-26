<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TeacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user.name')
                    ->required()
                    ->visible(fn ($record) => $record == null),
                TextInput::make('user.email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->visible(fn ($record) => $record == null),
                TextInput::make('user.password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->confirmed()
                    ->visible(fn ($record) => $record == null),
                TextInput::make('user.password_confirmation')
                    ->label('Password Confirmation')
                    ->password()
                    ->required()
                    ->visible(fn ($record) => $record == null),
                Checkbox::make('user.is_verified')
                    ->label('Verified Email')
                    ->default(true)
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record == null),
                Textarea::make('bio')
                    ->required(),
                Textarea::make('proposal')
                    ->required(),
                FileUpload::make('cv')
                    ->disk('r2')
                    ->directory('cvs')
                    ->required()
                       ->acceptedFileTypes([
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                        ])
                        ->maxSize(10240)
                        ->helperText('Upload an image or PDF, max 10MB.'),
                FileUpload::make('profile')
                    ->disk('r2')
                    ->directory('profiles')
                    ->required()
                    ->acceptedFileTypes([
                            'image/jpeg',
                            'image/png',
                        ])
                        ->maxSize(10240)
                        ->imagePreviewHeight('150')
                    ->helperText('Upload an image or PDF, max 10MB.'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'suspended' => 'Suspended',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
