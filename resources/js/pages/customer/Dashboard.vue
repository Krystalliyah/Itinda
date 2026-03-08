<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';

const props = defineProps<{ tenantProductsProof?: Array<any>; showDebugPanel?: boolean }>();

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

                <div v-if="showDebugPanel && tenantProductsProof && tenantProductsProof.length" class="mt-8">
                    <h2 class="text-lg font-bold mb-4">Tenant Products Proof Panel (Debug)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="t in tenantProductsProof" :key="t.tenant_id" class="p-4 border rounded shadow-sm">
                            <h3 class="font-semibold">{{ t.store_name || t.tenant_id }} ({{ t.tenant_id }})</h3>
                            <p class="text-sm text-gray-600">{{ t.domain }} &middot; DB: {{ t.tenant_db }}</p>
                            <p>Status: <span :class="t.status === 'ok' ? 'text-green-600' : 'text-red-600'">{{ t.status }}</span></p>
                            <p v-if="t.status === 'error'" class="text-xs text-red-600">Error: {{ t.error_message }}</p>
                            <p class="mt-2">Products count: {{ t.products_count }}</p>
                            <ul class="list-disc list-inside mt-2">
                                <li v-for="p in t.sample_products" :key="p.id">
                                    {{ p.id }} - {{ p.name }} <span v-if="p.price">({{ p.price }})</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
