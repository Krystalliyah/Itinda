<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="nav-menu-icons">
        <Link href="/customer/dashboard" class="nav-icon" title="Dashboard">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        </Link>

        <Link href="/customer/stores" class="nav-icon" title="Find Stores">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </Link>

        <Link href="/customer/products" class="nav-icon" title="Browse Products">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </Link>

        <Link href="/customer/orders" class="nav-icon" title="My Orders">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        </Link>

        <Link href="/customer/profile" class="nav-icon" title="Profile">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </Link>
    </div>
</template>

<style scoped>
.nav-menu-icons {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 8px;
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.2s;
    position: relative;
}

.nav-icon:hover {
    background: rgba(197, 160, 89, 0.1);
    color: #C5A059;
}

.nav-icon svg {
    width: 20px;
    height: 20px;
}

.nav-icon:hover::after {
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
</style>
