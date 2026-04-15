<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import { useSidebar } from '@/composables/useSidebar';
import ConfirmationModal from '@/components/ui/modal/ConfirmationModal.vue';

defineProps<{
    role: 'admin' | 'vendor' | 'customer';
}>();

const {
    isCollapsed,
    isDrawerMode,
    isMobileOpen,
    closeMobileSidebar,
} = useSidebar();

const showLogoutModal = ref(false);

const openLogoutModal = () => {
    if (isDrawerMode.value) {
        closeMobileSidebar();
    }
    showLogoutModal.value = true;
};

const confirmLogout = () => {
    router.post('/logout');
};

const handleDrawerNavClick = () => {
    if (isDrawerMode.value) {
        closeMobileSidebar();
    }
};
</script>

<template>
    <div
        v-if="isDrawerMode && isMobileOpen"
        class="sidebar-overlay"
        @click="closeMobileSidebar"
    />

    <nav
        :class="[
            'sidebar',
            {
                collapsed: isCollapsed,
                'mobile-open': isMobileOpen,
                'drawer-mode': isDrawerMode,
            },
        ]"
    >
        <div class="sidebar-content">
            <div class="sidebar-header">
                <div class="sidebar-header-left">
                    <div class="sidebar-logo">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                            />
                        </svg>
                    </div>

                    <h3 v-show="!isCollapsed || isDrawerMode" class="sidebar-title">
                        {{
                            role === 'admin'
                                ? 'Admin'
                                : role === 'vendor'
                                  ? 'Vendor'
                                  : 'Customer'
                        }}
                    </h3>
                </div>

                <button
                    v-if="isDrawerMode"
                    type="button"
                    class="sidebar-close-btn"
                    aria-label="Close sidebar"
                    @click="closeMobileSidebar"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="sidebar-menu" @click="handleDrawerNavClick">
                <slot />
            </div>

            <div class="sidebar-footer">
                <Link
                    href="/settings/password"
                    class="menu-item"
                    title="Settings"
                    @click="handleDrawerNavClick"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                    <span v-show="!isCollapsed || isDrawerMode" class="menu-text">Settings</span>
                </Link>

                <button
                    @click="openLogoutModal"
                    class="menu-item logout-btn"
                    title="Logout"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                        />
                    </svg>
                    <span v-show="!isCollapsed || isDrawerMode" class="menu-text">Logout</span>
                </button>
            </div>
        </div>

        <ConfirmationModal
            v-model:open="showLogoutModal"
            title="Confirm Logout"
            description="Are you sure you want to log out of your account?"
            confirm-text="Log out"
            cancel-text="Cancel"
            variant="destructive"
            @confirm="confirmLogout"
        />
    </nav>
</template>

<style scoped>
.sidebar {
    --sidebar-expanded: 250px;
    --sidebar-collapsed: 78px;
    --sidebar-pad-x: 14px;
    --sidebar-pad-y: 18px;
    --icon-box: 44px;
    --icon-size: 20px;

    position: fixed;
    left: 0;
    top: 64px;
    height: calc(100vh - 64px);
    width: var(--sidebar-expanded);
    background: var(--sidebar-background);
    z-index: 30;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition:
        width 0.28s cubic-bezier(0.4, 0, 0.2, 1),
        transform 0.28s cubic-bezier(0.4, 0, 0.2, 1),
        box-shadow 0.28s cubic-bezier(0.4, 0, 0.2, 1),
        top 0.2s ease,
        height 0.2s ease;
}

.sidebar.collapsed {
    width: var(--sidebar-collapsed);
}

.sidebar-content {
    padding: var(--sidebar-pad-y) var(--sidebar-pad-x);
    color: var(--sidebar-foreground);
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    transition: padding 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar.collapsed .sidebar-content {
    padding: var(--sidebar-pad-y) 10px;
}

.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: var(--icon-box);
    margin-bottom: 26px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--sidebar-border);
}

.sidebar-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.sidebar.collapsed .sidebar-header {
    justify-content: center;
}

.sidebar-logo {
    width: var(--icon-box);
    height: var(--icon-box);
    background: var(--brand-gold);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sidebar-logo svg {
    width: var(--icon-size);
    height: var(--icon-size);
    color: var(--brand-green);
}

.sidebar-title {
    color: var(--brand-gold);
    margin: 0;
    font-size: 1.3rem;
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    white-space: nowrap;
    opacity: 1;
    transform: translateX(0);
    transition:
        opacity 0.18s ease,
        transform 0.18s ease,
        width 0.18s ease,
        margin 0.18s ease;
}

.sidebar.collapsed .sidebar-title {
    opacity: 0;
    width: 0;
    margin: 0;
    overflow: hidden;
    transform: translateX(-6px);
}

.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-footer {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid var(--sidebar-border);
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: var(--icon-box);
    padding: 0 14px;
    border-radius: 12px;
    color: var(--sidebar-foreground);
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        gap 0.28s cubic-bezier(0.4, 0, 0.2, 1),
        padding 0.28s cubic-bezier(0.4, 0, 0.2, 1),
        width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-family: 'Lato', sans-serif;
    position: relative;
}

.sidebar.collapsed .menu-item {
    width: var(--icon-box);
    min-height: var(--icon-box);
    padding: 0;
    justify-content: center;
    margin: 0 auto;
    gap: 0;
}

.menu-item:hover {
    background: rgba(197, 160, 89, 0.1);
    color: var(--brand-gold);
}

.menu-item svg {
    width: var(--icon-size);
    height: var(--icon-size);
    flex-shrink: 0;
}

.menu-text {
    white-space: nowrap;
    opacity: 1;
    transform: translateX(0);
    transition:
        opacity 0.18s ease,
        transform 0.18s ease,
        width 0.18s ease,
        margin 0.18s ease;
}

.sidebar.collapsed .menu-text {
    opacity: 0;
    width: 0;
    margin: 0;
    overflow: hidden;
    transform: translateX(-6px);
}

.sidebar.collapsed .menu-item:hover::after {
    content: attr(title);
    position: absolute;
    left: 56px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--brand-green-dark);
    color: white;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 0.8rem;
    white-space: nowrap;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    pointer-events: none;
}

