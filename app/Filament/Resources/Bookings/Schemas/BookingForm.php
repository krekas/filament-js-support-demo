<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingSource;
use App\Filament\Forms\Components\SeatPicker;
use App\Models\Event;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BookingForm
{
    public const MAX_SEATS = 8;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Booking')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('event_id')
                            ->label('Event')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('seats', [])),
                        ToggleButtons::make('source')
                            ->options(fn (string $operation): array => self::sourceOptions($operation))
                            ->colors(collect(BookingSource::cases())->mapWithKeys(fn (BookingSource $source): array => [$source->value => $source->getColor()])->all())
                            ->default(BookingSource::BoxOffice->value)
                            ->inline()
                            ->required()
                            ->live(),
                        TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer_email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Seats')
                    ->columnSpanFull()
                    ->schema([
                        SeatPicker::make('seats')
                            ->hiddenLabel()
                            ->event(fn (Get $get): ?Event => Event::query()->with('venue')->whereKey($get('event_id'))->first())
                            ->comp(fn (Get $get): bool => self::isComp($get('source')))
                            ->maxSeats(self::MAX_SEATS)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Online bookings only come from the public site, so staff can't create them here.
     *
     * @return array<string, string>
     */
    protected static function sourceOptions(string $operation): array
    {
        return collect(BookingSource::cases())
            ->reject(fn (BookingSource $source): bool => $operation === 'create' && $source === BookingSource::Online)
            ->mapWithKeys(fn (BookingSource $source): array => [$source->value => $source->getLabel()])
            ->all();
    }

    protected static function isComp(mixed $source): bool
    {
        return ($source instanceof BookingSource ? $source : BookingSource::tryFrom((string) $source)) === BookingSource::Comp;
    }
}
