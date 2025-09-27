<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Enums\CourseRank;
use App\Enums\CourseStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('mm_title')
                    ->searchable(),
                TextColumn::make('course_type')
                    ->label('Course Type')
                    ->getStateUsing(fn($record) => $record->course_type)
                    ->colors([
                        'success' => fn($state) => $state === 'Free',
                        'primary' => fn($state) => $state === 'Paid',
                    ]),

                TextColumn::make('price')
                    ->getStateUsing(fn($record) => $record->price . ' MMK')
                    ->sortable(),
                TextColumn::make('status')
                    ->getStateUsing(fn($record) => CourseStatus::AVAILABLES[$record->status])
                    ->colors([
                        'warning' => fn($state) => $state === CourseStatus::ON_PROGRESS,
                        'success' => fn($state) => $state === CourseStatus::PUBLISHED,
                        'info' => fn($state) => $state === CourseStatus::FINISHED,
                        'danger' => fn($state) => $state === CourseStatus::HIDE,
                    ])
                    ->searchable(),
                TextColumn::make('rank')
                    ->getStateUsing(fn($record) => CourseRank::AVAILABLES[$record->rank])
                    ->colors([
                        'warning' => fn($state) => $state === CourseRank::BASIC,
                        'info' => fn($state) => $state === CourseRank::INTERMEDIATE,
                        'danger' => fn($state) => $state === CourseRank::ADVANCED,
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
