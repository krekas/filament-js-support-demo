import { formatMoney } from '@/lib/format-money';
import type { BookingSourceKey, Prices } from '@/types/ticketing';
import { seatColors, sourceLabels, tierColor } from './seat-colors';
import type { SeatMapMode } from './seat-map';

type SeatLegendProps = {
    mode: SeatMapMode;
    prices?: Readonly<Prices>;
};

type LegendItem = {
    label: string;
    color: string;
    opacity?: number;
};

const tierLabels: Record<string, string> = {
    premium: 'Premium',
    standard: 'Standard',
};

function legendItems(
    mode: SeatMapMode,
    prices: Readonly<Prices>,
): LegendItem[] {
    if (mode === 'heatmap') {
        return [
            ...(Object.keys(sourceLabels) as BookingSourceKey[]).map(
                (source) => ({
                    label: sourceLabels[source],
                    color: seatColors.sources[source],
                }),
            ),
            { label: 'Unsold', color: seatColors.unsold, opacity: 0.5 },
        ];
    }

    if (mode === 'readonly') {
        return [
            { label: 'Your seats', color: seatColors.selected },
            { label: 'Other seats', color: seatColors.taken, opacity: 0.35 },
        ];
    }

    return [
        ...Object.entries(prices).map(([tier, price]) => ({
            label: `${tierLabels[tier] ?? tier} · ${formatMoney(price)}`,
            color: tierColor(tier),
            opacity: 0.9,
        })),
        { label: 'Selected', color: seatColors.selected },
        { label: 'Taken', color: seatColors.taken, opacity: 0.35 },
    ];
}

export default function SeatLegend({ mode, prices = {} }: SeatLegendProps) {
    return (
        <ul className="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-zinc-600 dark:text-zinc-300">
            {legendItems(mode, prices).map((item) => (
                <li key={item.label} className="flex items-center gap-2">
                    <svg viewBox="0 0 12 12" className="size-3" aria-hidden>
                        <circle
                            cx={6}
                            cy={6}
                            r={5.5}
                            fill={item.color}
                            fillOpacity={item.opacity ?? 1}
                            stroke={item.color}
                        />
                    </svg>
                    {item.label}
                </li>
            ))}
        </ul>
    );
}
