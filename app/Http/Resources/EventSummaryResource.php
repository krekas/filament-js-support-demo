<?php

namespace App\Http\Resources;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Venue;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The props of the shared `EventCard` React component, used by both the public site and the Filament preview.
 *
 * @mixin Event
 */
class EventSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int|null, name: string, slug: string|null, venueName: string|null, venueCity: string|null, startsAt: string|null, timezone: string, posterUrl: string|null, fromPrice: int|null, status: string, statusLabel: string}
     */
    public function toArray(Request $request): array
    {
        $status = $this->status ?? EventStatus::Draft;
        $prices = array_filter($this->prices ?? [], is_int(...));

        // Unsaved events (the Filament preview) may not have a venue or date yet.
        $venue = $this->resource->getRelationValue('venue');
        $startsAt = $this->resource->getAttribute('starts_at');

        return [
            'id' => $this->id,
            'name' => (string) $this->name,
            'slug' => $this->slug,
            'venueName' => $venue instanceof Venue ? $venue->name : null,
            'venueCity' => $venue instanceof Venue ? $venue->city : null,
            'startsAt' => $startsAt instanceof CarbonInterface ? $startsAt->toIso8601String() : null,
            'timezone' => $startsAt instanceof CarbonInterface ? $startsAt->getTimezone()->getName() : config('app.timezone'),
            'posterUrl' => $this->poster_url,
            'fromPrice' => $prices === [] ? null : min($prices),
            'status' => $status->value,
            'statusLabel' => $status->getLabel(),
        ];
    }
}
