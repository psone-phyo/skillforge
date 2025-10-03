<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\Input;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('language')
                    ->label('Lecture Language')
                    ->options(['Myanmar' => 'Myanmar', 'English' => 'English'])
                    ->required(),
                FileUpload::make('thumbnail_url')
                    ->disk('r2')
                    ->directory('thumbnails')
                    ->image()
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('mm_title')
                    ->required(),
                TextInput::make('sub_title')
                    ->default(null),
                TextInput::make('mm_sub_title')
                    ->default(null),
                TextInput::make('description')
                    ->default(null),
                TextInput::make('mm_description')
                    ->default(null),
                Select::make('level')
                    ->options(['basic' => 'Basic', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'])
                    ->required(),
                Toggle::make('is_paid')
                    ->label('Paid Course?')
                    ->reactive()
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('MMK')
                    ->visible(fn($get) => $get('is_paid'))
                    ->required(fn($get) => $get('is_paid'))
                    ->default(null),
                TagsInput::make('tags')
                    ->default(null),
            ]);
    }
}
