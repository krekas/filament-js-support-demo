import { formatMoney } from '@/lib/format-money';
import { describeSeat, seatDetailsMap } from '@/lib/venue-layout';
import type { Prices, VenueLayout } from '@/types/ticketing';

type SelectionSummaryProps = {
    layout: VenueLayout;
    selectedSeats: readonly string[];
    prices?: Readonly<Prices>;
    isComp?: boolean;
    /** Prices already charged per seat key, which win over the current tier prices. */
    seatPrices?: Readonly<Record<string, number>>;
    emptyMessage?: string;
};

export default function SelectionSummary({
    layout,
    selectedSeats,
    prices = {},
    isComp = false,
    seatPrices = {},
    emptyMessage = 'No seats selected yet. Pick seats on the map.',
}: SelectionSummaryProps) {
    const details = seatDetailsMap(layout, prices);
    const seats = selectedSeats.map((key) => {
        const seat = details.get(key);

        return {
            key,
            label: seat ? describeSeat(seat) : key,
            price: seatPrices[key] ?? (isComp ? 0 : (seat?.price ?? 0)),
        };
    });
    const total = seats.reduce((sum, seat) => sum + seat.price, 0);

    return (
        <div className="rounded-lg border border-zinc-200 bg-white p-4 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
            {seats.length === 0 ? (
                <p className="text-zinc-500 dark:text-zinc-400">
                    {emptyMessage}
                </p>
            ) : (
                <>
                    <ul className="divide-y divide-zinc-100 dark:divide-zinc-800">
                        {seats.map((seat) => (
                            <li
                                key={seat.key}
                                className="flex items-center justify-between gap-4 py-1.5"
                            >
                                <span>{seat.label}</span>
                                <span className="text-zinc-500 tabular-nums dark:text-zinc-400">
                                    {formatMoney(seat.price)}
                                </span>
                            </li>
                        ))}
                    </ul>
                    <div className="mt-3 flex items-center justify-between border-t border-zinc-200 pt-3 font-semibold dark:border-zinc-700">
                        <span>
                            Total · {seats.length}{' '}
                            {seats.length === 1 ? 'seat' : 'seats'}
                            {isComp && (
                                <span className="ml-2 rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                                    Comp
                                </span>
                            )}
                        </span>
                        <span className="tabular-nums">
                            {formatMoney(total)}
                        </span>
                    </div>
                </>
            )}
        </div>
    );
}
