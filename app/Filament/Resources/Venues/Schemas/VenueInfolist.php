<?php

namespace App\Filament\Resources\Venues\Schemas;

use App\Models\Venue;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VenueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('city'),
                TextEntry::make('capacity')
                    ->state(fn (Venue $record): int => $record->capacity())
                    ->suffix(' seats'),
            ])
            ->columns(3);
    }
}
