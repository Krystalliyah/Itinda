<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);

const isActive = (href: string) => {
    if (href === '/admin/dashboard') return currentPath.value === href;
    return currentPath.value.startsWith(href);
};
</script>

<template>
    <div class="space-y-1.5">
        <Link
            href="/admin/dashboard"
            :class="['sidebar-nav-item', { active: isActive('/admin/dashboard') }]"
            title="Dashboard"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                    />
                </svg>
            </span>
            <span class="sidebar-nav-label">Dashboard</span>
        </Link>

        <Link
            href="/admin/vendors"
            :class="['sidebar-nav-item', { active: isActive('/admin/vendors') }]"
            title="Vendors"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    />
                </svg>
            </span>
            <span class="sidebar-nav-label">Vendors</span>
        </Link>

        <Link
            href="/admin/customers"
            :class="['sidebar-nav-item', { active: isActive('/admin/customers') }]"
            title="Customers"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                </svg>
            </span>
            <span class="sidebar-nav-label">Customers</span>
        </Link>

        <Link
            href="/admin/categories"
            :class="['sidebar-nav-item', { active: isActive('/admin/categories') }]"
            title="Categories"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                    />
                </svg>
            </span>
            <span class="sidebar-nav-label">Categories</span>
        </Link>

        <Link
            href="/admin/reports"
            :class="['sidebar-nav-item', { active: isActive('/admin/reports') }]"
            title="Reports"
        >
            <span class="sidebar-nav-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    />
                </svg>
            </span>
            <span class="sidebar-nav-label">Reports</span>
        </Link>
    </div>
</template>

<style scoped>
.active {
    background: rgba(197, 160, 89, 0.1);
    color: #c5a059;
}
</style>