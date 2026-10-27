<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingSource;
use App\Models\Booking;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('event.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (Booking $record): string => $record->customer_email)
                    ->searchable(['customer_name', 'customer_email']),
                TextColumn::make('source')
                    ->badge(),
                TextColumn::make('seats_count')
                    ->label('Seats')
                    ->counts('seats')
                    ->sortable(),
                TextColumn::make('total')
                    ->money('GBP', divideBy: 100)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('source')
                    ->options(BookingSource::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
