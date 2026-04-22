<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSidebar } from '@/composables/useSidebar';

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
            <img src="/itinda-logo-2.png" alt="iTinda Logo" class="header-logo-img" />
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
    background: #143926;
    border-bottom: 1px solid #1f5d42;
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
    background: rgba(255, 255, 255, 0.1);
}

.menu-toggle svg {
    width: 24px;
    height: 24px;
    color: #C5A059;
    stroke: #C5A059;
}

.header-logo-img {
    height: 36px;
    width: auto;
    object-fit: contain;
    display: block;
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
    color: #ffffff;
    white-space: nowrap;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #C5A059;
    color: #0e250f;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
}
</style>
