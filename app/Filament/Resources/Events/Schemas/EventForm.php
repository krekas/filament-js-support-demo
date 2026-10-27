<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventStatus;
use App\Filament\Schemas\Components\EventCardPreview;
use App\Models\Event;
use App\Models\Venue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 2])
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Details')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true),
                                Select::make('venue_id')
                                    ->label('Venue')
                                    ->relationship('venue', 'name')
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->disabled(fn (?Event $record): bool => (bool) $record?->bookingSeats()->exists())
                                    ->helperText(fn (?Event $record): ?string => $record?->bookingSeats()->exists() ? 'Seats are already booked at this venue.' : null),
                                DateTimePicker::make('starts_at')
                                    ->label('Starts at')
                                    ->seconds(false)
                                    ->required()
                                    ->live(),
                                Select::make('status')
                                    ->options(EventStatus::class)
                                    ->default(EventStatus::Draft)
                                    ->required()
                                    ->live(),
                                TextInput::make('poster_url')
                                    ->label('Poster URL')
                                    ->url()
                                    ->maxLength(255)
                                    ->live(onBlur: true),
                                self::priceInput('premium', 'Premium price'),
                                self::priceInput('standard', 'Standard price'),
                                Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Customer preview')
                            ->description('Rendered by the same React component as the public site.')
                            ->schema([
                                EventCardPreview::make()
                                    ->key('event-card-preview'),
                            ]),
                    ]),
            ]);
    }

    /**
     * Drop tiers left empty, so the event only has prices for the tiers it sells.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function withoutEmptyPrices(array $data): array
    {
        $data['prices'] = array_filter($data['prices'] ?? [], fn (mixed $price): bool => $price !== null);

        return $data;
    }

    /**
     * Every tier the venue sells needs a price before tickets go on sale; drafts can stay incomplete.
     */
    protected static function requiresPrice(Get $get, string $tier): bool
    {
        $status = $get('status');
        $status = $status instanceof EventStatus ? $status : EventStatus::tryFrom((string) $status);

        if ($status !== EventStatus::OnSale) {
            return false;
        }

        $venue = Venue::query()->whereKey($get('venue_id'))->first();

        return $venue !== null && in_array($tier, $venue->venueLayout()->tiers(), strict: true);
    }

    /**
     * Prices are stored in cents but edited in pounds.
     */
    protected static function priceInput(string $tier, string $label): TextInput
    {
        return TextInput::make("prices.{$tier}")
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->step(0.01)
            ->prefix('£')
            ->helperText('Leave empty if the venue has no '.$tier.' seats.')
            ->required(fn (Get $get): bool => self::requiresPrice($get, $tier))
            ->validationMessages(['required' => 'Seats in this tier need a price before the event goes on sale.'])
            ->live(onBlur: true)
            ->formatStateUsing(fn (mixed $state): ?string => is_numeric($state) ? number_format($state / 100, 2, '.', '') : null)
            ->dehydrateStateUsing(fn (mixed $state): ?int => is_numeric($state) ? (int) round(((float) $state) * 100) : null);
    }
}
