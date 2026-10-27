import { Head, Link } from '@inertiajs/react';
import EventCard from '@/components/ticketing/event-card';
import SeatLegend from '@/components/ticketing/seat-legend';
import SeatMap from '@/components/ticketing/seat-map';
import SelectionSummary from '@/components/ticketing/selection-summary';
import { formatMoney } from '@/lib/format-money';
import { home } from '@/routes';
import { show } from '@/routes/events';
import type {
    BookingSourceKey,
    EventSummary,
    Prices,
    VenueLayout,
} from '@/types';

type BookingShowProps = {
    booking: {
        reference: string;
        customerName: string;
        customerEmail: string;
        source: BookingSourceKey;
        total: number;
        seats: string[];
        seatPrices: Record<string, number>;
    };
    event: EventSummary;
    layout: VenueLayout;
    prices: Prices;
};

export default function BookingShow({
    booking,
    event,
    layout,
    prices,
}: BookingShowProps) {
    return (
        <>
            <Head title={`Booking ${booking.reference}`} />

            <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div className="space-y-6">
                    <div>
                        <p className="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                            Booking confirmed
                        </p>
                        <h1 className="mt-1 text-3xl font-semibold tracking-tight">
                            You're going to {event.name}!
                        </h1>
                        <p className="mt-2 text-zinc-600 dark:text-zinc-400">
                            Thanks, {booking.customerName}. Your booking
                            reference is{' '}
                            <span className="font-mono font-semibold text-zinc-900 dark:text-zinc-100">
                                {booking.reference}
                            </span>
                            . Show it at the door; no tickets are emailed in
                            this demo.
                        </p>
                    </div>

                    <div className="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <SeatMap
                            layout={layout}
                            mode="readonly"
                            selectedSeats={booking.seats}
                            prices={prices}
                        />
                    </div>

                    <SeatLegend mode="readonly" />
                </div>

                <aside className="space-y-6">
                    <EventCard
                        event={event}
                        href={event.slug ? show(event.slug).url : undefined}
                    />

                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold">Your seats</h2>
                        <SelectionSummary
                            layout={layout}
                            selectedSeats={booking.seats}
                            prices={prices}
                            isComp={booking.source === 'comp'}
                            seatPrices={booking.seatPrices}
                        />
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            Paid {formatMoney(booking.total)}
                        </p>
                    </div>

                    <Link
                        href={home()}
                        className="inline-block text-sm font-medium text-zinc-700 underline underline-offset-4 dark:text-zinc-300"
                    >
                        Browse more events
                    </Link>
                </aside>
            </div>
        </>
    );
}
