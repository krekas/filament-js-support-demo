<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventSummaryResource;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * List the events that are on sale.
     */
    public function __invoke(): Response
    {
        $events = Event::query()
            ->onSale()
            ->with('venue')
            ->orderBy('starts_at')
            ->get();

        return Inertia::render('home', [
            'events' => EventSummaryResource::collection($events)->resolve(),
        ]);
    }
}
