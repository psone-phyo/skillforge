<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Enums\CourseRank;
use App\Enums\CourseStatus;
use App\Models\Course;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\HtmlString;

class CourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('instructor.name')
                    ->visible(fn() => FacadesAuth::user()->hasRole('super_admin')),
                TextEntry::make('course_code'),
                TextEntry::make('title'),
                TextEntry::make('mm_title'),
                TextEntry::make('slug'),
                TextEntry::make('sub_title')
                    ->placeholder('-'),
                TextEntry::make('mm_sub_title')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-'),
                TextEntry::make('mm_description')
                    ->placeholder('-'),
                TextEntry::make('level')
                    ->badge()
                    ->colors(CourseRank::COLORS),
                TextEntry::make('language')
                    ->badge()
                    ->colors(CourseRank::LANG_COLORS),
                ImageEntry::make('thumbnail_url')
                    ->label('Thumbnail')
                    ->disk('r2'),
                IconEntry::make('is_paid')
                    ->boolean(),
                TextEntry::make('price')
                    ->money('MMK')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge()
                    ->colors(CourseStatus::COLORS),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn(Course $record): bool => $record->trashed()),
                TextEntry::make('tags')
                    ->label('Tags')
                    ->formatStateUsing(fn($state) => $state->name)
                    ->badge(),
            ]);
    }
}
