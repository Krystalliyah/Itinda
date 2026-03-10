<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);

const isActive = (href: string) => {
    if (href === '/customer/dashboard') return currentPath.value === href;
    return currentPath.value.startsWith(href);
};
</script>

<template>
    <div class="space-y-1.5">
        <Link
            href="/customer/dashboard"
            :class="['sidebar-nav-item', { active: isActive('/customer/dashboard') }]"
            title="Dashboard"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </span>
            <span class="sidebar-nav-label">Dashboard</span>
        </Link>

        <Link
            href="/customer/stores"
            :class="['sidebar-nav-item', { active: isActive('/customer/stores') }]"
            title="Find Stores"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <span class="sidebar-nav-label">Find Stores</span>
        </Link>

        <Link
            href="/customer/products"
            :class="['sidebar-nav-item', { active: isActive('/customer/products') }]"
            title="Browse Products"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </span>
            <span class="sidebar-nav-label">Browse Products</span>
        </Link>

        <Link
            href="/customer/orders"
            :class="['sidebar-nav-item', { active: isActive('/customer/orders') }]"
            title="My Orders"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </span>
            <span class="sidebar-nav-label">My Orders</span>
        </Link>

        <Link
            href="/customer/profile"
            :class="['sidebar-nav-item', { active: isActive('/customer/profile') }]"
            title="Profile"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
            <span class="sidebar-nav-label">Profile</span>
        </Link>
    </div>
</template>

<style scoped>
.active {
    background: rgba(197, 160, 89, 0.1);
    color: #c5a059;
}
</style>