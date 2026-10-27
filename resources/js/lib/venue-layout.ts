import type { Prices, VenueLayout } from '@/types/ticketing';

export type SeatDetails = {
    key: string;
    sectionName: string;
    tier: string;
    row: string;
    number: number;
    price: number | null;
};

export function seatKey(section: string, row: string, number: number): string {
    return `${section}-${row}-${number}`;
}

export function seatDetailsMap(
    layout: VenueLayout,
    prices: Prices = {},
): Map<string, SeatDetails> {
    const details = new Map<string, SeatDetails>();

    for (const section of layout.sections) {
        for (const row of section.rows) {
            for (const seat of row.seats) {
                const key = seatKey(section.key, row.label, seat.number);

                details.set(key, {
                    key,
                    sectionName: section.name,
                    tier: section.tier,
                    row: row.label,
                    number: seat.number,
                    price: prices[section.tier] ?? null,
                });
            }
        }
    }

    return details;
}

export function describeSeat(
    seat: Pick<SeatDetails, 'sectionName' | 'row' | 'number'>,
): string {
    return `${seat.sectionName} row ${seat.row} seat ${seat.number}`;
}
