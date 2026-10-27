<?php

use App\Actions\SyncBookingSeats;
use App\Enums\BookingSource;
use App\Filament\Widgets\SeatOccupancy;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;

test('the widget reflects the filtered event\'s sold seats and sources', function () {
    $this->actingAs(User::factory()->create());
    $event = Event::factory()->create(['name' => 'Filtered Event']);
    $otherEvent = Event::factory()->create();
    app(SyncBookingSeats::class)->handle(Booking::factory()->for($event)->source(BookingSource::Online)->create(), ['stalls-A-1', 'stalls-A-2']);
    app(SyncBookingSeats::class)->handle(Booking::factory()->for($event)->source(BookingSource::Comp)->create(), ['balcony-A-1']);
    app(SyncBookingSeats::class)->handle(Booking::factory()->for($otherEvent)->create(), ['stalls-B-1']);

    $component = Livewire::test(SeatOccupancy::class, ['pageFilters' => ['event_id' => $event->id]]);

    $configuration = $component->instance()->getRendererConfiguration();
    expect($configuration['eventName'])->toBe('Filtered Event')
        ->and($configuration['heatmap'])->toEqual([
            'stalls-A-1' => 'online',
            'stalls-A-2' => 'online',
            'balcony-A-1' => 'comp',
        ])
        ->and($configuration['stats']['sold'])->toBe(3)
        ->and($configuration['stats']['capacity'])->toBe(24)
        ->and($configuration['stats']['revenue'])->toBe(17000)
        ->and($configuration['stats']['sources'])->toBe(['online' => 2, 'box_office' => 0, 'comp' => 1])
        ->and($configuration['stats']['sections'][0])->toMatchArray(['key' => 'stalls', 'sold' => 2, 'capacity' => 12, 'revenue' => 17000]);
});
