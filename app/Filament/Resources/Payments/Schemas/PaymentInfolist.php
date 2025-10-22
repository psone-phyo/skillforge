<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('course_id')
                    ->numeric(),
                TextEntry::make('ref')
                    ->columnSpanFull(),
                TextEntry::make('transaction_url')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('transaction_number')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('course_fee')
                    ->columnSpanFull(),
                TextEntry::make('comission')
                    ->columnSpanFull(),
                TextEntry::make('total_amount')
                    ->columnSpanFull(),
                TextEntry::make('payment_method')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('note')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('purchased_at')
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
                    ->visible(fn (Payment $record): bool => $record->trashed()),
            ]);
    }
}
