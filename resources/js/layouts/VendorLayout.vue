<script setup lang="ts">
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import VendorNavIcons from '@/components/navigation/VendorNavIcons.vue';
import VendorStoreSetupBanner from '@/components/VendorStoreSetupBanner.vue';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));
</script>

<template>
    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="vendor">
            <VendorNav />
            <template #icons>
                <VendorNavIcons />
            </template>
        </Sidebar>
        
        <main :class="contentClass">
            <VendorStoreSetupBanner />
            <slot />
        </main>
    </div>
</template>

<style scoped>
/* Remove the extra wrapper styles since dashboard-content already handles positioning */
</style>
