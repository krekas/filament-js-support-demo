import type {
    JsWidgetProps,
    JsWidgetRenderer,
} from '@filament/widgets/js-widget';
import { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { seatColors, sourceLabels } from '@/components/ticketing/seat-colors';
import SeatLegend from '@/components/ticketing/seat-legend';
import SeatMap from '@/components/ticketing/seat-map';
import { formatMoney } from '@/lib/format-money';
import type { BookingSourceKey, VenueLayout } from '@/types/ticketing';

type SectionStats = {
    key: string;
    name: string;
    sold: number;
    capacity: number;
    revenue: number;
};

type SeatOccupancyConfiguration = {
    eventId: number | null;
    eventName: string | null;
    layout: VenueLayout | null;
    heatmap: Record<string, BookingSourceKey>;
    stats: {
        sold: number;
        capacity: number;
        revenue: number;
        sections: SectionStats[];
        sources: Record<BookingSourceKey, number>;
    } | null;
};

type SeatOccupancyMethods = Record<never, never>;

type SeatOccupancyProps = JsWidgetProps<SeatOccupancyConfiguration> & {
    openBookingForm: () => Promise<unknown>;
};

function percentage(sold: number, capacity: number): string {
    return capacity === 0 ? '0%' : `${Math.round((sold / capacity) * 100)}%`;
}

function Stat({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg bg-zinc-50 px-4 py-3 dark:bg-white/5">
            <dt className="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                {label}
            </dt>
            <dd className="mt-1 text-lg font-semibold text-zinc-950 tabular-nums dark:text-white">
                {value}
            </dd>
        </div>
    );
}

function SeatOccupancy({ configuration, openBookingForm }: SeatOccupancyProps) {
    const { layout, stats } = configuration;
    const [isOpening, setIsOpening] = useState(false);

    const openBoxOffice = async () => {
        setIsOpening(true);

        try {
            await openBookingForm();
        } finally {
            setIsOpening(false);
        }
    };

    return (
        <section>
            <header className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 className="text-base font-semibold text-zinc-950 dark:text-white">
                        Seat occupancy
                    </h2>
                    <p className="text-sm text-zinc-500 dark:text-zinc-400">
                        {configuration.eventName ?? 'No event selected'}
                    </p>
                </div>
                {configuration.eventId !== null && (
                    <button
                        type="button"
                        onClick={openBoxOffice}
                        disabled={isOpening}
                        className="rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-400 disabled:opacity-50"
                    >
                        Open in box office
                    </button>
                )}
            </header>

            {layout === null || stats === null ? (
                <p className="mt-6 text-sm text-zinc-500 dark:text-zinc-400">
                    Choose an event in the dashboard filter to see its seat map.
                </p>
            ) : (
                <div className="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
                    <div className="space-y-4">
                        <SeatMap
                            layout={layout}
                            mode="heatmap"
                            heatmap={configuration.heatmap}
                        />
                        <SeatLegend mode="heatmap" />
                    </div>

                    <div className="space-y-4">
                        <dl className="grid grid-cols-2 gap-3">
                            <Stat
                                label="Sold"
                                value={`${stats.sold} / ${stats.capacity}`}
                            />
                            <Stat
                                label="Occupancy"
                                value={percentage(stats.sold, stats.capacity)}
                            />
                            <Stat
                                label="Revenue"
                                value={formatMoney(stats.revenue)}
                            />
                            <Stat
                                label="Unsold"
                                value={String(stats.capacity - stats.sold)}
                            />
                        </dl>

                        <ul className="space-y-2 text-sm">
                            {(
                                Object.keys(sourceLabels) as BookingSourceKey[]
                            ).map((source) => (
                                <li
                                    key={source}
                                    className="flex items-center justify-between gap-3 text-zinc-700 dark:text-zinc-300"
                                >
                                    <span className="flex items-center gap-2">
                                        <span
                                            className="size-2.5 rounded-full"
                                            style={{
                                                backgroundColor:
                                                    seatColors.sources[source],
                                            }}
                                        />
                                        {sourceLabels[source]}
                                    </span>
                                    <span className="tabular-nums">
                                        {stats.sources[source] ?? 0} seats
                                    </span>
                                </li>
                            ))}
                        </ul>

                        <table className="w-full text-left text-sm">
                            <thead className="text-xs text-zinc-500 dark:text-zinc-400">
                                <tr>
                                    <th className="py-1 font-medium">
                                        Section
                                    </th>
                                    <th className="py-1 text-right font-medium">
                                        Sold
                                    </th>
                                    <th className="py-1 text-right font-medium">
                                        Revenue
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-zinc-100 text-zinc-700 dark:divide-white/5 dark:text-zinc-300">
                                {stats.sections.map((section) => (
                                    <tr key={section.key}>
                                        <td className="py-1.5">
                                            {section.name}
                                        </td>
                                        <td className="py-1.5 text-right tabular-nums">
                                            {section.sold} / {section.capacity}
                                        </td>
                                        <td className="py-1.5 text-right tabular-nums">
                                            {formatMoney(section.revenue)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </section>
    );
}

const mountSeatOccupancy: JsWidgetRenderer<
    SeatOccupancyConfiguration,
    SeatOccupancyMethods
> = ({ host, props: initialProps, utilities }) => {
    const root = createRoot(host);
    const openBookingForm = () => utilities.$wire.$call('openBookingForm');
    const update = (props: typeof initialProps) =>
        root.render(
            <SeatOccupancy {...props} openBookingForm={openBookingForm} />,
        );

    update(initialProps);

    return { update, destroy: () => root.unmount() };
};

export default mountSeatOccupancy;
