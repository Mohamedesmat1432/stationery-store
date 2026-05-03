import type { ComputedRef, Ref } from 'vue';
import { computed, onMounted, ref } from 'vue';
import type { AccentColor, Appearance, ResolvedAppearance } from '@/types';

export type { AccentColor, Appearance, ResolvedAppearance };

export const ACCENT_COLORS: Record<AccentColor, { light: string; dark: string; ring: string }> = {
    zinc: { light: 'hsl(240 5.9% 10%)', dark: 'hsl(0 0% 98%)', ring: 'hsl(240 4.9% 83.9%)' },
    slate: { light: 'hsl(215.4 16.3% 46.9%)', dark: 'hsl(215.4 25% 60%)', ring: 'hsl(215.4 16.3% 46.9%)' },
    stone: { light: 'hsl(25 5.3% 44.7%)', dark: 'hsl(25 15% 60%)', ring: 'hsl(25 5.3% 44.7%)' },
    gray: { light: 'hsl(220 8.9% 46.1%)', dark: 'hsl(220 15% 60%)', ring: 'hsl(220 8.9% 46.1%)' },
    neutral: { light: 'hsl(0 0% 45.1%)', dark: 'hsl(0 0% 60%)', ring: 'hsl(0 0% 45.1%)' },
    red: { light: 'hsl(0 72.2% 50.6%)', dark: 'hsl(0 84% 60%)', ring: 'hsl(0 72.2% 50.6%)' },
    rose: { light: 'hsl(346.8 77.2% 49.8%)', dark: 'hsl(346.8 84% 61%)', ring: 'hsl(346.8 77.2% 49.8%)' },
    orange: { light: 'hsl(24.6 95% 53.1%)', dark: 'hsl(24 94% 50%)', ring: 'hsl(24.6 95% 53.1%)' },
    green: { light: 'hsl(142.1 76.2% 36.3%)', dark: 'hsl(142 71% 45%)', ring: 'hsl(142.1 76.2% 36.3%)' },
    blue: { light: 'hsl(221.2 83.2% 53.3%)', dark: 'hsl(217 91% 60%)', ring: 'hsl(221.2 83.2% 53.3%)' },
    yellow: { light: 'hsl(47.9 95.8% 51.2%)', dark: 'hsl(47 95% 51%)', ring: 'hsl(47.9 95.8% 51.2%)' },
    violet: { light: 'hsl(262.1 83.3% 57.8%)', dark: 'hsl(262 83% 58%)', ring: 'hsl(262.1 83.3% 57.8%)' },
};

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    accentColor: Ref<AccentColor>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
    updateAccentColor: (value: AccentColor) => void;
};

export function updateTheme(value: Appearance, accent: AccentColor): void {
    if (typeof window === 'undefined') {
        return;
    }

    const isDark =
        value === 'system'
            ? window.matchMedia('(prefers-color-scheme: dark)').matches
            : value === 'dark';

    document.documentElement.classList.toggle('dark', isDark);

    const color = ACCENT_COLORS[accent];
    const primary = isDark ? color.dark : color.light;
    const ring = color.ring;

    // Set variables on documentElement for Tailwind and components...
    const root = document.documentElement;
    root.style.setProperty('--primary', primary, 'important');
    root.style.setProperty('--ring', ring, 'important');

    // Sidebar accents (to make active states in sidebar match the accent color)
    root.style.setProperty('--sidebar-primary', primary, 'important');
    root.style.setProperty('--sidebar-accent', primary, 'important');
    root.style.setProperty(
        '--sidebar-accent-foreground',
        accent === 'yellow' ? 'hsl(0 0% 9%)' : 'hsl(0 0% 98%)',
        'important',
    );

    if (accent === 'zinc') {
        root.style.setProperty(
            '--primary-foreground',
            isDark ? 'hsl(0 0% 9%)' : 'hsl(0 0% 98%)',
            'important',
        );
        root.style.setProperty(
            '--sidebar-primary-foreground',
            isDark ? 'hsl(0 0% 9%)' : 'hsl(0 0% 98%)',
            'important',
        );
    } else {
        root.style.setProperty('--primary-foreground', 'hsl(0 0% 98%)', 'important');
        root.style.setProperty('--sidebar-primary-foreground', 'hsl(0 0% 98%)', 'important');
    }

    // Support for Tailwind v4 theme variables directly if needed
    root.style.setProperty('--color-primary', primary, 'important');
    root.style.setProperty('--color-ring', ring, 'important');
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const prefersDark = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

const getStoredAccentColor = () => {
    if (typeof window === 'undefined') {
        return 'zinc';
    }

    return (localStorage.getItem('accent-color') as AccentColor) || 'zinc';
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();
    const currentAccent = getStoredAccentColor();

    updateTheme(currentAppearance || 'system', currentAccent);
};

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize theme from saved preference or default to system...
    const savedAppearance = getStoredAppearance();
    const savedAccent = getStoredAccentColor();
    updateTheme(savedAppearance || 'system', savedAccent);

    // Set up system theme change listener...
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

const appearance = ref<Appearance>('system');
const accentColor = ref<AccentColor>('zinc');

export function useAppearance(): UseAppearanceReturn {
    onMounted(() => {
        const savedAppearance = localStorage.getItem(
            'appearance',
        ) as Appearance | null;

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }

        const savedAccent = localStorage.getItem(
            'accent-color',
        ) as AccentColor | null;

        if (savedAccent) {
            accentColor.value = savedAccent;
        }
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        if (appearance.value === 'system') {
            return prefersDark() ? 'dark' : 'light';
        }

        return appearance.value;
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value, accentColor.value);
    }

    function updateAccentColor(value: AccentColor) {
        accentColor.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('accent-color', value);

        // Store in cookie for SSR...
        setCookie('accent-color', value);

        updateTheme(appearance.value, value);
    }

    return {
        appearance,
        accentColor,
        resolvedAppearance,
        updateAppearance,
        updateAccentColor,
    };
}
