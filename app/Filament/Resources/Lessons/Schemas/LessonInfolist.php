<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Models\Lesson;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class LessonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('course')
                    ->formatStateUsing(fn (Lesson $record) => "{$record->course->course_code} - {$record->course->title}"),
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('mm_title')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('mm_description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                Action::make('playVideo')
                    ->label('▶ Play Video')
                    ->button()
                    ->color('primary')
                    ->modalHeading('Video Player')
                    ->modalContent(fn ($record) => view('filament.components.video_model', [
                        'url' => config('filesystems.disks.r2.url') . $record->video_url,
                    ]))
                    ->modalWidth('lg'),
                IconEntry::make('is_locked')
                    ->boolean(),
                TextEntry::make('sort')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn(Lesson $record): bool => $record->trashed()),
            ]);
    }
}
