<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\Quiz;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\Grid;

class QuizInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('course')
                    ->formatStateUsing(fn(Quiz $record) => "{$record->course->course_code} - {$record->course->title}"),
                TextEntry::make('instructor_id')
                    ->numeric(),
                TextEntry::make('title'),
                TextEntry::make('mm_title')
                    ->placeholder('-'),
                TextEntry::make('passing_score')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn(Quiz $record): bool => $record->trashed()),
                RepeatableEntry::make('quizQuestions')
                    ->columnSpanFull()
                    ->schema([
                        ComponentsGrid::make(2)
                            ->schema([
                                TextEntry::make('question')->label('Question'),
                                TextEntry::make('point'),
                            ])
                            ->columnSpan(1),
                        RepeatableEntry::make('options')
                            ->schema([
                                TextEntry::make('answer')->label('Option'),
                                TextEntry::make('is_correct')->label('Correct')
                                    ->badge()
                                    ->colors([
                                        'success' => fn($state) => $state == true,
                                        'danger' => fn($state) => $state == false,
                                    ])
                                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),
                            ])
                            ->columns(2),
                    ]),

            ]);
    }
}
