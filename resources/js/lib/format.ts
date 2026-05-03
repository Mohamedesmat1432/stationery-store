/**
 * Formats a slug or snake_case string into a capitalized label.
 * Example: 'user_admin' -> 'User Admin'
 */
export function formatLabel(str: string): string {
    if (!str) return '';
    return str
        .split(/[_-]/)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}
