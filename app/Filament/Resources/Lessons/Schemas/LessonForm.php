<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Models\Course;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('Course')
                    ->required()
                    ->options(function () {
                        return Course::where('instructor_id', Auth::id())
                            ->get()
                            ->mapWithKeys(function ($course) {
                                return [$course->id => "{$course->course_code} - {$course->title}"];
                            })
                            ->toArray();
                    })
                    ->searchable(),
                TextInput::make('title')
                    ->default(null),
                TextInput::make('mm_title')
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('mm_description')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('video_url')
                ->label('Lesson Video')
                    ->disk('r2')
                    ->directory('lessons')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_locked')
                    ->required(),
                TextInput::make('sort')
                    ->required()
                    ->numeric(),
            ]);
    }
}
