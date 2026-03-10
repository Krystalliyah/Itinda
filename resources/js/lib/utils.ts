import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    const url = typeof href === 'string' ? href : href?.url;
    if (!url) return '';

    // If it's a protocol-relative or absolute URL pointing to our app
    if (url.includes('itinda.test')) {
        try {
            // Handle // domains by temporarily adding a protocol for the URL constructor
            const fullUrl = url.startsWith('//') ? `http:${url}` : url;
            const parsed = new URL(fullUrl);
            return parsed.pathname + parsed.search + parsed.hash;
        } catch {
            return url;
        }
    }

    return url;
}
