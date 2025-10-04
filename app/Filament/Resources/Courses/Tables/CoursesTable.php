<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Enums\CourseRank;
use App\Enums\CourseStatus;
use Filament\Actions\Action;
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
use Illuminate\Support\HtmlString;

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
                    ->badge()
                    ->colors(CourseRank::COLORS),
                TextColumn::make('language')
                    ->badge()
                    ->colors(CourseRank::LANG_COLORS),
                IconColumn::make('is_paid')
                    ->boolean(),
                TextColumn::make('price')
                    ->money('MMK')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors(CourseStatus::COLORS),
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
                DeleteAction::make(),
                Action::make('editTags')
                    ->label('Edit Tags')
                    ->button()
                    ->color('primary')
                    ->modalHeading('Manage Tags')
                    ->modalContent(function ($record) {
                        $html = '';
                        foreach ($record->tags()->get() as $tag) { // use the relationship method to ensure fresh IDs
                            $html .= '<div class="flex items-center mb-2">';

                            // Tag badge
                            $html .= "<span class='inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-800 mr-2'>{$tag->name}</span>";

                            // Delete form (fully self-contained)
                            $html .= "<form method='POST' action='" . route('courses.detach-tag', ['course' => $record->id, 'tag' => $tag->id]) . "' style='display:inline' onsubmit='return confirm(\"Are you sure you want to delete this tag?\")'>";
                            $html .= csrf_field();
                            $html .= method_field('DELETE');
                            $html .= "<button type='submit' class='text-red-600 hover:text-red-800'>Delete</button>";
                            $html .= "</form>";

                            $html .= '</div>';
                        }

                        return new HtmlString($html);
                    }),
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
