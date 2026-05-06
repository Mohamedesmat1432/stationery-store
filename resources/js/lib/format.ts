/**
 * Formats a slug or snake_case string into a capitalized label.
 * Example: 'user_admin' -> 'User Admin'
 */
export function formatLabel(str: string): string {
    if (!str) return '';
    return str
        .split(/[_-]+/)
        .filter((word) => word.length > 0)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

/**
 * Formats a number as a currency string.
 */
export function formatCurrency(amount: number, currency = 'USD', locale = 'en-US'): string {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency,
    }).format(amount);
}
