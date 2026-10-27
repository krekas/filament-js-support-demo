<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Actions\SyncBookingSeats;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

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
