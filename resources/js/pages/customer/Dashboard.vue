<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));
</script>

<template>
    <Head title="Customer Dashboard" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="customer">
            <CustomerNav />
            <template #icons>
                <CustomerNavIcons />
            </template>
        </Sidebar>

        <main :class="contentClass">
            <div class="container">
                <h1>Customer Dashboard</h1>
                <p>Browse stores, compare prices, and manage your orders</p>
            </div>
        </main>
    </div>
</template>
