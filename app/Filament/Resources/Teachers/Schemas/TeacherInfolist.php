<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Models\Teacher;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class TeacherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->columnSpanFull(),
                TextEntry::make('user.email')
                    ->columnSpanFull(),
                TextEntry::make('bio')
                    ->columnSpanFull(),
                TextEntry::make('proposal')
                    ->columnSpanFull(),
                TextEntry::make('cv')
                    ->label('CV')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';

                        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                        $disk = Storage::disk('r2');

                        $url = $disk->temporaryUrl($state, now()->addMinutes(10));

                        return "<a href='{$url}' target='_blank'>View CV</a>";
                    })
                    ->html(),
                ImageEntry::make('profile')
                    ->disk('r2')
                    ->columnSpanFull()
                    ->label('Profile Image'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn(Teacher $record): bool => $record->trashed()),
            ]);
    }
}
