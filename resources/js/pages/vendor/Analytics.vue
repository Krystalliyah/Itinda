<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import VendorNavIcons from '@/components/navigation/VendorNavIcons.vue';
import PlaceholderPage from '@/components/PlaceholderPage.vue';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));
</script>

<template>
    <Head title="Sales Analytics" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="vendor">
            <VendorNav />
            <template #icons>
                <VendorNavIcons />
            </template>
        </Sidebar>

        <main :class="contentClass">
            <PlaceholderPage
                title="Sales Analytics"
                description="View complete sales analytics, product performance, and revenue trends. Owner access only."
            />
        </main>
    </div>
</template>
