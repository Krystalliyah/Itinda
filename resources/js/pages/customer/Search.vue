<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import PlaceholderPage from '@/components/PlaceholderPage.vue';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));
</script>

<template>
    <Head title="Search Shops" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="customer">
            <CustomerNav />
            <template #icons>
                <CustomerNavIcons />
            </template>
        </Sidebar>

        <main :class="contentClass">
            <PlaceholderPage
                title="Search Shops"
                description="Find and compare products from nearby vendors."
            />
        </main>
    </div>
</template>
