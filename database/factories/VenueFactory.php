<?php

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Hall',
            'city' => fake()->city(),
            'layout' => [
                'width' => 600,
                'height' => 400,
                'stage' => ['x' => 200, 'y' => 20, 'width' => 200, 'height' => 30, 'label' => 'Stage'],
                'sections' => [
                    $this->gridSection('stalls', 'Stalls', 'premium', ['A', 'B'], 6, 100),
                    $this->gridSection('balcony', 'Balcony', 'standard', ['A', 'B'], 6, 260),
                ],
            ],
        ];
    }

    /**
     * @param  list<string>  $rowLabels
     * @return array{key: string, name: string, tier: string, rows: list<array{label: string, seats: list<array{number: int, x: int, y: int}>}>}
     */
    protected function gridSection(string $key, string $name, string $tier, array $rowLabels, int $seatsPerRow, int $top): array
    {
        $rows = [];

        foreach ($rowLabels as $rowIndex => $label) {
            $seats = [];

            for ($number = 1; $number <= $seatsPerRow; $number++) {
                $seats[] = ['number' => $number, 'x' => 150 + $number * 40, 'y' => $top + $rowIndex * 40];
            }

            $rows[] = ['label' => $label, 'seats' => $seats];
        }

        return ['key' => $key, 'name' => $name, 'tier' => $tier, 'rows' => $rows];
    }
}
