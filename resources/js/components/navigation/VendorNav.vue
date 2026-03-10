<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth as { user: any; hasStore: boolean; storeIsApproved: boolean; roles: string[]; permissions: string[] });

function hasPermission(permission: string): boolean {
    return auth.value.permissions?.includes(permission) || auth.value.roles?.includes('vendor');
}

const showFullNav = computed(() => auth.value.hasStore && auth.value.storeIsApproved);

function isActive(path: string): boolean {
    return page.url.startsWith(path);
}
</script>

<template>
    <div class="nav-menu">
        <Link href="/vendor/dashboard" class="nav-item" :class="{ active: isActive('/vendor/dashboard') }" title="Dashboard">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </Link>

        <Link href="/vendor/profile" class="nav-item" :class="{ active: isActive('/vendor/profile') }" title="Profile">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile
        </Link>

        <template v-if="showFullNav">
            <Link v-if="hasPermission('manage-products')" href="/vendor/products" class="nav-item" :class="{ active: isActive('/vendor/products') }" title="Products">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Products
            </Link>

            <Link v-if="hasPermission('manage-inventory')" href="/vendor/inventory" class="nav-item" :class="{ active: isActive('/vendor/inventory') }" title="Inventory">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Inventory
            </Link>

            <Link v-if="hasPermission('manage-orders')" href="/vendor/orders" class="nav-item" :class="{ active: isActive('/vendor/orders') }" title="Orders">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Orders
            </Link>

            <template v-if="hasPermission('manage-store-settings') || hasPermission('manage-staff') || hasPermission('view-revenue')">
                <div class="nav-divider"></div>
                <div class="nav-section-label">Management</div>

                <Link v-if="hasPermission('manage-store-settings')" href="/vendor/store-settings" class="nav-item" :class="{ active: isActive('/vendor/store-settings') }" title="Store Management">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Store Management
                </Link>

                <Link v-if="hasPermission('manage-staff')" href="/vendor/staff" class="nav-item" :class="{ active: isActive('/vendor/staff') }" title="Staff Management">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Staff Management
                </Link>

                <Link v-if="hasPermission('view-expenses')" href="/vendor/expenses" class="nav-item" :class="{ active: isActive('/vendor/expenses') }" title="Expenses">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Expenses
                </Link>

                <Link v-if="hasPermission('view-analytics')" href="/vendor/analytics" class="nav-item" :class="{ active: isActive('/vendor/analytics') }" title="Analytics">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Analytics
                </Link>
            </template>
        </template>
    </div>
</template>

<style scoped>
.nav-menu {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-item {
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
    font-family: 'Lato', sans-serif;
    position: relative;
}

.nav-item svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

/* Hover */
.nav-item:hover {
    background: rgba(197, 160, 89, 0.1);
    color: #C5A059;
}

/* Active */
.nav-item.active {
    background: rgba(197, 160, 89, 0.15);
    color: #C5A059;
    font-weight: 700;
}

/* Gold accent bar on left edge */
.nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 55%;
    background: #C5A059;
    border-radius: 0 3px 3px 0;
}

.nav-divider {
    height: 1px;
    background: rgba(197, 160, 89, 0.2);
    margin: 12px 0;
}

.nav-section-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(197, 160, 89, 0.5);
    padding: 8px 14px 4px;
    font-family: 'Lato', sans-serif;
    white-space: nowrap;
    overflow: hidden;
}
</style>