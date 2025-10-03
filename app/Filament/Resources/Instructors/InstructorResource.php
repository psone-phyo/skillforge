<?php

namespace App\Filament\Resources\Instructors;

use App\Enums\InstructorStatus;
use App\Filament\Resources\Instructors\Pages\ManageInstructors;
use App\Models\Instructor;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstructorResource extends Resource
{
    protected static ?string $model = Instructor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    protected static ?string $recordTitleAttribute = 'Instructor';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user.name')
                    ->required(),
                TextInput::make('user.email')
                    ->numeric()
                    ->default(null),
                TextInput::make('title')
                    ->default(null),
                Textarea::make('bio')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('profile_url')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'suspended' => 'Suspended',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Instructor')
            ->columns([
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors(InstructorStatus::COLORS),
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(InstructorStatus::STATUSES),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->form(fn($record) => [
                        TextEntry::make('user.name')
                            ->label('Name')
                            ->default($record->user->name),

                        TextEntry::make('user.email')
                            ->label('Email')
                            ->default($record->user->email),

                        TextEntry::make('title')
                            ->label('Title')
                            ->default($record->title),

                        TextEntry::make('bio')
                            ->label('Bio')
                            ->default($record->bio)

                        
                    ]),
                Action::make('Approve')
                    ->badge()
                    ->color('success')
                    ->icon(Heroicon::OutlinedCheck)
                    ->action(function ($record) {
                        $record->update(['status' => InstructorStatus::ID_APPROVED]);
                    })
                    ->visible(fn($record) => $record->status === InstructorStatus::ID_PENDING || $record->status === InstructorStatus::ID_REJECTED || $record->status === InstructorStatus::ID_SUSPENDED),

                Action::make('Reject')
                    ->badge()
                    ->color('danger')
                    ->icon(Heroicon::XMark)
                    ->action(function ($record) {
                        $record->update(['status' => InstructorStatus::ID_REJECTED]);
                    })
                    ->visible(fn($record) => $record->status === InstructorStatus::ID_PENDING),

                Action::make('Suspend')
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-user-minus')
                    ->action(fn($record) => $record->update(['status' => InstructorStatus::ID_SUSPENDED]))
                    ->visible(fn($record) => $record->status === InstructorStatus::ID_APPROVED),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('pending')
                    ->label(fn() => 'Pending (' . Instructor::where('status', InstructorStatus::ID_PENDING)->count() . ')')
                    ->button()
                    ->color('warning')
                    ->url(fn() => 'instructors?filters[status][value]=pending'),

                Action::make('approved')
                    ->label(fn() => 'Approved (' . Instructor::where('status', InstructorStatus::ID_APPROVED)->count() . ')')
                    ->button()
                    ->color('success')
                    ->url(fn() => 'instructors?filters[status][value]=approved'),

                Action::make('rejected')
                    ->label(fn() => 'Rejected (' . Instructor::where('status', InstructorStatus::ID_REJECTED)->count() . ')')
                    ->button()
                    ->color('danger')
                    ->url(fn() => 'instructors?filters[status][value]=rejected'),

                Action::make('suspended')
                    ->label(fn() => 'Suspended (' . Instructor::where('status', InstructorStatus::ID_SUSPENDED)->count() . ')')
                    ->button()
                    ->color('gray')
                    ->url(fn() => 'instructors?filters[status][value]=suspended'),
            ])

        ;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInstructors::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
