<?php

namespace App\Actions;

use App\Enums\BookingSource;
use App\Models\Booking;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyncBookingSeats
{
    /**
     * Save the booking and replace its seats, pricing each one from the event's tier prices.
     *
     * @param  array<int, string>  $seatKeys
     *
     * @throws ValidationException
     */
    public function handle(Booking $booking, array $seatKeys, string $errorKey = 'seats'): Booking
    {
        $seatKeys = array_values(array_unique($seatKeys));
        $bookingSnapshot = [$booking->exists, $booking->getRawOriginal(), $booking->getAttributes()];

        try {
            return DB::transaction(function () use ($booking, $seatKeys, $errorKey, $bookingSnapshot): Booking {
                $this->restoreBooking($booking, $bookingSnapshot);

                $event = $booking->event;
                $layout = $event->venue->venueLayout();

                $unknownSeats = array_values(array_filter($seatKeys, fn (string $seatKey): bool => ! $layout->hasSeat($seatKey)));

                if ($unknownSeats !== []) {
                    throw ValidationException::withMessages([
                        $errorKey => 'These seats do not exist: '.implode(', ', $unknownSeats).'.',
                    ]);
                }

                $conflictingSeats = array_values(array_intersect($seatKeys, $event->takenSeatKeys(except: $booking)));

                if ($conflictingSeats !== []) {
                    throw $this->conflictException($errorKey, $conflictingSeats);
                }

                $isComp = $booking->source === BookingSource::Comp;
                $seats = [];

                foreach ($seatKeys as $seatKey) {
                    $price = $isComp ? 0 : $event->priceForTier($layout->tierFor($seatKey));

                    if ($price === null) {
                        throw ValidationException::withMessages([
                            $errorKey => "Seat {$seatKey} has no price for this event.",
                        ]);
                    }

                    $seats[] = ['event_id' => $event->getKey(), 'seat_key' => $seatKey, 'price' => $price];
                }

                $booking->total = array_sum(array_column($seats, 'price'));
                $booking->save();

                $booking->seats()->delete();
                $booking->seats()->createMany($seats);

                return $booking->setRelation('seats', $booking->seats()->get());
            }, attempts: 3);
        } catch (UniqueConstraintViolationException) {
            $this->restoreBooking($booking, $bookingSnapshot);

            throw $this->conflictException($errorKey);
        }
    }

    /**
     * Undo in-memory changes from a rolled-back attempt, so a retry writes everything again.
     *
     * @param  array{0: bool, 1: array<string, mixed>, 2: array<string, mixed>}  $snapshot
     */
    protected function restoreBooking(Booking $booking, array $snapshot): void
    {
        [$exists, $original, $attributes] = $snapshot;

        $booking->exists = $exists;
        $booking->wasRecentlyCreated = false;
        $booking->setRawAttributes($original, sync: true);
        $booking->setRawAttributes($attributes);
    }

    /**
     * @param  array<int, string>  $seatKeys
     */
    protected function conflictException(string $errorKey, array $seatKeys = []): ValidationException
    {
        return ValidationException::withMessages([
            $errorKey => $seatKeys === []
                ? 'Some of the selected seats were just booked by someone else. Please choose again.'
                : 'These seats are already taken: '.implode(', ', $seatKeys).'.',
        ]);
    }
}
