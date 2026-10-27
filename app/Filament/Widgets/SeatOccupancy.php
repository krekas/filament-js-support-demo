<?php

namespace App\Filament\Widgets;

use App\Enums\BookingSource;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\BookingSeat;
use App\Models\Event;
use Filament\Widgets\Concerns\HasJsRenderer;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Vite;

class SeatOccupancy extends Widget
{
    use HasJsRenderer;
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getRenderer(): string
    {
        return Vite::asset('resources/js/filament/widgets/seat-occupancy.tsx');
    }

    public function getEvent(): ?Event
    {
        $eventId = $this->pageFilters['event_id'] ?? Dashboard::defaultEventId();

        return is_numeric($eventId)
            ? Event::query()->with('venue')->whereKey($eventId)->first()
            : null;
    }

    /**
     * Sends box-office staff to the booking form with the filtered event already selected.
     */
    public function openBookingForm(): void
    {
        $event = $this->getEvent();

        if ($event === null) {
            return;
        }

        $this->redirect(BookingResource::getUrl('create', ['event' => $event->id]));
    }

    /**
     * @return array<string, mixed>
     */
    public function getRendererConfiguration(): array
    {
        $event = $this->getEvent();

        if ($event === null) {
            return ['eventId' => null, 'eventName' => null, 'layout' => null, 'heatmap' => [], 'stats' => null];
        }

        $layout = $event->venue->venueLayout();

        $soldSeats = BookingSeat::query()
            ->join('bookings', 'bookings.id', '=', 'booking_seats.booking_id')
            ->where('booking_seats.event_id', $event->id)
            ->get(['booking_seats.seat_key', 'booking_seats.price', 'bookings.source']);

        $heatmap = $soldSeats->mapWithKeys(fn (BookingSeat $seat): array => [$seat->seat_key => $seat->getAttribute('source')])->all();

        $sections = collect($layout->sections())->map(function (array $section) use ($layout, $soldSeats): array {
            $sectionSeats = $soldSeats->filter(fn (BookingSeat $seat): bool => ($layout->sectionFor($seat->seat_key)['key'] ?? null) === $section['key']);

            return [
                'key' => $section['key'],
                'name' => $section['name'],
                'sold' => $sectionSeats->count(),
                'capacity' => collect($section['rows'])->sum(fn (array $row): int => count($row['seats'])),
                'revenue' => (int) $sectionSeats->sum('price'),
            ];
        });

        return [
            'eventId' => $event->id,
            'eventName' => $event->name,
            'layout' => $event->venue->layout,
            'heatmap' => $heatmap,
            'stats' => [
                'sold' => $soldSeats->count(),
                'capacity' => $layout->capacity(),
                'revenue' => (int) $soldSeats->sum('price'),
                'sections' => $sections->values()->all(),
                'sources' => collect(BookingSource::cases())
                    ->mapWithKeys(fn (BookingSource $source): array => [$source->value => $soldSeats->where('source', $source->value)->count()])
                    ->all(),
            ],
        ];
    }
}
