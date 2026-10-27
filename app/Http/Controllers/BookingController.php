<?php

namespace App\Http\Controllers;

use App\Actions\SyncBookingSeats;
use App\Enums\BookingSource;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\EventSummaryResource;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Book seats for an event as a guest. Payment is faked.
     */
    public function store(StoreBookingRequest $request, Event $event, SyncBookingSeats $syncBookingSeats): RedirectResponse
    {
        $booking = new Booking([
            ...$request->safe()->only(['customer_name', 'customer_email']),
            'source' => BookingSource::Online,
        ]);
        $booking->event()->associate($event);

        /** @var list<string> $seatKeys */
        $seatKeys = $request->validated('seats');

        $syncBookingSeats->handle($booking, $seatKeys);

        return to_route('bookings.show', $booking);
    }

    /**
     * Show a booking confirmation.
     */
    public function show(Booking $booking): Response
    {
        $booking->load(['event.venue', 'seats']);

        return Inertia::render('bookings/show', [
            'booking' => [
                'reference' => $booking->reference,
                'customerName' => $booking->customer_name,
                'customerEmail' => $booking->customer_email,
                'source' => $booking->source->value,
                'total' => $booking->total,
                'seats' => $booking->seats->pluck('seat_key')->all(),
                'seatPrices' => $booking->seats->pluck('price', 'seat_key')->all(),
            ],
            'event' => EventSummaryResource::make($booking->event)->resolve(),
            'layout' => $booking->event->venue->layout,
            'prices' => $booking->event->prices,
        ]);
    }
}
