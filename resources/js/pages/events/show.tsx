import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import BookingController from '@/actions/App/Http/Controllers/BookingController';
import InputError from '@/components/input-error';
import EventCard from '@/components/ticketing/event-card';
import SeatLegend from '@/components/ticketing/seat-legend';
import SeatMap from '@/components/ticketing/seat-map';
import SelectionSummary from '@/components/ticketing/selection-summary';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EventSummary, Prices, VenueLayout } from '@/types';

type EventShowProps = {
    event: EventSummary;
    description: string | null;
    layout: VenueLayout;
    prices: Prices;
    takenSeats: string[];
    maxSeats: number;
    isOnSale: boolean;
};

export default function EventShow({
    event,
    description,
    layout,
    prices,
    takenSeats,
    maxSeats,
    isOnSale,
}: EventShowProps) {
    const [pickedSeats, setPickedSeats] = useState<string[]>([]);

    // Seats someone else bought since they were picked drop out of the selection.
    const selectedSeats = pickedSeats.filter(
        (seat) => !takenSeats.includes(seat),
    );

    const toggleSeat = (seatKey: string) => {
        setPickedSeats((current) =>
            current.includes(seatKey)
                ? current.filter((seat) => seat !== seatKey)
                : [...current, seatKey],
        );
    };

    return (
        <>
            <Head title={event.name} />

            <Form
                {...BookingController.store.form(event.slug ?? '')}
                options={{ preserveScroll: true }}
                className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]"
            >
                {({ errors, processing }) => {
                    const seatError = Object.entries(errors).find(([key]) =>
                        key.startsWith('seats'),
                    )?.[1];

                    return (
                        <>
                            <div className="space-y-6">
                                <div>
                                    <h1 className="text-3xl font-semibold tracking-tight">
                                        {event.name}
                                    </h1>
                                    {description && (
                                        <p className="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">
                                            {description}
                                        </p>
                                    )}
                                </div>

                                <InputError
                                    message={seatError}
                                    role="alert"
                                    className="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950"
                                />

                                <div className="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                                    <SeatMap
                                        layout={layout}
                                        mode="select"
                                        takenSeats={takenSeats}
                                        selectedSeats={selectedSeats}
                                        onToggleSeat={toggleSeat}
                                        maxSelectable={maxSeats}
                                        prices={prices}
                                        disabled={!isOnSale || processing}
                                    />
                                </div>

                                <SeatLegend mode="select" prices={prices} />

                                {selectedSeats.map((seat) => (
                                    <input
                                        key={seat}
                                        type="hidden"
                                        name="seats[]"
                                        value={seat}
                                    />
                                ))}
                            </div>

                            <aside className="space-y-6">
                                <EventCard event={event} />

                                <div className="space-y-3">
                                    <h2 className="text-lg font-semibold">
                                        Your seats
                                    </h2>
                                    <SelectionSummary
                                        layout={layout}
                                        selectedSeats={selectedSeats}
                                        prices={prices}
                                    />
                                    {selectedSeats.length >= maxSeats && (
                                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                                            You can book up to {maxSeats} seats
                                            at a time.
                                        </p>
                                    )}
                                </div>

                                {isOnSale ? (
                                    <div className="space-y-4">
                                        <h2 className="text-lg font-semibold">
                                            Checkout
                                        </h2>

                                        <div className="grid gap-2">
                                            <Label htmlFor="customer_name">
                                                Name
                                            </Label>
                                            <Input
                                                id="customer_name"
                                                name="customer_name"
                                                required
                                                autoComplete="name"
                                                placeholder="Full name"
                                            />
                                            <InputError
                                                message={errors.customer_name}
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="customer_email">
                                                Email
                                            </Label>
                                            <Input
                                                id="customer_email"
                                                name="customer_email"
                                                type="email"
                                                required
                                                autoComplete="email"
                                                placeholder="you@example.com"
                                            />
                                            <InputError
                                                message={errors.customer_email}
                                            />
                                        </div>

                                        <Button
                                            type="submit"
                                            className="w-full"
                                            disabled={
                                                processing ||
                                                selectedSeats.length === 0
                                            }
                                        >
                                            {processing
                                                ? 'Booking…'
                                                : 'Book seats'}
                                        </Button>
                                        <p className="text-xs text-zinc-500 dark:text-zinc-400">
                                            Demo checkout: no payment is taken.
                                        </p>
                                    </div>
                                ) : (
                                    <p className="rounded-lg border border-zinc-200 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
                                        Tickets for this event are not on sale.
                                    </p>
                                )}
                            </aside>
                        </>
                    );
                }}
            </Form>
        </>
    );
}
