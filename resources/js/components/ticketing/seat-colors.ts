import type { BookingSourceKey } from '@/types/ticketing';

/**
 * Seat colours are plain hex values applied through SVG `fill`/`stroke`
 * attributes, so they render the same with or without compiled Tailwind CSS.
 */
export const seatColors = {
    tiers: {
        premium: '#8b5cf6',
        standard: '#0ea5e9',
    } as Record<string, string>,
    fallbackTier: '#64748b',
    selected: '#10b981',
    selectedStroke: '#047857',
    taken: '#a1a1aa',
    focus: '#f59e0b',
    stage: '#52525b',
    stageText: '#fafafa',
    unsold: '#d4d4d8',
    sources: {
        online: '#3b82f6',
        box_office: '#10b981',
        comp: '#f59e0b',
    } satisfies Record<BookingSourceKey, string>,
};

export const sourceLabels: Record<BookingSourceKey, string> = {
    online: 'Online',
    box_office: 'Box office',
    comp: 'Comp',
};

export function tierColor(tier: string): string {
    return seatColors.tiers[tier] ?? seatColors.fallbackTier;
}
