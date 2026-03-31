<script setup lang="ts">
import { useSidebar } from '@/composables/useSidebar';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { toggleSidebar } = useSidebar();
const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <header class="app-header">
        <div class="header-left">
            <button @click="toggleSidebar" class="menu-toggle" title="Toggle Menu">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h1 class="header-title">iTinda</h1>
        </div>
        
        <div class="header-right" v-if="user">
            <div class="user-info">
                <span class="user-name">{{ user.name }}</span>
                <div class="user-avatar">
                    {{ user.name.charAt(0).toUpperCase() }}
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
.app-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 64px;
    background: var(--background);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    z-index: 40;
    box-shadow: none;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.menu-toggle {
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
    flex-shrink: 0;
}

.menu-toggle:hover {
    background: var(--muted);
}

.menu-toggle svg {
    width: 24px;
    height: 24px;
    color: var(--brand-green);
    stroke: var(--brand-green);
}

.header-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--brand-green);
    margin: 0;
    font-family: 'Montserrat', sans-serif;
    white-space: nowrap;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--foreground);
    white-space: nowrap;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--brand-gold);
    color: var(--brand-green);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
}
</style>
