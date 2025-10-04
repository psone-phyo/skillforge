<?php

namespace App\Filament\Resources\Lessons\Tables;

use App\Models\Course;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('course.instructor', function ($q) {
                    $q->where('id', Auth::id());
                });
            })
            ->columns([
                TextColumn::make('course.title')
                    ->formatStateUsing(fn($record) => "{$record->course->course_code} - {$record->course->title}")
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('mm_title')
                    ->searchable(),
                IconColumn::make('is_locked')
                    ->boolean(),
                TextColumn::make('sort')
                    ->numeric()
                    ->sortable(),
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
                TrashedFilter::make(),
                SelectFilter::make('course_id')
                    ->label('Course')
                    ->options(function () {
                        return Course::where('instructor_id', Auth::id())
                            ->get()
                            ->mapWithKeys(function ($course) {
                                return [$course->id => "{$course->course_code} - {$course->title}"];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->placeholder('Course')
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
