<?php

namespace App\Models;

use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $venue_id
 * @property string $name
 * @property string $slug
 * @property Carbon $starts_at
 * @property string|null $description
 * @property string|null $poster_url
 * @property array<string, int> $prices
 * @property EventStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Venue $venue
 */
#[Fillable(['venue_id', 'name', 'slug', 'starts_at', 'description', 'poster_url', 'prices', 'status'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return HasMany<BookingSeat, $this>
     */
    public function bookingSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    /**
     * Seat keys already sold for this event, optionally ignoring one booking's own seats.
     *
     * @return list<string>
     */
    public function takenSeatKeys(?Booking $except = null): array
    {
        if (! $this->exists) {
            return [];
        }

        $seatKeys = $this->bookingSeats()
            ->when($except?->exists, fn (Builder $query) => $query->where('booking_id', '!=', $except?->getKey()))
            ->pluck('seat_key')
            ->all();

        return array_values(array_map(strval(...), $seatKeys));
    }

    public function priceForTier(?string $tier): ?int
    {
        if ($tier === null) {
            return null;
        }

        return $this->prices[$tier] ?? null;
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function onSale(Builder $query): void
    {
        $query->where('status', EventStatus::OnSale);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'prices' => 'array',
            'status' => EventStatus::class,
        ];
    }
}
