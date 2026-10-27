import { Head } from '@inertiajs/react';
import EventCard from '@/components/ticketing/event-card';
import { show } from '@/routes/events';
import type { EventSummary } from '@/types';

type HomeProps = {
    events: EventSummary[];
};

export default function Home({ events }: HomeProps) {
    return (
        <>
            <Head title="What's on" />

            <section className="mb-10 max-w-2xl">
                <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">
                    Pick your night out, then pick your seat.
                </h1>
                <p className="mt-3 text-zinc-600 dark:text-zinc-400">
                    Concerts, theatre and comedy with assigned seating. Choose
                    exactly where you sit on the live seat map.
                </p>
            </section>

            {events.length === 0 ? (
                <p className="text-zinc-500 dark:text-zinc-400">
                    No events are on sale right now. Check back soon.
                </p>
            ) : (
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {events.map((event) => (
                        <EventCard
                            key={event.id}
                            event={event}
                            href={show(event.slug ?? '').url}
                        />
                    ))}
                </div>
            )}
        </>
    );
}
