<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\Course;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('Course')
                    ->options(function () {
                        return Course::where('instructor_id', Auth::id())
                            ->get()
                            ->mapWithKeys(function ($course) {
                                return [$course->id => "{$course->course_code} - {$course->title}"];
                            })
                            ->toArray();
                    }),
                TextInput::make('title')
                    ->required(),
                TextInput::make('mm_title')
                    ->default(null),
                TextInput::make('passing_score')
                    ->numeric()
                    ->default(null),
                Repeater::make('quizQuestions')
                    ->relationship()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('question')
                            ->required()
                            ->label('Question'),

                        Grid::make(2) // 2 columns in one row
                            ->schema([
                                TextInput::make('point')
                                    ->required()
                                    ->label('Points')
                                    ->columnSpan(2), // half width
                            ]),
                        Grid::make(2) // 2 columns in one row
                            ->schema([
                                Repeater::make('options') // QuizQuestion -> Options
                                    ->relationship()
                                    ->columns(2)
                                    ->label('Answer Choices')
                                    ->schema([
                                        TextInput::make('answer')->required()->label('Option'),
                                        Toggle::make('is_correct')->label('Correct Answer?')->default(false)
                                    ])
                                    ->defaultItems(3)
                                    ->columnSpan(2  ),
                            ]),

                    ])
                    ->collapsible()
                    ->defaultItems(1)
                    ->label('Questions')
            ]);
    }
}
