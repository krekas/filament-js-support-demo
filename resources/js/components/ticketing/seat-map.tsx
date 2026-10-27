import type { KeyboardEvent } from 'react';
import { formatMoney } from '@/lib/format-money';
import { describeSeat, seatKey } from '@/lib/venue-layout';
import type { BookingSourceKey, Prices, VenueLayout } from '@/types/ticketing';
import { seatColors, sourceLabels, tierColor } from './seat-colors';

export type SeatMapMode = 'select' | 'readonly' | 'heatmap';

type SeatMapProps = {
    layout: VenueLayout;
    mode: SeatMapMode;
    takenSeats?: readonly string[];
    selectedSeats?: readonly string[];
    onToggleSeat?: (seatKey: string) => void;
    maxSelectable?: number | null;
    prices?: Readonly<Prices>;
    heatmap?: Readonly<Record<string, BookingSourceKey>>;
    disabled?: boolean;
};

type SeatState = 'available' | 'selected' | 'taken' | 'disabled';

type SeatAppearance = {
    fill: string;
    stroke: string;
    opacity: number;
};

const SEAT_RADIUS = 9;

export default function SeatMap({
    layout,
    mode,
    takenSeats = [],
    selectedSeats = [],
    onToggleSeat,
    maxSelectable = null,
    prices = {},
    heatmap = {},
    disabled = false,
}: SeatMapProps) {
    const taken = new Set(takenSeats);
    const selected = new Set(selectedSeats);
    const isAtLimit = maxSelectable !== null && selected.size >= maxSelectable;

    const stateFor = (key: string): SeatState => {
        if (selected.has(key)) {
            return 'selected';
        }

        if (taken.has(key)) {
            return 'taken';
        }

        if (disabled || isAtLimit) {
            return 'disabled';
        }

        return 'available';
    };

    const appearanceFor = (
        key: string,
        tier: string,
        state: SeatState,
    ): SeatAppearance => {
        if (mode === 'heatmap') {
            const source = heatmap[key];

            return source
                ? {
                      fill: seatColors.sources[source],
                      stroke: seatColors.sources[source],
                      opacity: 1,
                  }
                : {
                      fill: seatColors.unsold,
                      stroke: seatColors.unsold,
                      opacity: 0.5,
                  };
        }

        if (state === 'selected') {
            return {
                fill: seatColors.selected,
                stroke: seatColors.selectedStroke,
                opacity: 1,
            };
        }

        if (state === 'taken' || mode === 'readonly') {
            return {
                fill: seatColors.taken,
                stroke: seatColors.taken,
                opacity: 0.35,
            };
        }

        return {
            fill: tierColor(tier),
            stroke: tierColor(tier),
            opacity: state === 'disabled' ? 0.35 : 0.9,
        };
    };

    const toggle = (key: string, state: SeatState) => {
        if (!disabled && (state === 'selected' || state === 'available')) {
            onToggleSeat?.(key);
        }
    };

    const handleKeyDown = (
        event: KeyboardEvent<SVGGElement>,
        key: string,
        state: SeatState,
    ) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggle(key, state);
        }
    };

    return (
        <svg
            viewBox={`0 0 ${layout.width} ${layout.height}`}
            className="h-auto w-full text-zinc-500 select-none dark:text-zinc-400"
            role={mode === 'select' ? 'group' : 'img'}
            aria-label={
                mode === 'heatmap'
                    ? 'Seat occupancy by sales source'
                    : 'Seat map'
            }
        >
            {layout.stage && (
                <g>
                    <rect
                        x={layout.stage.x}
                        y={layout.stage.y}
                        width={layout.stage.width}
                        height={layout.stage.height}
                        rx={8}
                        fill={seatColors.stage}
                    />
                    <text
                        x={layout.stage.x + layout.stage.width / 2}
                        y={layout.stage.y + layout.stage.height / 2}
                        textAnchor="middle"
                        dominantBaseline="central"
                        fontSize={16}
                        fontWeight={600}
                        letterSpacing={4}
                        fill={seatColors.stageText}
                    >
                        {layout.stage.label.toUpperCase()}
                    </text>
                </g>
            )}

            {layout.sections.map((section) =>
                section.rows.map((row) => {
                    const firstSeat = row.seats[0];

                    return (
                        <g key={`${section.key}-${row.label}`}>
                            {firstSeat && (
                                <text
                                    x={firstSeat.x - SEAT_RADIUS * 2.2}
                                    y={firstSeat.y}
                                    textAnchor="middle"
                                    dominantBaseline="central"
                                    fontSize={11}
                                    fill="currentColor"
                                >
                                    {row.label}
                                </text>
                            )}

                            {row.seats.map((seat) => {
                                const key = seatKey(
                                    section.key,
                                    row.label,
                                    seat.number,
                                );
                                const state = stateFor(key);
                                const appearance = appearanceFor(
                                    key,
                                    section.tier,
                                    state,
                                );
                                const price = prices[section.tier];
                                const description = describeSeat({
                                    sectionName: section.name,
                                    row: row.label,
                                    number: seat.number,
                                });
                                const label =
                                    mode === 'heatmap'
                                        ? `${description}, ${heatmap[key] ? `sold ${sourceLabels[heatmap[key]].toLowerCase()}` : 'unsold'}`
                                        : price === undefined
                                          ? description
                                          : `${description}, ${formatMoney(price)}`;
                                const isInteractive =
                                    mode === 'select' &&
                                    !disabled &&
                                    (state === 'available' ||
                                        state === 'selected');

                                return (
                                    <g
                                        key={key}
                                        className={
                                            isInteractive
                                                ? 'group cursor-pointer outline-none'
                                                : undefined
                                        }
                                        {...(mode === 'select'
                                            ? {
                                                  role: 'button',
                                                  tabIndex: isInteractive
                                                      ? 0
                                                      : -1,
                                                  'aria-label': label,
                                                  'aria-pressed':
                                                      state === 'selected',
                                                  'aria-disabled':
                                                      !isInteractive,
                                                  onClick: () =>
                                                      toggle(key, state),
                                                  onKeyDown: (
                                                      event: KeyboardEvent<SVGGElement>,
                                                  ) =>
                                                      handleKeyDown(
                                                          event,
                                                          key,
                                                          state,
                                                      ),
                                              }
                                            : {})}
                                    >
                                        <title>
                                            {state === 'taken' &&
                                            mode === 'select'
                                                ? `${label} (taken)`
                                                : label}
                                        </title>
                                        {isInteractive && (
                                            <circle
                                                cx={seat.x}
                                                cy={seat.y}
                                                r={SEAT_RADIUS + 3}
                                                fill="none"
                                                stroke={seatColors.focus}
                                                strokeWidth={2.5}
                                                className="opacity-0 group-focus-visible:opacity-100"
                                            />
                                        )}
                                        <circle
                                            cx={seat.x}
                                            cy={seat.y}
                                            r={SEAT_RADIUS}
                                            fill={appearance.fill}
                                            fillOpacity={appearance.opacity}
                                            stroke={appearance.stroke}
                                            strokeWidth={
                                                state === 'selected' ? 2 : 1
                                            }
                                        />
                                    </g>
                                );
                            })}
                        </g>
                    );
                }),
            )}
        </svg>
    );
}
