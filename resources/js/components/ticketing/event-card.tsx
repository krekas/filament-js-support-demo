import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { formatMoney } from '@/lib/format-money';
import type { EventStatusKey, EventSummary } from '@/types/ticketing';

type EventCardProps = {
    event: EventSummary;
    href?: string;
};

const statusClasses: Record<EventStatusKey, string> = {
    draft: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    on_sale:
        'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
    sold_out:
        'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-300',
};

function formatStartsAt(startsAt: string | null, timeZone: string): string {
    const date = startsAt ? new Date(startsAt) : null;

    if (!date || Number.isNaN(date.getTime())) {
        return 'Date to be announced';
    }

    try {
        return new Intl.DateTimeFormat('en-GB', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone,
        }).format(date);
    } catch {
        return date.toLocaleString('en-GB');
    }
}

function CardFrame({ href, children }: { href?: string; children: ReactNode }) {
    const className =
        'group block overflow-hidden rounded-xl border border-zinc-200 bg-white text-left text-zinc-900 shadow-sm transition dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100';

    if (href) {
        return (
            <Link
                href={href}
                className={`${className} hover:-translate-y-0.5 hover:shadow-md focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:outline-none`}
            >
                {children}
            </Link>
        );
    }

    return <article className={className}>{children}</article>;
}

export default function EventCard({ event, href }: EventCardProps) {
    const venue = [event.venueName, event.venueCity].filter(Boolean).join(', ');

    return (
        <CardFrame href={href}>
            <div className="relative aspect-[16/10] bg-zinc-100 dark:bg-zinc-800">
                {event.posterUrl ? (
                    <img
                        src={event.posterUrl}
                        alt=""
                        loading="lazy"
                        className="size-full object-cover"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center text-sm text-zinc-400">
                        No poster yet
                    </div>
                )}
                <span
                    className={`absolute top-3 left-3 rounded-full px-2.5 py-1 text-xs font-semibold ${statusClasses[event.status]}`}
                >
                    {event.statusLabel}
                </span>
            </div>
            <div className="space-y-1 p-4">
                <h3 className="text-lg leading-snug font-semibold">
                    {event.name || 'Untitled event'}
                </h3>
                <p className="text-sm text-zinc-500 dark:text-zinc-400">
                    {formatStartsAt(event.startsAt, event.timezone)}
                </p>
                {venue && (
                    <p className="text-sm text-zinc-500 dark:text-zinc-400">
                        {venue}
                    </p>
                )}
                <p className="pt-2 text-sm font-medium">
                    {event.fromPrice === null
                        ? 'Prices to be announced'
                        : `From ${formatMoney(event.fromPrice)}`}
                </p>
            </div>
        </CardFrame>
    );
}
