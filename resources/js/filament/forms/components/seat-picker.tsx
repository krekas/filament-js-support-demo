import type { JsFieldProps, JsFieldRenderer } from '@filament/forms/js-field';
import { useState } from 'react';
import { createRoot } from 'react-dom/client';
import SeatLegend from '@/components/ticketing/seat-legend';
import SeatMap from '@/components/ticketing/seat-map';
import SelectionSummary from '@/components/ticketing/selection-summary';
import type { Prices, VenueLayout } from '@/types/ticketing';

type SeatPickerConfiguration = {
    layout: VenueLayout;
    takenSeats: string[];
    prices: Prices;
    maxSelectable: number | null;
    isComp: boolean;
    eventId: number | null;
};

type SeatPickerMethods = {
    $refreshAvailability(): Promise<string[] | null>;
};

type SeatPickerProps = JsFieldProps<string[], SeatPickerConfiguration> & {
    refreshAvailability: () => Promise<string[] | null>;
};

function SeatPicker({ refreshAvailability, ...props }: SeatPickerProps) {
    const { configuration } = props;
    const value = props.value ?? [];
    const isLocked = props.isDisabled || props.isReadOnly;
    const [refreshed, setRefreshed] = useState<{
        basis: string;
        seats: readonly string[];
    } | null>(null);
    const [isRefreshing, setIsRefreshing] = useState(false);

    // A fresh server render supersedes the last manual refresh.
    const basis = `${configuration.eventId}:${configuration.takenSeats.join(',')}`;
    const takenSeats =
        refreshed?.basis === basis ? refreshed.seats : configuration.takenSeats;

    if (configuration.layout.sections.length === 0) {
        return (
            <p
                id={props.id}
                aria-describedby={props.ariaDescribedBy}
                className="rounded-lg border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400"
            >
                Select an event first.
            </p>
        );
    }

    const toggleSeat = (seatKey: string) => {
        props.onChange(
            value.includes(seatKey)
                ? value.filter((seat) => seat !== seatKey)
                : [...value, seatKey],
        );
    };

    const refresh = async () => {
        setIsRefreshing(true);

        try {
            const seats = await refreshAvailability();

            if (seats !== null) {
                setRefreshed({ basis, seats });
            }
        } finally {
            setIsRefreshing(false);
        }
    };

    return (
        <div
            id={props.id}
            role="group"
            aria-describedby={props.ariaDescribedBy}
            className="space-y-4"
        >
            <div className="flex flex-wrap items-center justify-between gap-3">
                <SeatLegend mode="select" prices={configuration.prices} />
                <button
                    type="button"
                    onClick={refresh}
                    disabled={isRefreshing}
                    className="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                >
                    {isRefreshing ? 'Refreshing…' : 'Refresh availability'}
                </button>
            </div>

            <div className="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                <SeatMap
                    layout={configuration.layout}
                    mode="select"
                    takenSeats={takenSeats}
                    selectedSeats={value}
                    onToggleSeat={toggleSeat}
                    maxSelectable={configuration.maxSelectable}
                    prices={configuration.prices}
                    disabled={isLocked}
                />
            </div>

            <SelectionSummary
                layout={configuration.layout}
                selectedSeats={value}
                prices={configuration.prices}
                isComp={configuration.isComp}
            />
        </div>
    );
}

const mountSeatPicker: JsFieldRenderer<
    string[],
    SeatPickerConfiguration,
    SeatPickerMethods
> = ({ host, props: initialProps, utilities }) => {
    const root = createRoot(host);
    let latestProps = initialProps;
    let isDestroyed = false;

    // Fetches taken seats and drops them from the selection as it is when the
    // response arrives. Responses for a removed field or a previously selected
    // event are ignored.
    const refreshAvailability = async (): Promise<string[] | null> => {
        const eventId = latestProps.configuration.eventId;
        const seats = await utilities.$refreshAvailability();

        if (
            seats === null ||
            isDestroyed ||
            latestProps.configuration.eventId !== eventId
        ) {
            return null;
        }

        const selection = latestProps.value ?? [];
        const isLocked = latestProps.isDisabled || latestProps.isReadOnly;

        if (!isLocked && selection.some((seat) => seats.includes(seat))) {
            latestProps.onChange(
                selection.filter((seat) => !seats.includes(seat)),
            );
        }

        return seats;
    };

    const update = (props: typeof initialProps) => {
        latestProps = { ...initialProps, ...props };

        root.render(
            <SeatPicker
                {...latestProps}
                refreshAvailability={refreshAvailability}
            />,
        );
    };

    update(initialProps);

    return {
        update,
        destroy: () => {
            isDestroyed = true;
            root.unmount();
        },
    };
};

export default mountSeatPicker;
