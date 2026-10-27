<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingSeat>
 */
class BookingSeatFactory extends Factory
{
    /**
     * Seat keys handed out by this factory but possibly not inserted yet, keyed by event ID.
     *
     * @var array<int, list<string>>
     */
    protected array $allocatedSeatKeys = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'event_id' => fn (array $attributes): int => Booking::query()->whereKey($attributes['booking_id'])->value('event_id'),
            'seat_key' => fn (array $attributes): ?string => $this->firstAvailableSeatKey($attributes['event_id']),
            'price' => 0,
        ];
    }

    protected function firstAvailableSeatKey(int $eventId): ?string
    {
        $event = Event::query()->with('venue')->whereKey($eventId)->firstOrFail();
        $availableSeatKeys = array_diff(
            $event->venue->venueLayout()->seatKeys(),
            $event->takenSeatKeys(),
            $this->allocatedSeatKeys[$eventId] ?? [],
        );

        $seatKey = array_values($availableSeatKeys)[0] ?? null;

        if ($seatKey !== null) {
            $this->allocatedSeatKeys[$eventId][] = $seatKey;
        }

        return $seatKey;
    }
}
