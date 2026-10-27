<?php

namespace App\Filament\Resources\Venues\Tables;

use App\Models\Venue;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VenuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('capacity')
                    ->state(fn (Venue $record): int => $record->capacity())
                    ->numeric(),
                TextColumn::make('events_count')
                    ->label('Events')
                    ->counts('events'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
