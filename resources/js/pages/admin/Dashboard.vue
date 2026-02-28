<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
import AdminNavIcons from '@/components/navigation/AdminNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { 
    BuildingStorefrontIcon, 
    CheckCircleIcon, 
    ClockIcon, 
    UsersIcon,
    ChartBarIcon 
} from '@heroicons/vue/24/outline';

interface Props {
    vendorStats: {
        pending: number;
        active: number;
        total: number;
    };
    customerCount: number;
}

const props = defineProps<Props>();

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="admin">
            <AdminNav />
            <template #icons>
                <AdminNavIcons />
            </template>
        </Sidebar>

        <main :class="contentClass">
            <div class="page-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-header-icon">
                        <ChartBarIcon class="header-icon" />
                    </div>
                    <div>
                        <h1>Admin Dashboard</h1>
                        <p>Platform overview and management</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    
                    <!-- Total Vendors -->
                    <div class="stat-card stat-total">
                        <div class="stat-icon-wrapper">
                            <BuildingStorefrontIcon class="stat-icon" />
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Total Vendors</p>
                            <h2 class="stat-value">{{ vendorStats.total }}</h2>
                        </div>
                    </div>

                    <!-- Active Vendors -->
                    <div class="stat-card stat-active">
                        <div class="stat-icon-wrapper">
                            <CheckCircleIcon class="stat-icon" />
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Active Vendors</p>
                            <h2 class="stat-value">{{ vendorStats.active }}</h2>
                        </div>
                    </div>

                    <!-- Pending Vendors -->
                    <div class="stat-card stat-pending">
                        <div class="stat-icon-wrapper">
                            <ClockIcon class="stat-icon" />
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Pending Approval</p>
                            <h2 class="stat-value">{{ vendorStats.pending }}</h2>
                        </div>
                    </div>

                    <!-- Total Customers -->
                    <div class="stat-card stat-customers">
                        <div class="stat-icon-wrapper">
                            <UsersIcon class="stat-icon" />
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Total Customers</p>
                            <h2 class="stat-value">{{ customerCount }}</h2>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

:root {
    --emerald-900: #134e3a;
    --emerald-700: #245c4a;
    --emerald-500: #2d8c65;
    --emerald-400: #3aac7d;
    --emerald-100: #d1fae5;
    --emerald-50:  #f0fdf8;
    --muted:       #7a8f88;
    --border:      #d4e8df;
    --bg:          #f6faf8;
    --white:       #ffffff;
    --shadow-sm:   0 1px 3px rgba(36,92,74,0.08);
    --shadow-md:   0 4px 16px rgba(36,92,74,0.10);
    --text-primary: #1a2e27;
}

:global(.dark) {
    --emerald-900: #d1fae5;
    --emerald-700: #3aac7d;
    --emerald-500: #2d8c65;
    --emerald-400: #245c4a;
    --emerald-100: #134e3a;
    --emerald-50:  #0a2e22;
    --muted:       #8a9c95;
    --border:      #1a332a;
    --bg:          #050e0a;
    --white:       #0d1712;
    --shadow-sm:   0 1px 3px rgba(0,0,0,0.4);
    --shadow-md:   0 4px 16px rgba(0,0,0,0.5);
    --text-primary: #e6f0eb;
}

.page-container {
    padding: 2rem 2.5rem;
    background: #f8fafc;
    min-height: 100vh;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.page-header-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 12px;
    padding: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-icon {
    width: 28px;
    height: 28px;
    color: white;
}

.page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.25rem 0;
}

.page-header p {
    color: #64748b;
    margin: 0;
    font-size: 0.95rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 14px;
    padding: 1.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon {
    width: 28px;
    height: 28px;
    color: white;
}

.stat-total .stat-icon-wrapper {
    background: linear-gradient(135deg, var(--emerald-700), var(--emerald-500));
}

.stat-active .stat-icon-wrapper {
    background: linear-gradient(135deg, #10b981, #34d399);
}

.stat-pending .stat-icon-wrapper {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
}

.stat-customers .stat-icon-wrapper {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #64748b;
    margin: 0 0 0.35rem 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    line-height: 1;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
