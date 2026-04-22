<script setup lang="ts">
import {
    CheckCircleIcon,
    ClockIcon,
    TrashIcon,
    CheckIcon,
    BuildingStorefrontIcon,
    GlobeAltIcon,
    CalendarIcon,
    Squares2X2Icon,
    TableCellsIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Header from '@/components/Header.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
import Sidebar from '@/components/Sidebar.vue';
import StoreDetailsModal from '@/components/StoreDetailsModal.vue';
import ConfirmationModal from '@/components/ui/modal/ConfirmationModal.vue';
import { useSidebar } from '@/composables/useSidebar';

interface Domain {
    id: number;
    domain: string;
    tenant_id: string;
    created_at: string;
}

interface Tenant {
    id: string;
    name: string;
    email: string;
    is_approved: boolean;
    created_at: string;
    domains: Domain[];
}

interface Props {
    tenants: {
        data: Tenant[];
    };
}

const props = defineProps<Props>();

const { isCollapsed } = useSidebar();
const viewMode = ref<'cards' | 'table'>('table');

const showStoreDetailsModal = ref(false);
const selectedTenant = ref<Tenant | null>(null);

const showApproveModal = ref(false);
const tenantToApprove = ref<Tenant | null>(null);

const showDeleteModal = ref(false);
const tenantToDelete = ref<Tenant | null>(null);
const deleteProcessing = ref(false);

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

const deleteVendor = (id: string) => {
    const tenant = props.tenants.data.find((t) => t.id === id);
    if (!tenant) return;
    tenantToDelete.value = tenant;
    showDeleteModal.value = true;
};

const confirmDeleteVendor = () => {
    if (!tenantToDelete.value) return;
    deleteProcessing.value = true;
    router.delete(`/admin/vendors/${tenantToDelete.value.id}`, {
        onFinish: () => {
            deleteProcessing.value = false;
            showDeleteModal.value = false;
            tenantToDelete.value = null;
        },
    });
};

const cancelDeleteVendor = () => {
    showDeleteModal.value = false;
    tenantToDelete.value = null;
};

const requestApproveVendor = (tenant: Tenant) => {
    tenantToApprove.value = tenant;
    showApproveModal.value = true;
};

const confirmApproveVendor = () => {
    if (!tenantToApprove.value) return;
    router.post(`/admin/vendors/${tenantToApprove.value.id}/approve`, {}, {
        onSuccess: () => {
            showApproveModal.value = false;
            tenantToApprove.value = null;
        },
    });
};

const openStoreDetails = (tenant: Tenant) => {
    selectedTenant.value = tenant;
    showStoreDetailsModal.value = true;
};

const approvedVendors = computed(() =>
    props.tenants?.data?.filter((t) => t.is_approved) ?? [],
);

const pendingVendors = computed(() =>
    props.tenants?.data?.filter((t) => !t.is_approved) ?? [],
);

const totalVendors = computed(() => props.tenants?.data?.length ?? 0);

const getInitials = (name: string) =>
    name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
</script>

<template>
    <Head title="Vendor Management" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="admin">
            <AdminNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="page-container">

                <!-- ── Page Header ── -->
                <div class="page-header">
                    <div class="header-left">
                        <div class="page-title-wrapper">
                            <div class="page-icon-wrap">
                                <BuildingStorefrontIcon class="page-icon-svg" />
                            </div>
                            <div>
                                <h1 class="page-title">Vendor Management</h1>
                                <p class="page-subtitle">Manage vendor stores, approvals &amp; databases</p>
                            </div>
                        </div>
                    </div>

                    <div class="stats-row">
                        <div class="stat-card">
                            <div class="stat-card-icon total-icon">
                                <BuildingStorefrontIcon />
                            </div>
                            <div class="stat-card-body">
                                <span class="stat-card-value">{{ totalVendors }}</span>
                                <span class="stat-card-label">Total</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-icon active-icon">
                                <CheckCircleIcon />
                            </div>
                            <div class="stat-card-body">
                                <span class="stat-card-value">{{ approvedVendors.length }}</span>
                                <span class="stat-card-label">Active</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-icon pending-icon">
                                <ClockIcon />
                            </div>
                            <div class="stat-card-body">
                                <span class="stat-card-value">{{ pendingVendors.length }}</span>
                                <span class="stat-card-label">Pending</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Active Vendors ── -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title-wrapper">
                            <span class="section-accent active-accent"></span>
                            <CheckCircleIcon class="section-icon active-section-icon" />
                            <h2 class="section-title">Active Vendors</h2>
                            <span class="section-badge active-badge">{{ approvedVendors.length }}</span>
                        </div>
                        <div class="view-toggle">
                            <button @click="viewMode = 'table'" :class="['toggle-btn', { active: viewMode === 'table' }]" title="Table View">
                                <TableCellsIcon class="toggle-icon" />
                            </button>
                            <button @click="viewMode = 'cards'" :class="['toggle-btn', { active: viewMode === 'cards' }]" title="Card View">
                                <Squares2X2Icon class="toggle-icon" />
                            </button>
                        </div>
                    </div>

                    <div v-if="approvedVendors.length > 0 && viewMode === 'table'" class="table-container">
                        <table class="vendors-table">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Domain</th>
                                    <th>Database</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tenant in approvedVendors" :key="tenant.id" class="table-row">
                                    <td>
                                        <div class="table-vendor-info">
                                            <div class="table-avatar active-avatar">{{ getInitials(tenant.name) }}</div>
                                            <div>
                                                <div class="table-vendor-name">{{ tenant.name }}</div>
                                                <div class="table-vendor-email">{{ tenant.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a v-if="tenant.domains?.length" :href="`http://${tenant.domains[0]?.domain}`" target="_blank" class="table-link">
                                            <GlobeAltIcon class="link-icon" />
                                            {{ tenant.domains[0]?.domain }}
                                        </a>
                                        <span v-else class="table-muted">—</span>
                                    </td>
                                    <td><span class="db-badge">tenant{{ tenant.id }}</span></td>
                                    <td><span class="date-cell">{{ new Date(tenant.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button @click="openStoreDetails(tenant)" class="action-btn view-action" data-tip="View details"><EyeIcon /></button>
                                            <button @click="deleteVendor(tenant.id)" class="action-btn delete-action" data-tip="Delete vendor"><TrashIcon /></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else-if="approvedVendors.length > 0 && viewMode === 'cards'" class="vendors-grid">
                        <div v-for="tenant in approvedVendors" :key="tenant.id" class="vendor-card active-card">
                            <div class="card-top">
                                <div class="card-avatar active-avatar">{{ getInitials(tenant.name) }}</div>
                                <div class="card-info">
                                    <h3 class="card-name">{{ tenant.name }}</h3>
                                    <p class="card-email">{{ tenant.email }}</p>
                                </div>
                                <span class="status-dot active-dot"></span>
                            </div>
                            <div class="card-meta">
                                <div class="meta-row">
                                    <GlobeAltIcon class="meta-icon" />
                                    <a v-if="tenant.domains?.length" :href="`http://${tenant.domains[0]?.domain}`" target="_blank" class="meta-link">{{ tenant.domains[0]?.domain }}</a>
                                    <span v-else class="meta-muted">No domain</span>
                                </div>
                                <div class="meta-row">
                                    <CalendarIcon class="meta-icon" />
                                    <span class="meta-muted">{{ new Date(tenant.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span class="db-badge">tenant{{ tenant.id }}</span>
                                <div class="card-actions">
                                    <button @click="openStoreDetails(tenant)" class="action-btn view-action"><EyeIcon /></button>
                                    <button @click="deleteVendor(tenant.id)" class="action-btn delete-action"><TrashIcon /></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        <div class="empty-illustration"><BuildingStorefrontIcon class="empty-icon" /></div>
                        <p class="empty-text">No active vendors yet</p>
                        <p class="empty-hint">Approve pending vendors below to get started</p>
                    </div>
                </div>

                <!-- ── Pending Approval ── -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title-wrapper">
                            <span class="section-accent pending-accent"></span>
                            <ClockIcon class="section-icon pending-section-icon" />
                            <h2 class="section-title">Pending Approval</h2>
                            <span class="section-badge pending-badge">{{ pendingVendors.length }}</span>
                        </div>
                        <div class="view-toggle">
                            <button @click="viewMode = 'table'" :class="['toggle-btn', { active: viewMode === 'table' }]" title="Table View">
                                <TableCellsIcon class="toggle-icon" />
                            </button>
                            <button @click="viewMode = 'cards'" :class="['toggle-btn', { active: viewMode === 'cards' }]" title="Card View">
                                <Squares2X2Icon class="toggle-icon" />
                            </button>
                        </div>
                    </div>

                    <div v-if="pendingVendors.length > 0 && viewMode === 'table'" class="table-container">
                        <table class="vendors-table">
                            <thead>
                                <tr>
                                    <th>Vendor</th>
                                    <th>Domain</th>
                                    <th>Database</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tenant in pendingVendors" :key="tenant.id" class="table-row">
                                    <td>
                                        <div class="table-vendor-info">
                                            <div class="table-avatar pending-avatar">{{ getInitials(tenant.name) }}</div>
                                            <div>
                                                <div class="table-vendor-name">{{ tenant.name }}</div>
                                                <div class="table-vendor-email">{{ tenant.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="table-muted">{{ tenant.domains?.[0]?.domain || '—' }}</span></td>
                                    <td><span class="db-badge pending-db">Not provisioned</span></td>
                                    <td><span class="date-cell">{{ new Date(tenant.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <button @click="openStoreDetails(tenant)" class="action-btn view-action" data-tip="View details"><EyeIcon /></button>
                                            <button @click="requestApproveVendor(tenant)" class="action-btn approve-action" data-tip="Approve vendor"><CheckIcon /></button>
                                            <button @click="deleteVendor(tenant.id)" class="action-btn delete-action" data-tip="Delete"><TrashIcon /></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else-if="pendingVendors.length > 0 && viewMode === 'cards'" class="vendors-grid">
                        <div v-for="tenant in pendingVendors" :key="tenant.id" class="vendor-card pending-card">
                            <div class="card-top">
                                <div class="card-avatar pending-avatar">{{ getInitials(tenant.name) }}</div>
                                <div class="card-info">
                                    <h3 class="card-name">{{ tenant.name }}</h3>
                                    <p class="card-email">{{ tenant.email }}</p>
                                </div>
                                <span class="status-dot pending-dot"></span>
                            </div>
                            <div class="card-meta">
                                <div class="meta-row">
                                    <GlobeAltIcon class="meta-icon" />
                                    <span class="meta-muted">{{ tenant.domains?.[0]?.domain || 'No domain' }}</span>
                                </div>
                                <div class="meta-row">
                                    <CalendarIcon class="meta-icon" />
                                    <span class="meta-muted">{{ new Date(tenant.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span class="db-badge pending-db">Not provisioned</span>
                                <div class="card-actions">
                                    <button @click="openStoreDetails(tenant)" class="action-btn view-action"><EyeIcon /></button>
                                    <button @click="requestApproveVendor(tenant)" class="action-btn approve-action"><CheckIcon /></button>
                                    <button @click="deleteVendor(tenant.id)" class="action-btn delete-action"><TrashIcon /></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        <div class="empty-illustration"><ClockIcon class="empty-icon" /></div>
                        <p class="empty-text">No pending vendors</p>
                        <p class="empty-hint">All vendor applications have been reviewed</p>
                    </div>
                </div>

            </div>

            <StoreDetailsModal :open="showStoreDetailsModal" :tenant="selectedTenant" @update:open="showStoreDetailsModal = $event" />

            <ConfirmationModal
                v-model:open="showApproveModal"
                title="Confirm Vendor Approval"
                :description="tenantToApprove ? `Approve ${tenantToApprove.name}? This will create their tenant database and enable vendor access.` : 'Approve this vendor?'"
                confirm-text="Approve Vendor"
                cancel-text="Cancel"
                variant="destructive"
                @confirm="confirmApproveVendor"
            />

            <ConfirmationModal
                v-model:open="showDeleteModal"
                title="Delete Vendor"
                :description="tenantToDelete ? (tenantToDelete.is_approved ? `Delete ${tenantToDelete.name} and its tenant database permanently?` : `Delete ${tenantToDelete.name}? No tenant database exists yet.`) : 'Delete this vendor?'"
                :details="tenantToDelete ? (tenantToDelete.is_approved ? `Tenant ID: ${tenantToDelete.id}. This action cannot be undone.` : `Vendor ID: ${tenantToDelete.id}. The database has not been created yet.`) : 'This action cannot be undone.'"
                confirm-text="Delete Vendor"
                cancel-text="Cancel"
                variant="destructive"
                :loading="deleteProcessing"
                @confirm="confirmDeleteVendor"
                @cancel="cancelDeleteVendor"
            />
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=DM+Mono:wght@400;500&display=swap');

* { font-family: 'DM Sans', sans-serif; }

/* ── Layout ── */
.page-container {
    padding: 2rem 2.5rem;
    background: var(--background);
    min-height: 100vh;
}

/* ── Page Header ── */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.page-title-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--brand-green) 0%, color-mix(in srgb, var(--brand-green) 60%, #000) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px color-mix(in srgb, var(--primary) 35%, transparent);
    flex-shrink: 0;
}

.page-icon-svg {
    width: 26px;
    height: 26px;
    color: var(--primary-foreground);
}

.page-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--foreground);
    margin: 0 0 0.2rem 0;
    letter-spacing: -0.025em;
}

.page-subtitle {
    font-size: 0.875rem;
    color: var(--muted-foreground);
    margin: 0;
}

/* ── Stat Cards ── */
.stats-row {
    display: flex;
    gap: 0.75rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1.125rem;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    min-width: 120px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s, transform 0.2s;
}

.stat-card:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}

.stat-card-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-card-icon svg { width: 17px; height: 17px; }

.total-icon   { background: color-mix(in srgb, var(--muted-foreground) 10%, transparent); color: var(--muted-foreground); }
.active-icon  { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.pending-icon { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }

.stat-card-body { display: flex; flex-direction: column; }

.stat-card-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--foreground);
    line-height: 1;
    letter-spacing: -0.025em;
}

.stat-card-label {
    font-size: 0.68rem;
    color: var(--muted-foreground);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-top: 0.2rem;
}

/* ── Section Cards ── */
.section-card {
    background: var(--card);
    border-radius: 16px;
    border: 1px solid var(--border);
    margin-bottom: 1.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.125rem 1.5rem;
    border-bottom: 1px solid var(--border);
    background: color-mix(in srgb, var(--secondary) 50%, var(--card));
}

.section-title-wrapper {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.section-accent {
    width: 3px;
    height: 18px;
    border-radius: 2px;
    flex-shrink: 0;
}
.active-accent  { background: linear-gradient(180deg, #10b981, #34d399); }
.pending-accent { background: linear-gradient(180deg, #f59e0b, #fbbf24); }

.section-icon { width: 17px; height: 17px; }
.active-section-icon  { color: #10b981; }
.pending-section-icon { color: #f59e0b; }

.section-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--foreground);
    margin: 0;
    letter-spacing: -0.01em;
}

.section-badge {
    padding: 0.18rem 0.6rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
}
.active-badge  { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.pending-badge { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }

/* ── View Toggle ── */
.view-toggle {
    display: flex;
    gap: 0.2rem;
    background: var(--secondary);
    padding: 0.2rem;
    border-radius: 8px;
    border: 1px solid var(--border);
}

.toggle-btn {
    padding: 0.35rem;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
    color: var(--muted-foreground);
    display: flex;
    align-items: center;
    justify-content: center;
}
.toggle-btn:hover { color: var(--foreground); background: var(--accent); }
.toggle-btn.active { background: var(--card); color: var(--foreground); box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.toggle-icon { width: 15px; height: 15px; }

/* ── Table ── */
.table-container {
    overflow-x: auto;
    width: 100%;
    -webkit-overflow-scrolling: touch;
}

.vendors-table {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
}

.vendors-table thead th {
    text-align: left;
    padding: 0.7rem 1.25rem;
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--muted-foreground);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border-bottom: 1px solid var(--border);
    background: color-mix(in srgb, var(--secondary) 40%, var(--card));
    white-space: nowrap;
}

.vendors-table tbody td {
    padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.vendors-table tbody tr:last-child td { border-bottom: none; }

.table-row { transition: background 0.12s; }
.table-row:hover { background: color-mix(in srgb, var(--accent) 70%, transparent); }

/* ── Avatars ── */
.table-avatar,
.card-avatar {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
    letter-spacing: 0.03em;
}

.active-avatar {
    background: linear-gradient(135deg, rgba(16,185,129,0.18) 0%, rgba(16,185,129,0.07) 100%);
    color: #059669;
    border: 1px solid rgba(16,185,129,0.22);
}

.pending-avatar {
    background: linear-gradient(135deg, rgba(245,158,11,0.18) 0%, rgba(245,158,11,0.07) 100%);
    color: #b45309;
    border: 1px solid rgba(245,158,11,0.25);
}

.table-vendor-info { display: flex; align-items: center; gap: 0.75rem; }
.table-vendor-name { font-weight: 600; color: var(--foreground); font-size: 0.875rem; }
.table-vendor-email { font-size: 0.775rem; color: var(--muted-foreground); margin-top: 0.1rem; }

.table-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: color 0.15s;
}
.table-link:hover { color: #60a5fa; }
.link-icon { width: 13px; height: 13px; flex-shrink: 0; }

.table-muted { color: var(--muted-foreground); font-size: 0.85rem; }

.date-cell {
    color: var(--muted-foreground);
    font-size: 0.825rem;
}

/* ── DB Badges ── */
.db-badge {
    display: inline-block;
    padding: 0.2rem 0.55rem;
    border-radius: 5px;
    font-size: 0.72rem;
    font-weight: 500;
    font-family: 'DM Mono', monospace;
    background: color-mix(in srgb, var(--muted-foreground) 8%, transparent);
    color: var(--muted-foreground);
    border: 1px solid var(--border);
    white-space: nowrap;
}

.db-badge.pending-db {
    font-family: 'DM Sans', sans-serif;
    font-style: italic;
    font-size: 0.75rem;
    background: rgba(245,158,11,0.08);
    color: #d97706;
    border-color: rgba(245,158,11,0.25);
}

/* ── Action Buttons ── */
.table-actions, .card-actions {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.action-btn {
    position: relative;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    border-radius: 7px;
    cursor: pointer;
    transition: all 0.15s;
    background: var(--card);
}
.action-btn svg { width: 14px; height: 14px; }

/* Tooltip */
.action-btn[data-tip]::after {
    content: attr(data-tip);
    position: absolute;
    bottom: calc(100% + 7px);
    left: 50%;
    transform: translateX(-50%);
    background: var(--foreground);
    color: var(--background);
    font-size: 0.68rem;
    font-weight: 600;
    padding: 0.22rem 0.5rem;
    border-radius: 5px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.15s;
    z-index: 20;
}
.action-btn[data-tip]:hover::after { opacity: 1; }

.view-action    { color: #3b82f6; }
.approve-action { color: #10b981; }
.delete-action  { color: #f87171; }

.view-action:hover    { background: #3b82f6; color: #fff; border-color: #3b82f6; box-shadow: 0 3px 8px rgba(59,130,246,0.28); }
.approve-action:hover { background: #10b981; color: #fff; border-color: #10b981; box-shadow: 0 3px 8px rgba(16,185,129,0.28); }
.delete-action:hover  { background: #ef4444; color: #fff; border-color: #ef4444; box-shadow: 0 3px 8px rgba(239,68,68,0.28); }

/* ── Card Grid ── */
.vendors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(268px, 1fr));
    gap: 1rem;
    padding: 1.25rem;
}

.vendor-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 13px;
    padding: 1.25rem;
    transition: box-shadow 0.2s, transform 0.2s, border-color 0.2s;
    position: relative;
    overflow: hidden;
}

/* top color bar */
.vendor-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}
.active-card::before  { background: linear-gradient(90deg, #10b981, #6ee7b7); }
.pending-card::before { background: linear-gradient(90deg, #f59e0b, #fde68a); }

.vendor-card:hover {
    box-shadow: 0 8px 28px rgba(0,0,0,0.09);
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--muted-foreground) 25%, transparent);
}

.card-top {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.875rem;
}

.card-avatar { width: 40px; height: 40px; font-size: 0.8rem; }

.card-info { flex: 1; min-width: 0; }

.card-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--foreground);
    margin: 0 0 0.12rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: -0.01em;
}

.card-email {
    font-size: 0.75rem;
    color: var(--muted-foreground);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.active-dot  { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.18); }
.pending-dot { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.18); }

.card-meta {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    padding: 0.7rem 0.875rem;
    background: color-mix(in srgb, var(--secondary) 60%, transparent);
    border-radius: 8px;
    margin-bottom: 0.875rem;
    border: 1px solid var(--border);
}

.meta-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; }
.meta-icon { width: 12px; height: 12px; color: var(--muted-foreground); flex-shrink: 0; }
.meta-link { color: #3b82f6; text-decoration: none; font-weight: 500; }
.meta-link:hover { color: #60a5fa; }
.meta-muted { color: var(--muted-foreground); }

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* ── Empty State ── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    gap: 0.4rem;
}

.empty-illustration {
    width: 68px;
    height: 68px;
    border-radius: 18px;
    background: color-mix(in srgb, var(--border) 35%, transparent);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
}

.empty-icon { width: 32px; height: 32px; color: var(--muted-foreground); opacity: 0.45; }
.empty-text { font-size: 0.95rem; font-weight: 600; color: var(--foreground); margin: 0; }
.empty-hint { font-size: 0.825rem; color: var(--muted-foreground); margin: 0; }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .stats-row { gap: 0.5rem; }
}

@media (max-width: 768px) {
    .page-container { padding: 1.25rem 1rem; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .stats-row { width: 100%; }
    .stat-card { flex: 1; min-width: unset; }
    .section-header { flex-direction: column; align-items: flex-start; gap: 0.75rem; padding: 1rem; }
    .vendors-grid { padding: 1rem; grid-template-columns: 1fr; }
}

/* ── Dark mode ── */
:global(.dark) .active-avatar   { background: rgba(16,185,129,0.15);  color: #34d399; border-color: rgba(16,185,129,0.25); }
:global(.dark) .pending-avatar  { background: rgba(245,158,11,0.15);  color: #fbbf24; border-color: rgba(245,158,11,0.28); }
:global(.dark) .db-badge.pending-db { background: rgba(245,158,11,0.1); color: #fbbf24; border-color: rgba(245,158,11,0.28); }
:global(.dark) .active-badge    { background: rgba(16,185,129,0.15);  color: #34d399; }
:global(.dark) .pending-badge   { background: rgba(245,158,11,0.15);  color: #fbbf24; }
:global(.dark) .vendor-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,0.35); }
:global(.dark) .page-header h1 { color: var(--foreground); }
:global(.dark) .page-header p { color: var(--muted-foreground); }

:global(.dark) .stat-card {
    background: var(--card);
    border-color: var(--border);
}

:global(.dark) .stat-label { color: var(--muted-foreground); }
:global(.dark) .stat-value { color: var(--foreground); }

:global(.dark) .vendors-section {
    background: var(--card);
    border-color: var(--border);
}

:global(.dark) .section-header {
    background: var(--accent);
    border-bottom-color: var(--border);
}

:global(.dark) .section-title { color: var(--foreground); }

:global(.dark) .vendor-row { border-bottom-color: var(--border); }
:global(.dark) .vendor-row:hover { background: var(--accent); }

:global(.dark) .vendor-name { color: var(--foreground); }
:global(.dark) .vendor-metadata { color: var(--muted-foreground); }

:global(.dark) .status-pill.active { background: rgba(16, 185, 129, 0.2); color: #34d399; }
:global(.dark) .status-pill.pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

:global(.dark) .approve-btn {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
}
:global(.dark) .approve-btn:hover {
    background: rgba(16, 185, 129, 0.35);
}

:global(.dark) .empty-state h3 { color: var(--foreground); }
:global(.dark) .empty-state p { color: var(--muted-foreground); }
</style>