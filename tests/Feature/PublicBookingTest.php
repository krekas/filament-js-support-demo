<?php

use App\Actions\SyncBookingSeats;
use App\Enums\BookingSource;
use App\Enums\EventStatus;
use App\Models\Booking;
use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

test('the home page lists on-sale events only', function () {
    $onSale = Event::factory()->create(['name' => 'Live Tonight']);
    Event::factory()->status(EventStatus::Draft)->create();
    Event::factory()->status(EventStatus::Cancelled)->create();

    $response = $this->get(route('home'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('home')
        ->has('events', 1)
        ->where('events.0.slug', $onSale->slug)
        ->where('events.0.fromPrice', 5500));
});

test('a valid checkout creates an online booking and redirects to its confirmation', function () {
    $event = Event::factory()->create();

    $response = $this->post(route('events.bookings.store', $event), [
        'customer_name' => 'Ada Lovelace',
        'customer_email' => 'ada@example.com',
        'seats' => ['stalls-A-1', 'stalls-A-2'],
    ]);

    $booking = Booking::query()->sole();
    $response->assertRedirect(route('bookings.show', $booking));
    expect($booking->source)->toBe(BookingSource::Online)
        ->and($booking->total)->toBe(17000)
        ->and($booking->customer_email)->toBe('ada@example.com');
    $this->assertDatabaseCount('booking_seats', 2);
});

test('checking out a taken seat returns a seats validation error', function () {
    $event = Event::factory()->create();
    app(SyncBookingSeats::class)->handle(Booking::factory()->for($event)->create(), ['stalls-A-1']);

    $response = $this->from(route('events.show', $event))->post(route('events.bookings.store', $event), [
        'customer_name' => 'Ada Lovelace',
        'customer_email' => 'ada@example.com',
        'seats' => ['stalls-A-1'],
    ]);

    $response->assertRedirect(route('events.show', $event))->assertSessionHasErrors('seats');
    $this->assertDatabaseCount('bookings', 1);
});
