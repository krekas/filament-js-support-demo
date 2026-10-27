<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grandTheatre = Venue::query()->where('name', 'Grand Theatre')->firstOrFail();
        $basementClub = Venue::query()->where('name', 'The Basement Club')->firstOrFail();

        $events = [
            [$grandTheatre, 'The Midnight Orchestra', 12, EventStatus::OnSale, 'A late-night programme of film scores and modern classics, played by candlelight.'],
            [$grandTheatre, 'Hamlet Reimagined', 20, EventStatus::OnSale, 'Shakespeare\'s tragedy, staged in a crumbling 1920s hotel.'],
            [$grandTheatre, 'Comedy Gala Night', 34, EventStatus::OnSale, 'Six of the country\'s sharpest stand-ups on one stage for one night only.'],
            [$grandTheatre, 'Swan Lake', 48, EventStatus::SoldOut, 'The classic ballet, performed by a touring company with a live orchestra.'],
            [$basementClub, 'Late Jazz Sessions', 9, EventStatus::OnSale, 'An intimate evening of standards and improvisation from a local quartet.'],
            [$basementClub, 'Open Mic Showcase', 60, EventStatus::Draft, 'New voices, new songs. Line-up to be announced.'],
        ];

        foreach ($events as [$venue, $name, $daysFromNow, $status, $description]) {
            $slug = Str::slug($name);

            Event::query()->create([
                'venue_id' => $venue->id,
                'name' => $name,
                'slug' => $slug,
                'starts_at' => now()->addDays($daysFromNow)->setTime(19, 30),
                'description' => $description,
                'poster_url' => "https://picsum.photos/seed/{$slug}/800/500",
                'prices' => $venue->is($grandTheatre)
                    ? ['premium' => 8500, 'standard' => 5500]
                    : ['standard' => 2500],
                'status' => $status,
            ]);
        }
    }
}
