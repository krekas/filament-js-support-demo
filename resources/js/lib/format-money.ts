const formatter = new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency: 'GBP',
});

export function formatMoney(cents: number): string {
    return formatter.format(cents / 100);
}
