<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Enums\CourseRank;
use App\Enums\CourseStatus;
use App\Models\Course;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('mm_title'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('mm_description')
                    ->columnSpanFull(),
                TextEntry::make('outline')
                    ->columnSpanFull(),
                TextEntry::make('mm_outline')
                    ->columnSpanFull(),
                TextEntry::make('course_type')
                    ->label('Course Type')
                    ->getStateUsing(fn($record) => $record->course_type)
                    ->colors([
                        'success' => fn($state) => $state === 'Free',
                        'primary' => fn($state) => $state === 'Paid',
                    ]),

                TextEntry::make('price')
                    ->getStateUsing(fn($record) => $record->price . ' MMK'),
                TextEntry::make('status')
                    ->getStateUsing(fn($record) => CourseStatus::AVAILABLES[$record->status])
                    ->colors([
                        'warning' => fn($state) => $state === 'On Progress',
                        'success' => fn($state) => $state === 'Published',
                        'info' => fn($state) => $state === 'Finished',
                        'danger' => fn($state) => $state === 'Hide',
                    ]),
                TextEntry::make('rank')
                    ->getStateUsing(fn($record) => CourseRank::AVAILABLES[$record->rank])
                    ->colors([
                        'warning' => fn($state) => $state === CourseRank::BASIC,
                        'info' => fn($state) => $state === CourseRank::INTERMEDIATE,
                        'danger' => fn($state) => $state === CourseRank::ADVANCED,
                    ]),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn(Course $record): bool => $record->trashed()),
            ]);
    }
}
