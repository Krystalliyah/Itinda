<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { useSidebar } from '@/composables/useSidebar';

defineProps<{
    role: 'admin' | 'vendor' | 'customer';
}>();

const { isCollapsed } = useSidebar();

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <nav :class="['sidebar', { collapsed: isCollapsed }]">
        <div class="sidebar-content">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 v-if="!isCollapsed" class="sidebar-title">{{ role === 'admin' ? 'Admin' : role === 'vendor' ? 'Vendor' : 'Customer' }}</h3>
            </div>
            
            <div class="sidebar-menu">
                <!-- Show full navigation when expanded -->
                <slot v-if="!isCollapsed"></slot>
                <!-- Show icon-only navigation when collapsed -->
                <slot v-else name="icons"></slot>
            </div>

            <div class="sidebar-footer">
                <Link href="/settings/profile" class="menu-item" :title="isCollapsed ? 'Settings' : ''">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span v-if="!isCollapsed">Settings</span>
                </Link>
                <button @click="logout" class="menu-item logout-btn" :title="isCollapsed ? 'Logout' : ''">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span v-if="!isCollapsed">Logout</span>
                </button>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.sidebar {
    position: fixed;
    left: 0;
    top: 64px;
    height: calc(100vh - 64px);
    width: 250px;
    background: #1B4D3E;
    z-index: 998;
    display: flex;
    flex-direction: column;
    transition: width 0.3s ease;
}

.sidebar.collapsed {
    width: 70px;
}

.sidebar-content {
    padding: 20px;
    color: white;
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

.sidebar.collapsed .sidebar-content {
    padding: 20px 10px;
}

.sidebar-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(197, 160, 89, 0.2);
}

.sidebar.collapsed .sidebar-header {
    justify-content: center;
}

.sidebar-logo {
    width: 36px;
    height: 36px;
    background: #C5A059;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.sidebar-logo svg {
    width: 20px;
    height: 20px;
    color: #1B4D3E;
}

.sidebar-title {
    color: #C5A059;
    margin: 0;
    font-size: 1.3rem;
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
}

.sidebar-menu {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-footer {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid rgba(197, 160, 89, 0.2);
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-family: 'Lato', sans-serif;
    position: relative;
}

.sidebar.collapsed .menu-item {
    justify-content: center;
    padding: 12px 8px;
}

.menu-item:hover {
    background: rgba(197, 160, 89, 0.1);
    color: #C5A059;
}

.menu-item svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.sidebar.collapsed .menu-item:hover::after {
    content: attr(title);
    position: absolute;
    left: 75px;
    top: 50%;
    transform: translateY(-50%);
    background: #245c4a;
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    pointer-events: none;
}

.logout-btn {
    color: rgba(255, 255, 255, 0.6);
}

.logout-btn:hover {
    background: rgba(220, 38, 38, 0.1);
    color: #ef4444;
}

/* Scrollbar styling */
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
</style>
