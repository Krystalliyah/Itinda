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
    background: #ffffff !important;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    z-index: 999;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    background: transparent;
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
    transition: all 0.2s;
    flex-shrink: 0;
}

.menu-toggle:hover {
    background: #f3f4f6 !important;
}

.menu-toggle svg {
    width: 24px;
    height: 24px;
    color: #1B4D3E !important;
    stroke: #1B4D3E !important;
}

.header-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1B4D3E !important;
    margin: 0;
    font-family: 'Montserrat', sans-serif;
    white-space: nowrap;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 16px;
    background: transparent;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    background: transparent;
}

.user-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #374151 !important;
    white-space: nowrap;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #C5A059 !important;
    color: #1B4D3E !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
}
</style>
