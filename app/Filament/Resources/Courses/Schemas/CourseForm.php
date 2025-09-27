<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Enums\CourseRank;
use App\Enums\CourseStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('mm_title')
                    ->label('Myanmar Title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('mm_description')
                    ->required()
                    ->label('Myanmar Description')
                    ->columnSpanFull(),
                Textarea::make('outline')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('mm_outline')
                    ->label('Myanmar Outline')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(CourseStatus::AVAILABLES),
                Select::make('rank')
                    ->options(CourseRank::AVAILABLES),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('MMK')
                    ->hidden(fn($get) => $get('is_free'))
                    ->dehydrated(fn($state, $get) => !$get('is_free') || $state === 0),
                FileUpload::make('cover_photo')
                    ->disk('r2')
                    ->directory('covers')
                    ->required()
                    ->acceptedFileTypes([
                            'image/jpeg',
                            'image/png',
                        ])
                        ->maxSize(10240)
                        ->imagePreviewHeight('150')
                    ->helperText('Upload an image or PDF, max 10MB.'),
                Toggle::make('is_free')
                    ->label('Is Free?')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('price', '0.00');
                        }
                    }),
            ]);
    }
}
