<?php

namespace App\Filament\Resources\Events\Tables;

use App\Enums\EventStatus;
use App\Models\Event;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('venue'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('venue.name')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('booking_seats_count')
                    ->label('Sold')
                    ->counts('bookingSeats')
                    ->formatStateUsing(fn (int $state, Event $record): string => "{$state} / {$record->venue->capacity()}")
                    ->sortable(),
            ])
            ->defaultSort('starts_at')
            ->filters([
                SelectFilter::make('status')
                    ->options(EventStatus::class),
                SelectFilter::make('venue')
                    ->relationship('venue', 'name'),
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
