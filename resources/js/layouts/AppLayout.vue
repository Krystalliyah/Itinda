<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
import AdminNavIcons from '@/components/navigation/AdminNavIcons.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import Sidebar from '@/components/Sidebar.vue';
import { useSidebar } from '@/composables/useSidebar';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const { isCollapsed } = useSidebar();

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));

// Determine the primary user role
const userRole = computed(() => {
    const roles = page.props.auth?.roles || [];
    if (roles.includes('admin')) return 'admin';
    if (roles.includes('vendor')) return 'vendor';
    return 'customer';
});
</script>

<template>
    <div class="dashboard-wrapper">
        <Header />
        <Sidebar :role="userRole">
            <template v-if="userRole === 'admin'">
                <AdminNav />
            </template>
            <template v-else-if="userRole === 'vendor'">
                <VendorNav />
            </template>
            <template v-else>
                <CustomerNav />
            </template>
            
            <template #icons>
                <template v-if="userRole === 'admin'">
                    <AdminNavIcons />
                </template>
            </template>
        </Sidebar>

        <main :class="contentClass">
            <slot />
        </main>
    </div>
</template>
