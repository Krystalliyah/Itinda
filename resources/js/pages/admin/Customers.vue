<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
// import AdminNavIcons from '@/components/navigation/AdminNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { 
    UsersIcon, 
    EnvelopeIcon, 
    PhoneIcon, 
    CheckBadgeIcon,
    XCircleIcon 
} from '@heroicons/vue/24/outline';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    email_verified_at: string | null;
    created_at: string;
}

interface Props {
    customers: {
        data: Customer[];
    };
}

const props = defineProps<Props>();

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));

const verifiedCustomers = computed(() => 
    props.customers?.data?.filter(c => c.email_verified_at) ?? []
);

const unverifiedCustomers = computed(() => 
    props.customers?.data?.filter(c => !c.email_verified_at) ?? []
);
</script>

<template>
    <Head title="Manage Customers" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="admin">
            <AdminNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="page-container">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-header-icon">
                        <UsersIcon class="header-icon" />
                    </div>
                    <div>
                        <h1>Customer Management</h1>
                        <p>View and manage all customer accounts</p>
                    </div>
                    <div class="header-stats">
                        <div class="stat-chip stat-verified">
                            <CheckBadgeIcon class="stat-icon" />
                            <span>{{ verifiedCustomers.length }} Verified</span>
                        </div>
                        <div class="stat-chip stat-unverified">
                            <XCircleIcon class="stat-icon" />
                            <span>{{ unverifiedCustomers.length }} Unverified</span>
                        </div>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="card">
                    <div class="card-header">
                        <UsersIcon class="card-header-icon" />
                        <h2>All Customers <span class="count-badge">{{ customers.data.length }}</span></h2>
                    </div>

                    <div v-if="customers.data.length > 0" class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="customer in customers.data" :key="customer.id">
                                    <td><span class="id-pill">{{ customer.id }}</span></td>
                                    <td>
                                        <div class="customer-name">{{ customer.name }}</div>
                                    </td>
                                    <td>
                                        <div class="email-cell">
                                            <EnvelopeIcon class="cell-icon" />
                                            {{ customer.email }}
                                        </div>
                                    </td>
                                    <td>
                                        <div v-if="customer.phone" class="phone-cell">
                                            <PhoneIcon class="cell-icon" />
                                            {{ customer.phone }}
                                        </div>
                                        <span v-else class="text-muted">—</span>
                                    </td>
                                    <td>
                                        <span v-if="customer.email_verified_at" class="badge badge-verified">
                                            <CheckBadgeIcon class="badge-icon" />
                                            Verified
                                        </span>
                                        <span v-else class="badge badge-unverified">
                                            <XCircleIcon class="badge-icon" />
                                            Unverified
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ new Date(customer.created_at).toLocaleDateString() }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="empty-state">
                        <UsersIcon class="empty-icon" />
                        <p>No customers registered yet.</p>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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

.header-stats {
    display: flex;
    gap: 0.6rem;
    margin-left: auto;
}

.stat-chip {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
}

.stat-verified {
    background: #d1fae5;
    color: #065f46;
}

.stat-unverified {
    background: #fee2e2;
    color: #991b1b;
}

.stat-icon {
    width: 15px;
    height: 15px;
}

.card {
    background: white;
    border-radius: 14px;
    padding: 1.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-header-icon {
    width: 20px;
    height: 20px;
    color: #10b981;
}

.count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    background: #d1fae5;
    color: #065f46;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    padding: 0.6rem 0.85rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #f8fafc;
    border-bottom: 1.5px solid #e2e8f0;
}

td {
    padding: 0.85rem 0.85rem;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.88rem;
    color: #0f172a;
    vertical-align: middle;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background: #f8fafc;
}

.id-pill {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
}

.customer-name {
    font-weight: 600;
    color: #0f172a;
}

.email-cell,
.phone-cell {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: #475569;
}

.cell-icon {
    width: 14px;
    height: 14px;
    color: #94a3b8;
    flex-shrink: 0;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-verified {
    background: #d1fae5;
    color: #065f46;
}

.badge-unverified {
    background: #fee2e2;
    color: #991b1b;
}

.badge-icon {
    width: 13px;
    height: 13px;
}

.text-muted {
    color: #64748b;
    font-size: 0.85rem;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 2.5rem 1rem;
    text-align: center;
}

.empty-icon {
    width: 48px;
    height: 48px;
    color: #cbd5e1;
}

.empty-state p {
    color: #64748b;
    font-size: 0.9rem;
    margin: 0;
}

@media (max-width: 900px) {
    .header-stats {
        display: none;
    }
}

@media (max-width: 768px) {
    .page-container {
        padding: 1.25rem 1rem;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }
}

</style>
