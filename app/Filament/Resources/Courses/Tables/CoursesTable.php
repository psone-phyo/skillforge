<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('instructor_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('course_code')
                    ->searchable(),
                // ImageColumn::make('thumbnail_url')
                //     ->label('Thumbnail')
                //     ->disk('r2')
                //     ->square()
                //     ->height(50)
                //     ->width(50),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('mm_title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('sub_title')
                    ->searchable(),
                TextColumn::make('mm_sub_title')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('mm_description')
                    ->searchable(),
                TextColumn::make('level')
                    ->badge(),
                TextColumn::make('language')
                    ->badge(),
                IconColumn::make('is_paid')
                    ->boolean(),
                TextColumn::make('price')
                    ->money('MMK')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('published_at')
                    ->dateTime()
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
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
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
