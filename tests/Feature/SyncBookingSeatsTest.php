<?php

use App\Actions\SyncBookingSeats;
use App\Enums\BookingSource;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Validation\ValidationException;

test('seats are priced by their tier', function () {
    $booking = Booking::factory()->source(BookingSource::BoxOffice)->create();

    app(SyncBookingSeats::class)->handle($booking, ['stalls-A-1', 'balcony-A-1']);

    expect($booking->refresh()->total)->toBe(14000);
    $this->assertDatabaseHas('booking_seats', ['booking_id' => $booking->id, 'seat_key' => 'stalls-A-1', 'price' => 8500]);
    $this->assertDatabaseHas('booking_seats', ['booking_id' => $booking->id, 'seat_key' => 'balcony-A-1', 'price' => 5500]);
});

test('comp bookings are priced at zero', function () {
    $booking = Booking::factory()->source(BookingSource::Comp)->create();

    app(SyncBookingSeats::class)->handle($booking, ['stalls-A-1', 'balcony-A-1']);

    expect($booking->refresh()->total)->toBe(0);
    $this->assertDatabaseMissing('booking_seats', ['booking_id' => $booking->id, 'price' => 8500]);
    $this->assertDatabaseCount('booking_seats', 2);
});

test('a seat taken by another booking is rejected', function () {
    $event = Event::factory()->create();
    app(SyncBookingSeats::class)->handle(Booking::factory()->for($event)->create(), ['stalls-A-1']);
    $booking = Booking::factory()->for($event)->create();

    expect(fn () => app(SyncBookingSeats::class)->handle($booking, ['stalls-A-1', 'stalls-A-2']))
        ->toThrow(ValidationException::class, 'These seats are already taken: stalls-A-1.');

    expect($booking->seats()->count())->toBe(0);
});

test('an unknown seat key is rejected', function () {
    $booking = Booking::factory()->create();

    expect(fn () => app(SyncBookingSeats::class)->handle($booking, ['stalls-Z-99']))
        ->toThrow(ValidationException::class, 'These seats do not exist: stalls-Z-99.');

    $this->assertDatabaseCount('booking_seats', 0);
});

test('a booking keeps its own seats when it is updated', function () {
    $booking = Booking::factory()->source(BookingSource::BoxOffice)->create();
    app(SyncBookingSeats::class)->handle($booking, ['stalls-A-1']);

    app(SyncBookingSeats::class)->handle($booking, ['stalls-A-1', 'stalls-A-2']);

    expect($booking->seats()->pluck('seat_key')->sort()->values()->all())->toBe(['stalls-A-1', 'stalls-A-2'])
        ->and($booking->refresh()->total)->toBe(17000);
});
