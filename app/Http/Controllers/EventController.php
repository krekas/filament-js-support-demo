<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\EventSummaryResource;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Show an event with its seat map and checkout form.
     */
    public function show(Event $event): Response
    {
        abort_if($event->status === EventStatus::Draft, 404);

        $event->load('venue');

        return Inertia::render('events/show', [
            'event' => EventSummaryResource::make($event)->resolve(),
            'description' => $event->description,
            'layout' => $event->venue->layout,
            'prices' => $event->prices,
            'takenSeats' => $event->takenSeatKeys(),
            'maxSeats' => StoreBookingRequest::MAX_SEATS,
            'isOnSale' => $event->status === EventStatus::OnSale,
        ]);
    }
}
