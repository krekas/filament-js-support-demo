<?php

use App\Actions\SyncBookingSeats;
use App\Enums\BookingSource;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;

test('creating a booking in the admin persists its seats', function () {
    $this->actingAs(User::factory()->create());
    $event = Event::factory()->create();

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'event_id' => $event->id,
            'source' => BookingSource::BoxOffice->value,
            'customer_name' => 'Box Office Guest',
            'customer_email' => 'guest@example.com',
            'seats' => ['stalls-A-1', 'balcony-A-1'],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $booking = Booking::query()->sole();
    expect($booking->seats()->pluck('seat_key')->sort()->values()->all())->toBe(['balcony-A-1', 'stalls-A-1'])
        ->and($booking->total)->toBe(14000);
});

test('booking a taken seat in the admin errors on the seats field', function () {
    $this->actingAs(User::factory()->create());
    $event = Event::factory()->create();
    app(SyncBookingSeats::class)->handle(Booking::factory()->for($event)->create(), ['stalls-A-1']);

    Livewire::test(CreateBooking::class)
        ->fillForm([
            'event_id' => $event->id,
            'source' => BookingSource::BoxOffice->value,
            'customer_name' => 'Box Office Guest',
            'customer_email' => 'guest@example.com',
            'seats' => ['stalls-A-1'],
        ])
        ->call('create')
        ->assertHasErrors(['data.seats']);

    $this->assertDatabaseCount('bookings', 1);
});

test('the seat picker does not mark the edited booking\'s own seats as taken', function () {
    $this->actingAs(User::factory()->create());
    $event = Event::factory()->create();
    app(SyncBookingSeats::class)->handle(Booking::factory()->for($event)->create(), ['stalls-A-1']);
    $booking = Booking::factory()->for($event)->source(BookingSource::BoxOffice)->create();
    app(SyncBookingSeats::class)->handle($booking, ['stalls-A-2']);

    $component = Livewire::test(EditBooking::class, ['record' => $booking->id]);

    $configuration = $component->instance()->form->getComponent('seats')->getRendererConfiguration();
    expect($configuration['takenSeats'])->toBe(['stalls-A-1'])
        ->and($component->get('data.seats'))->toBe(['stalls-A-2']);
});
