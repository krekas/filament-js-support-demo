<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Venue::query()->create([
            'name' => 'Grand Theatre',
            'city' => 'London',
            'layout' => [
                'width' => 1000,
                'height' => 700,
                'stage' => ['x' => 300, 'y' => 20, 'width' => 400, 'height' => 40, 'label' => 'Stage'],
                'sections' => [
                    $this->curvedSection('stalls', 'Stalls', 'premium', range('A', 'H'), firstRadius: 140, spread: 0.9),
                    $this->curvedSection('balcony', 'Balcony', 'standard', range('A', 'E'), firstRadius: 450, spread: 0.7),
                ],
            ],
        ]);

        Venue::query()->create([
            'name' => 'The Basement Club',
            'city' => 'Manchester',
            'layout' => [
                'width' => 600,
                'height' => 360,
                'stage' => ['x' => 200, 'y' => 20, 'width' => 200, 'height' => 36, 'label' => 'Stage'],
                'sections' => [
                    $this->straightSection('floor', 'Floor', 'standard', range('A', 'E'), seatsPerRow: 10),
                ],
            ],
        ]);
    }

    /**
     * Rows curve around the stage: each row is an arc with a larger radius, and longer rows hold more seats.
     *
     * @param  list<string>  $rowLabels
     * @return array{key: string, name: string, tier: string, rows: list<array{label: string, seats: list<array{number: int, x: float, y: float}>}>}
     */
    protected function curvedSection(string $key, string $name, string $tier, array $rowLabels, int $firstRadius, float $spread): array
    {
        $centerX = 500;
        $centerY = 0;
        $rowGap = 34;
        $seatGap = 26;
        $rows = [];

        foreach ($rowLabels as $rowIndex => $label) {
            $radius = $firstRadius + $rowIndex * $rowGap;
            $seatCount = (int) floor($radius * 2 * $spread / $seatGap) + 1;
            $seats = [];

            for ($seatIndex = 0; $seatIndex < $seatCount; $seatIndex++) {
                $angle = -$spread + (2 * $spread) * $seatIndex / max($seatCount - 1, 1);

                $seats[] = [
                    'number' => $seatIndex + 1,
                    'x' => round($centerX + $radius * sin($angle), 1),
                    'y' => round($centerY + $radius * cos($angle), 1),
                ];
            }

            $rows[] = ['label' => $label, 'seats' => $seats];
        }

        return ['key' => $key, 'name' => $name, 'tier' => $tier, 'rows' => $rows];
    }

    /**
     * @param  list<string>  $rowLabels
     * @return array{key: string, name: string, tier: string, rows: list<array{label: string, seats: list<array{number: int, x: int, y: int}>}>}
     */
    protected function straightSection(string $key, string $name, string $tier, array $rowLabels, int $seatsPerRow): array
    {
        $rows = [];

        foreach ($rowLabels as $rowIndex => $label) {
            $seats = [];

            for ($number = 1; $number <= $seatsPerRow; $number++) {
                $seats[] = ['number' => $number, 'x' => 120 + ($number - 1) * 40, 'y' => 110 + $rowIndex * 50];
            }

            $rows[] = ['label' => $label, 'seats' => $seats];
        }

        return ['key' => $key, 'name' => $name, 'tier' => $tier, 'rows' => $rows];
    }
}
