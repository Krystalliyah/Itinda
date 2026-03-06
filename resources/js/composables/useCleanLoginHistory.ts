import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

/**
 * Improves browser history after login to prevent back button issues.
 * When user logs in, the history stack includes /login which redirects away.
 * This cleans up the redirect loop by replacing history entries.
 */
export function useCleanLoginHistory() {
    const page = usePage();

    onMounted(() => {
        // Check if this page was loaded after a successful login
        if (page.props.justLoggedIn) {
            // Replace the current page entry to clear the "just logged in" state
            // This prevents duplicate history entries from redirect chains
            window.history.replaceState(
                { cleaned: true },
                document.title,
                window.location.href
            );

            // If there are multiple entries from the login redirect chain,
            // go back once then forward to consolidate them
            if (window.history.length > 2) {
                // Small delay to ensure replaceState completes
                setTimeout(() => {
                    window.history.back();
                    setTimeout(() => window.history.forward(), 50);
                }, 50);
            }
        }
    });
}

