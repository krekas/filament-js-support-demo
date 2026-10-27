export type Seat = {
    number: number;
    x: number;
    y: number;
};

export type Row = {
    label: string;
    seats: readonly Seat[];
};

export type Section = {
    key: string;
    name: string;
    tier: string;
    rows: readonly Row[];
};

export type Stage = {
    x: number;
    y: number;
    width: number;
    height: number;
    label: string;
};

export type VenueLayout = {
    width: number;
    height: number;
    stage: Stage | null;
    sections: readonly Section[];
};

/** Tier key → price in cents, e.g. `{ premium: 8500, standard: 5500 }`. */
export type Prices = Record<string, number>;

export type BookingSourceKey = 'online' | 'box_office' | 'comp';

export type EventStatusKey = 'draft' | 'on_sale' | 'sold_out' | 'cancelled';

export type EventSummary = {
    id: number | null;
    name: string;
    slug: string | null;
    venueName: string | null;
    venueCity: string | null;
    startsAt: string | null;
    /** The timezone the event's start time is shown in, so the server and browser agree. */
    timezone: string;
    posterUrl: string | null;
    fromPrice: number | null;
    status: EventStatusKey;
    statusLabel: string;
};
