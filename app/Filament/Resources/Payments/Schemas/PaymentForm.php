<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('course_id')
                    ->required()
                    ->numeric(),
                Textarea::make('ref')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('transaction_url')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('transaction_number')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('course_fee')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('comission')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('total_amount')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('payment_method')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->required(),
                Textarea::make('note')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('purchased_at'),
            ]);
    }
}
