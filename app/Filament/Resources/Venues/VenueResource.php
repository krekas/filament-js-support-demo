<?php

namespace App\Filament\Resources\Venues;

use App\Filament\Resources\Venues\Pages\ListVenues;
use App\Filament\Resources\Venues\Pages\ViewVenue;
use App\Filament\Resources\Venues\Schemas\VenueInfolist;
use App\Filament\Resources\Venues\Tables\VenuesTable;
use App\Models\Venue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Venues and their seat layouts are seeded only, so this resource is read-only.
 */
class VenueResource extends Resource
{
    protected static ?string $model = Venue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return VenueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVenues::route('/'),
            'view' => ViewVenue::route('/{record}'),
        ];
    }
}
