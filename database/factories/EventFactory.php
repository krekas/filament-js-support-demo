<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(rtrim(fake()->unique()->sentence(3, variableNbWords: false), '.'));

        return [
            'venue_id' => Venue::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'starts_at' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'description' => fake()->paragraph(),
            'poster_url' => 'https://picsum.photos/seed/'.Str::slug($name).'/800/500',
            'prices' => ['premium' => 8500, 'standard' => 5500],
            'status' => EventStatus::OnSale,
        ];
    }

    public function status(EventStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
        ]);
    }
}
