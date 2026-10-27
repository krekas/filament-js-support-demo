<?php

namespace Database\Seeders;

use App\Actions\SyncBookingSeats;
use App\Enums\BookingSource;
use App\Enums\EventStatus;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(SyncBookingSeats $syncBookingSeats): void
    {
        $events = Event::query()
            ->with('venue')
            ->where('status', '!=', EventStatus::Draft)
            ->get();

        foreach ($events as $event) {
            $fillRatio = $event->status === EventStatus::SoldOut ? 0.95 : fake()->randomFloat(2, 0.3, 0.7);

            foreach ($this->seatGroups($event->venue->venueLayout()->seatKeys()) as $seatKeys) {
                if (fake()->randomFloat(2, 0, 1) > $fillRatio) {
                    continue;
                }

                $booking = new Booking([
                    'event_id' => $event->id,
                    'customer_name' => fake()->name(),
                    'customer_email' => fake()->safeEmail(),
                    'source' => fake()->randomElement([
                        BookingSource::Online,
                        BookingSource::Online,
                        BookingSource::Online,
                        BookingSource::BoxOffice,
                        BookingSource::BoxOffice,
                        BookingSource::Comp,
                    ]),
                ]);
                $booking->reference = Booking::generateReference();
                $booking->setRelation('event', $event);

                $syncBookingSeats->handle($booking, $seatKeys);
            }
        }
    }

    /**
     * Split seat keys into small groups of neighbouring seats, like real parties of friends.
     *
     * @param  list<string>  $seatKeys
     * @return list<list<string>>
     */
    protected function seatGroups(array $seatKeys): array
    {
        $groups = [];

        while ($seatKeys !== []) {
            $groups[] = array_splice($seatKeys, 0, fake()->numberBetween(1, 6));
        }

        return $groups;
    }
}
