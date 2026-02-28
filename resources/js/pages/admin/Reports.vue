<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
import AdminNavIcons from '@/components/navigation/AdminNavIcons.vue';
import PlaceholderPage from '@/components/PlaceholderPage.vue';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));
</script>

<template>
    <Head title="Platform Reports" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="admin">
            <AdminNav />
            <template #icons>
                <AdminNavIcons />
            </template>
        </Sidebar>

        <main :class="contentClass">
            <PlaceholderPage
                title="Platform Reports"
                description="View platform-wide analytics, generate reports, and monitor system performance."
            />
        </main>
    </div>
</template>
