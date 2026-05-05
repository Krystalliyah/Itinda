<script setup lang="ts">
import { BuildingStorefrontIcon, ChartBarIcon, CubeIcon, ClipboardDocumentListIcon, UsersIcon, Cog6ToothIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    tenant: { id: string; name: string };
}>();

const quickLinks = [
    { label: 'Products',      href: '/products',       icon: CubeIcon,                   color: '#4f46e5' },
    { label: 'Inventory',     href: '/inventory',      icon: ChartBarIcon,               color: '#0891b2' },
    { label: 'Orders',        href: '/orders',         icon: ClipboardDocumentListIcon,  color: '#059669' },
    { label: 'Staff',         href: '/staff',          icon: UsersIcon,                  color: '#d97706' },
    { label: 'Analytics',     href: '/analytics',      icon: ChartBarIcon,               color: '#7c3aed' },
    { label: 'Store Settings',href: '/store-settings', icon: Cog6ToothIcon,              color: '#64748b' },
];

const showLogoutModal = ref(false);

const openLogoutModal = () => {
    showLogoutModal.value = true;
};

const confirmLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head :title="`${tenant.name} — Dashboard`" />

    <div class="tenant-shell">
        <!-- Sidebar -->
        <aside class="tenant-sidebar">
            <div class="sidebar-brand">
                <BuildingStorefrontIcon class="brand-icon" />
                <span class="brand-name">{{ tenant.name }}</span>
            </div>

            <nav class="sidebar-nav">
                <a v-for="link in quickLinks" :key="link.href" :href="link.href" class="nav-item">
                    <component :is="link.icon" class="nav-icon" />
                    {{ link.label }}
                </a>
            </nav>

            <button @click="openLogoutModal" class="logout-btn">
                <ArrowRightOnRectangleIcon class="nav-icon" />
                Logout
            </button>
        </aside>

        <!-- Main content -->
        <main class="tenant-main">
            <div class="welcome-banner">
                <div class="welcome-icon">
                    <BuildingStorefrontIcon />
                </div>
                <div>
                    <h1 class="welcome-title">Welcome to {{ tenant.name }}</h1>
                    <p class="welcome-sub">Your store is live. Start managing your products and orders below.</p>
                </div>
            </div>

            <div class="quick-grid">
                <a v-for="link in quickLinks" :key="link.href" :href="link.href" class="quick-card">
                    <div class="quick-icon" :style="{ background: link.color + '18', color: link.color }">
                        <component :is="link.icon" />
                    </div>
                    <span class="quick-label">{{ link.label }}</span>
                </a>
            </div>
        </main>

        <Dialog v-model:open="showLogoutModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Confirm Logout</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to log out of your account?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showLogoutModal = false">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmLogout">
                        Log out
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* { font-family: 'Inter', sans-serif; }

.tenant-shell {
    display: flex;
    min-height: 100vh;
    background: #f8fafc;
}

/* Sidebar */
.tenant-sidebar {
    width: 240px;
    flex-shrink: 0;
    background: #1b4d3e;
    display: flex;
    flex-direction: column;
    padding: 1.5rem 1rem;
    gap: 1.5rem;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0.75rem;
}

.brand-icon {
    width: 28px;
    height: 28px;
    color: #c5a059;
    flex-shrink: 0;
}

.brand-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #c5a059;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-nav {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.75rem;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.15s;
}

.nav-item:hover {
    background: rgba(197,160,89,0.12);
    color: #c5a059;
}

.nav-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.75rem;
    width: 100%;
    background: none;
    border: none;
    color: rgba(255,255,255,0.5);
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    text-align: left;
}

.logout-btn:hover {
    background: rgba(220,38,38,0.1);
    color: #f87171;
}

/* Main */
.tenant-main {
    flex: 1;
    padding: 2.5rem;
    max-width: 1000px;
}

.welcome-banner {
    display: flex;
    gap: 1.25rem;
    align-items: center;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.welcome-icon {
    width: 56px;
    height: 56px;
    color: #1b4d3e;
    flex-shrink: 0;
}

.welcome-icon svg {
    width: 100%;
    height: 100%;
}

.welcome-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.25rem 0;
}

.welcome-sub {
    color: #64748b;
    margin: 0;
    font-size: 0.95rem;
}

.quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1rem;
}

.quick-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem 1rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-decoration: none;
    color: #0f172a;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.quick-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.quick-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quick-icon svg {
    width: 24px;
    height: 24px;
}

.quick-label {
    text-align: center;
    line-height: 1.3;
}
</style>
