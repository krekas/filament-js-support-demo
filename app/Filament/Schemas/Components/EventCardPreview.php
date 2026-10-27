<?php

namespace App\Filament\Schemas\Components;

use App\Enums\EventStatus;
use App\Http\Resources\EventSummaryResource;
use App\Models\Event;
use App\Models\Venue;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Concerns\HasJsRenderer;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Vite;

class EventCardPreview extends Component
{
    use HasJsRenderer;

    public static function make(): static
    {
        $static = app(static::class);
        $static->configure();

        return $static;
    }

    public function getRenderer(): string
    {
        return Vite::asset('resources/js/filament/schemas/components/event-card-preview.tsx');
    }

    /**
     * The exact props the public home page passes to `EventCard`, built from the unsaved form state.
     *
     * @return array<string, mixed>
     */
    public function getRendererConfiguration(): array
    {
        $event = self::eventFromState($this->makeGetUtility());

        return EventSummaryResource::make($event)->resolve();
    }

    protected static function eventFromState(Get $get): Event
    {
        $status = $get('status');

        $event = new Event([
            'name' => $get('name'),
            'slug' => $get('slug'),
            'starts_at' => filled($get('starts_at')) ? $get('starts_at') : null,
            'poster_url' => $get('poster_url'),
            'prices' => self::pricesFromState($get('prices')),
            'status' => $status instanceof EventStatus ? $status : EventStatus::tryFrom((string) $status),
        ]);

        $event->setRelation('venue', Venue::query()->whereKey($get('venue_id'))->first());

        return $event;
    }

    /**
     * The form shows prices in pounds; the preview, like the database, works in cents.
     *
     * @return array<string, int>
     */
    protected static function pricesFromState(mixed $prices): array
    {
        if (! is_array($prices)) {
            return [];
        }

        return collect($prices)
            ->filter(fn (mixed $pounds): bool => is_numeric($pounds))
            ->map(fn (mixed $pounds): int => (int) round(((float) $pounds) * 100))
            ->all();
    }
}