.logout-btn {
    color: rgba(255, 255, 255, 0.5);
}

.logout-btn:hover {
    background: rgba(220, 38, 38, 0.1);
    color: #ef4444;
}

.sidebar-menu::-webkit-scrollbar {
    width: 4px;
}

.sidebar-menu::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
}

.sidebar-menu::-webkit-scrollbar-thumb {
    background: rgba(197, 160, 89, 0.3);
    border-radius: 2px;
}

.sidebar-menu::-webkit-scrollbar-thumb:hover {
    background: rgba(197, 160, 89, 0.5);
}

.sidebar-overlay {
    position: fixed;
    inset: 64px 0 0 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 29;
    transition: inset 0.2s ease;
}

.sidebar-close-btn {
    display: none;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    color: white;
    cursor: pointer;
    flex-shrink: 0;
}

.sidebar-close-btn:hover {
    background: rgba(255, 255, 255, 0.14);
}

@media (max-width: 767px) {
    .sidebar {
        width: min(280px, calc(100vw - 32px));
        transform: translateX(-100%);
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
    }

    .sidebar.mobile-open {
        transform: translateX(0);
    }

    .sidebar.collapsed {
        width: min(280px, calc(100vw - 32px));
    }

    .sidebar.collapsed .sidebar-content {
        padding: var(--sidebar-pad-y) var(--sidebar-pad-x);
    }

    .sidebar.collapsed .sidebar-header {
        justify-content: space-between;
    }

    .sidebar.collapsed .sidebar-title,
    .sidebar.collapsed .menu-text {
        opacity: 1;
        width: auto;
        margin: 0;
        overflow: visible;
        transform: translateX(0);
    }

    .sidebar.collapsed .menu-item {
        width: 100%;
        min-height: var(--icon-box);
        padding: 0 14px;
        justify-content: flex-start;
        margin: 0;
        gap: 12px;
    }

    .sidebar.collapsed .menu-item:hover::after {
        display: none;
    }

    .sidebar-close-btn {
        display: inline-flex;
    }
}
.sidebar-nav-item,
:deep(.sidebar-nav-item) {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: var(--icon-box);
    padding: 0 14px;
    border-radius: 12px;
    color: var(--sidebar-foreground);
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        padding 0.28s cubic-bezier(0.4, 0, 0.2, 1),
        gap 0.28s cubic-bezier(0.4, 0, 0.2, 1),
        width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-family: 'Lato', sans-serif;
    position: relative;
    box-sizing: border-box;
}

.sidebar-nav-item:hover,
:deep(.sidebar-nav-item:hover) {
    background: rgba(197, 160, 89, 0.1);
    color: var(--brand-gold);
}

.sidebar-nav-icon,
:deep(.sidebar-nav-icon) {
    width: var(--icon-box);
    height: var(--icon-box);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sidebar-nav-icon svg,
:deep(.sidebar-nav-icon svg) {
    width: var(--icon-size);
    height: var(--icon-size);
    flex-shrink: 0;
}

.sidebar-nav-label,
:deep(.sidebar-nav-label) {
    white-space: nowrap;
    opacity: 1;
    transform: translateX(0);
    transition:
        opacity 0.18s ease,
        width 0.18s ease,
        margin 0.18s ease,
        transform 0.18s ease;
}

.sidebar.collapsed .sidebar-nav-item,
.sidebar.collapsed :deep(.sidebar-nav-item) {
    width: var(--icon-box);
    min-height: var(--icon-box);
    padding: 0;
    justify-content: center;
    gap: 0;
    margin: 0 auto;
}

.sidebar.collapsed .sidebar-nav-label,
.sidebar.collapsed :deep(.sidebar-nav-label) {
    width: 0;
    margin: 0;
    opacity: 0;
    overflow: hidden;
    transform: translateX(-6px);
}

.sidebar.collapsed .sidebar-nav-item:hover::after,
.sidebar.collapsed :deep(.sidebar-nav-item:hover::after) {
    content: attr(title);
    position: absolute;
    left: 56px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--brand-green-dark);
    color: white;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 0.8rem;
    white-space: nowrap;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    pointer-events: none;
}
</style>