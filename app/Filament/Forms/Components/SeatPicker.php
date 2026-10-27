<?php

namespace App\Filament\Forms\Components;

use App\Models\Booking;
use App\Models\Event;
use Closure;
use Filament\Forms\Components\Concerns\HasJsRenderer;
use Filament\Forms\Components\Field;
use Filament\Support\Components\Attributes\Exposed;
use Illuminate\Support\Facades\Vite;
use Livewire\Attributes\Renderless;

class SeatPicker extends Field
{
    use HasJsRenderer;

    protected Event|Closure|null $event = null;

    protected int|Closure|null $maxSeats = null;

    protected bool|Closure $isComp = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(static function (SeatPicker $component, mixed $state): void {
            if (! is_array($state)) {
                $component->state([]);
            }
        });

        $this->rule('array');

        $this->rule(
            static fn (SeatPicker $component): string => 'max:'.$component->getMaxSeats(),
            static fn (SeatPicker $component): bool => $component->getMaxSeats() !== null,
        );
    }

    public function getRenderer(): string
    {
        return Vite::asset('resources/js/filament/forms/components/seat-picker.tsx');
    }

    public function event(Event|Closure|null $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getEvent(): ?Event
    {
        $event = $this->evaluate($this->event);

        return $event instanceof Event ? $event : null;
    }

    public function maxSeats(int|Closure|null $maxSeats): static
    {
        $this->maxSeats = $maxSeats;

        return $this;
    }

    public function getMaxSeats(): ?int
    {
        return $this->evaluate($this->maxSeats);
    }

    public function comp(bool|Closure $condition = true): static
    {
        $this->isComp = $condition;

        return $this;
    }

    public function isComp(): bool
    {
        return (bool) $this->evaluate($this->isComp);
    }

    /**
     * Seats sold to other bookings, excluding the booking being edited.
     *
     * @return list<string>
     */
    public function getTakenSeatKeys(?Event $event = null): array
    {
        $event ??= $this->getEvent();
        $record = $this->getRecord();

        return $event?->takenSeatKeys(except: $record instanceof Booking ? $record : null) ?? [];
    }

    /**
     * Lets box-office staff pick up seats sold online since the form was rendered, without re-rendering it.
     *
     * @return list<string>
     */
    #[Exposed]
    #[Renderless]
    public function refreshAvailability(): array
    {
        return $this->getTakenSeatKeys();
    }

    /**
     * @return array{layout: array<string, mixed>, takenSeats: list<string>, prices: array<string, int>, maxSelectable: int|null, isComp: bool, eventId: int|null}
     */
    public function getRendererConfiguration(): array
    {
        $event = $this->getEvent();

        return [
            'layout' => $event?->venue->layout ?? ['width' => 0, 'height' => 0, 'stage' => null, 'sections' => []],
            'takenSeats' => $this->getTakenSeatKeys($event),
            'prices' => $event->prices ?? [],
            'maxSelectable' => $this->getMaxSeats(),
            'isComp' => $this->isComp(),
            'eventId' => $event?->id,
        ];
    }
}
