<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Actions\SyncBookingSeats;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    /**
     * Prefills the event when arriving from the dashboard's "Open in box office" button.
     */
    protected function afterFill(): void
    {
        $eventId = request()->integer('event');

        if ($eventId > 0 && Event::query()->whereKey($eventId)->exists()) {
            $this->data['event_id'] = $eventId;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Booking
    {
        $booking = new Booking(Arr::except($data, ['seats']));

        /** @var list<string> $seatKeys */
        $seatKeys = $data['seats'] ?? [];

        return app(SyncBookingSeats::class)->handle($booking, $seatKeys, errorKey: 'data.seats');
    }
}
