import { computed, ref } from 'vue';

const DRAWER_BREAKPOINT = 768;

const desktopCollapsed = ref(false);
const mobileOpen = ref(false);
const windowWidth = ref(
    typeof window !== 'undefined' ? window.innerWidth : 1440,
);

let initialized = false;

const syncWindowWidth = () => {
    if (typeof window === 'undefined') return;

    windowWidth.value = window.innerWidth;

    if (windowWidth.value >= DRAWER_BREAKPOINT) {
        mobileOpen.value = false;
    }
};

export function useSidebar() {
    if (typeof window !== 'undefined' && !initialized) {
        syncWindowWidth();
        window.addEventListener('resize', syncWindowWidth);
        initialized = true;
    }

    const isDrawerMode = computed(() => windowWidth.value < DRAWER_BREAKPOINT);

    const isCollapsed = computed(() => {
        return isDrawerMode.value ? false : desktopCollapsed.value;
    });

    const toggleSidebar = () => {
        if (isDrawerMode.value) {
            mobileOpen.value = !mobileOpen.value;
            return;
        }

        desktopCollapsed.value = !desktopCollapsed.value;
    };

    const openMobileSidebar = () => {
        mobileOpen.value = true;
    };

    const closeMobileSidebar = () => {
        mobileOpen.value = false;
    };

    return {
        isCollapsed,
        isDrawerMode,
        isMobileOpen: mobileOpen,
        isDesktopCollapsed: desktopCollapsed,
        toggleSidebar,
        openMobileSidebar,
        closeMobileSidebar,
    };
}