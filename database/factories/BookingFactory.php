<?php

namespace Database\Factories;

use App\Enums\BookingSource;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'source' => BookingSource::Online,
            'total' => 0,
        ];
    }

    public function source(BookingSource $source): static
    {
        return $this->state(fn (array $attributes): array => [
            'source' => $source,
        ]);
    }
}
