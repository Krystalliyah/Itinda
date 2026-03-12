<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
import { useSidebar } from '@/composables/useSidebar';
import StoreDetailsModal from '@/components/StoreDetailsModal.vue';
import ConfirmationModal from '@/components/ui/modal/ConfirmationModal.vue';
import {
    CheckCircleIcon,
    ClockIcon,
    TrashIcon,
    CheckIcon,
    PlusIcon,
    BuildingStorefrontIcon,
    GlobeAltIcon,
    XMarkIcon,
    CalendarIcon,
    Squares2X2Icon,
    TableCellsIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline';

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
const showCreateForm = ref(false);
const viewMode = ref<'cards' | 'table'>('table');

const showStoreDetailsModal = ref(false);
const selectedTenant = ref<Tenant | null>(null);

const showApproveModal = ref(false);
const tenantToApprove = ref<Tenant | null>(null);

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

const form = useForm({
    name: '',
    email: '',
    subdomain: '',
});

const submit = () => {
    form.post('/admin/vendors', {
        onSuccess: () => {
            form.reset();
            showCreateForm.value = false;
        },
    });
};

const deleteVendor = (id: string) => {
    if (confirm('Delete this vendor and its database?')) {
        router.delete(`/admin/vendors/${id}`);
    }
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
                <div class="page-header">
                    <div class="header-left">
                        <div class="page-title-wrapper">
                            <div class="page-icon">
                                <BuildingStorefrontIcon />
                            </div>
                            <div>
                                <h1 class="page-title">Vendor Management</h1>
                                <p class="page-subtitle">Manage vendor stores and databases</p>
                            </div>
                        </div>
                    </div>

                    <div class="header-right">
                        <div class="stats-row">
                            <div class="stat-box stat-total">
                                <BuildingStorefrontIcon class="stat-icon" />
                                <div class="stat-info">
                                    <span class="stat-value">{{ totalVendors }}</span>
                                    <span class="stat-label">Total</span>
                                </div>
                            </div>
                            <div class="stat-box stat-active">
                                <CheckCircleIcon class="stat-icon" />
                                <div class="stat-info">
                                    <span class="stat-value">{{ approvedVendors.length }}</span>
                                    <span class="stat-label">Active</span>
                                </div>
                            </div>
                            <div class="stat-box stat-pending">
                                <ClockIcon class="stat-icon" />
                                <div class="stat-info">
                                    <span class="stat-value">{{ pendingVendors.length }}</span>
                                    <span class="stat-label">Pending</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title-wrapper">
                            <CheckCircleIcon class="section-icon active-icon" />
                            <h2 class="section-title">Active Vendors</h2>
                            <span class="section-badge active-badge">{{ approvedVendors.length }}</span>
                        </div>

                        <div class="view-toggle">
                            <button
                                @click="viewMode = 'table'"
                                :class="['toggle-btn', { active: viewMode === 'table' }]"
                                title="Table View"
                            >
                                <TableCellsIcon class="toggle-icon" />
                            </button>
                            <button
                                @click="viewMode = 'cards'"
                                :class="['toggle-btn', { active: viewMode === 'cards' }]"
                                title="Card View"
                            >
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
                                <tr v-for="tenant in approvedVendors" :key="tenant.id">
                                    <td>
                                        <div class="table-vendor-info">
                                            <div class="table-avatar">
                                                <BuildingStorefrontIcon />
                                            </div>
                                            <div>
                                                <div class="table-vendor-name">{{ tenant.name }}</div>
                                                <div class="table-vendor-email">{{ tenant.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a
                                            v-if="tenant.domains?.length"
                                            :href="`http://${tenant.domains[0]?.domain}`"
                                            target="_blank"
                                            class="table-link"
                                        >
                                            <GlobeAltIcon class="link-icon" />
                                            {{ tenant.domains[0]?.domain }}
                                        </a>
                                        <span v-else class="table-muted">No domain</span>
                                    </td>
                                    <td>
                                        <span class="table-db-badge">tenant{{ tenant.id }}</span>
                                    </td>
                                    <td class="table-muted">
                                        {{ new Date(tenant.created_at).toLocaleDateString() }}
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button
                                                @click="openStoreDetails(tenant)"
                                                class="table-action-btn view"
                                                title="View store details"
                                            >
                                                <EyeIcon class="action-icon" />
                                            </button>

                                            <button @click="deleteVendor(tenant.id)" class="table-action-btn delete">
                                                <TrashIcon class="action-icon" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else-if="approvedVendors.length > 0 && viewMode === 'cards'" class="vendors-grid">
                        <div v-for="tenant in approvedVendors" :key="tenant.id" class="vendor-card active-card">
                            <div class="card-header-row">
                                <div class="card-avatar">
                                    <BuildingStorefrontIcon />
                                </div>
                                <div class="card-info">
                                    <h3 class="card-name">{{ tenant.name }}</h3>
                                    <p class="card-email">{{ tenant.email }}</p>
                                </div>
                            </div>

                            <div class="card-details">
                                <div class="detail-row">
                                    <GlobeAltIcon class="detail-icon" />
                                    <a
                                        v-if="tenant.domains?.length"
                                        :href="`http://${tenant.domains[0]?.domain}`"
                                        target="_blank"
                                        class="detail-link"
                                    >
                                        {{ tenant.domains[0]?.domain }}
                                    </a>
                                    <span v-else class="detail-text">No domain</span>
                                </div>
                                <div class="detail-row">
                                    <CalendarIcon class="detail-icon" />
                                    <span class="detail-text">{{ new Date(tenant.created_at).toLocaleDateString() }}</span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <span class="db-badge-small">tenant{{ tenant.id }}</span>
                                <div class="card-actions">
                                    <button
                                        @click="openStoreDetails(tenant)"
                                        class="card-action-btn view-btn"
                                        title="View store details"
                                    >
                                        <EyeIcon class="action-icon" />
                                    </button>

                                    <button @click="deleteVendor(tenant.id)" class="card-action-btn delete-btn">
                                        <TrashIcon class="action-icon" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        <CheckCircleIcon class="empty-icon" />
                        <p class="empty-text">No active vendors yet</p>
                        <p class="empty-hint">Approve pending vendors below to get started</p>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title-wrapper">
                            <ClockIcon class="section-icon pending-icon" />
                            <h2 class="section-title">Pending Approval</h2>
                            <span class="section-badge pending-badge">{{ pendingVendors.length }}</span>
                        </div>

                        <div class="view-toggle">
                            <button
                                @click="viewMode = 'table'"
                                :class="['toggle-btn', { active: viewMode === 'table' }]"
                                title="Table View"
                            >
                                <TableCellsIcon class="toggle-icon" />
                            </button>
                            <button
                                @click="viewMode = 'cards'"
                                :class="['toggle-btn', { active: viewMode === 'cards' }]"
                                title="Card View"
                            >
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
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="tenant in pendingVendors" :key="tenant.id" class="pending-row">
                                    <td>
                                        <div class="table-vendor-info">
                                            <div class="table-avatar pending">
                                                <BuildingStorefrontIcon />
                                            </div>
                                            <div>
                                                <div class="table-vendor-name">{{ tenant.name }}</div>
                                                <div class="table-vendor-email">{{ tenant.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="table-muted">{{ tenant.domains?.[0]?.domain || 'No domain' }}</span>
                                    </td>
                                    <td class="table-muted">
                                        {{ new Date(tenant.created_at).toLocaleDateString() }}
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button
                                                @click="openStoreDetails(tenant)"
                                                class="table-action-btn view"
                                                title="View store details"
                                            >
                                                <EyeIcon class="action-icon" />
                                            </button>

                                            <button
                                                @click="requestApproveVendor(tenant)"
                                                class="table-action-btn approve"
                                                title="Approve vendor"
                                            >
                                                <CheckIcon class="action-icon" />
                                            </button>

                                            <button @click="deleteVendor(tenant.id)" class="table-action-btn delete">
                                                <TrashIcon class="action-icon" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else-if="pendingVendors.length > 0 && viewMode === 'cards'" class="vendors-grid">
                        <div v-for="tenant in pendingVendors" :key="tenant.id" class="vendor-card pending-card">
                            <div class="card-header-row">
                                <div class="card-avatar pending">
                                    <BuildingStorefrontIcon />
                                </div>
                                <div class="card-info">
                                    <h3 class="card-name">{{ tenant.name }}</h3>
                                    <p class="card-email">{{ tenant.email }}</p>
                                </div>
                            </div>

                            <div class="card-details">
                                <div class="detail-row">
                                    <GlobeAltIcon class="detail-icon" />
                                    <span class="detail-text">{{ tenant.domains?.[0]?.domain || 'No domain' }}</span>
                                </div>
                                <div class="detail-row">
                                    <CalendarIcon class="detail-icon" />
                                    <span class="detail-text">{{ new Date(tenant.created_at).toLocaleDateString() }}</span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="card-actions">
                                    <button
                                        @click="openStoreDetails(tenant)"
                                        class="card-action-btn view-btn"
                                        title="View store details"
                                    >
                                        <EyeIcon class="action-icon" />
                                    </button>

                                    <button
                                        @click="requestApproveVendor(tenant)"
                                        class="card-action-btn approve-btn"
                                        title="Approve vendor"
                                    >
                                        <CheckIcon class="action-icon" />
                                    </button>

                                    <button @click="deleteVendor(tenant.id)" class="card-action-btn delete-btn">
                                        <TrashIcon class="action-icon" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        <ClockIcon class="empty-icon" />
                        <p class="empty-text">No pending vendors</p>
                        <p class="empty-hint">All vendors have been reviewed</p>
                    </div>
                </div>
            </div>

            <StoreDetailsModal
                :open="showStoreDetailsModal"
                :tenant="selectedTenant"
                @update:open="showStoreDetailsModal = $event"
            />

            <ConfirmationModal
                v-model:open="showApproveModal"
                title="Confirm Vendor Approval"
                :description="
                    tenantToApprove
                        ? `Approve ${tenantToApprove.name}? This will create and prepare their tenant database.`
                        : 'Approve this vendor? This will create and prepare their tenant database.'
                        "
                    confirm-text="Approve Vendor"
                    cancel-text="Cancel"
                    variant="destructive"
                    @confirm="confirmApproveVendor"
            />
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.page-container {
    padding: 2rem;
    background: #f8fafc;
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.header-left {
    flex: 1;
    min-width: 300px;
}

.page-title-wrapper {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.page-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #475569;
    flex-shrink: 0;
}

.page-icon svg {
    width: 32px;
    height: 32px;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.25rem 0;
}

.page-subtitle {
    font-size: 0.95rem;
    color: #64748b;
    margin: 0;
}

.header-right {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.stats-row {
    display: flex;
    gap: 0.75rem;
}

.stat-box {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    background: white;
    border: 1px solid #e2e8f0;
}

.stat-icon {
    width: 20px;
    height: 20px;
    color: #64748b;
    flex-shrink: 0;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #0f172a;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: #1e293b;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: white;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    border-color: #94a3b8;
    color: #334155;
}

.btn-icon {
    width: 18px;
    height: 18px;
}

.create-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.create-header {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.create-icon {
    width: 24px;
    height: 24px;
    color: #64748b;
    flex-shrink: 0;
}

.create-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.25rem 0;
}

.create-subtitle {
    font-size: 0.9rem;
    color: #64748b;
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.field-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
}

.field-input {
    padding: 0.75rem 1rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #0f172a;
    background: #f8fafc;
    transition: all 0.2s;
}

.field-input:focus {
    outline: none;
    border-color: #10b981;
    background: white;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.subdomain-preview {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.85rem;
    color: #475569;
    font-weight: 500;
}

.preview-icon {
    width: 16px;
    height: 16px;
    color: #64748b;
}

.field-error {
    font-size: 0.8rem;
    color: #ef4444;
    font-weight: 500;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.section-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.section-title-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-icon {
    width: 20px;
    height: 20px;
    color: #64748b;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.section-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    background: #f1f5f9;
    color: #475569;
}

.view-toggle {
    display: flex;
    gap: 0.5rem;
    background: #f8fafc;
    padding: 0.25rem;
    border-radius: 8px;
}

.toggle-btn {
    padding: 0.5rem;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    color: #94a3b8;
}

.toggle-btn:hover {
    background: white;
    color: #475569;
}

.toggle-btn.active {
    background: white;
    color: #0f172a;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.toggle-icon {
    width: 20px;
    height: 20px;
}

.table-container {
    overflow-x: auto;
}

.vendors-table {
    width: 100%;
    border-collapse: collapse;
}

.vendors-table thead th {
    text-align: left;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.vendors-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.vendors-table tbody tr:hover {
    background: #f8fafc;
}

.pending-row {
    background: #fffbeb;
}

.pending-row:hover {
    background: #fef9e7 !important;
}

.table-vendor-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.table-avatar {
    width: 40px;
    height: 40px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    flex-shrink: 0;
}

.table-avatar.pending {
    background: #fffbeb;
    border-color: #fde68a;
}

.table-avatar svg {
    width: 20px;
    height: 20px;
}

.table-vendor-name {
    font-weight: 600;
    color: #0f172a;
    font-size: 0.95rem;
}

.table-vendor-email {
    font-size: 0.85rem;
    color: #64748b;
}

.table-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
}

.table-link:hover {
    color: #2563eb;
    text-decoration: underline;
}

.link-icon {
    width: 16px;
    height: 16px;
}

.table-muted {
    color: #94a3b8;
    font-size: 0.9rem;
}

.table-db-badge {
    padding: 0.25rem 0.75rem;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}

.table-actions {
    display: flex;
    gap: 0.5rem;
}

.table-action-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.table-action-btn.view {
    background: #f8fafc;
    color: #3b82f6;
    border: 1px solid #e2e8f0;
}

.table-action-btn.view:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.table-action-btn.approve {
    background: #f8fafc;
    color: #10b981;
    border: 1px solid #e2e8f0;
}

.table-action-btn.approve:hover {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.table-action-btn.delete {
    background: #f8fafc;
    color: #ef4444;
    border: 1px solid #e2e8f0;
}

.table-action-btn.delete:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.action-icon {
    width: 16px;
    height: 16px;
}

.vendors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.vendor-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem;
    transition: all 0.2s;
}

.vendor-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.active-card {
    border-left: 2px solid #64748b;
}

.pending-card {
    border-left: 2px solid #f59e0b;
    background: #fffbeb;
}

.card-header-row {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.card-avatar {
    width: 40px;
    height: 40px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    flex-shrink: 0;
}

.card-avatar.pending {
    background: #fef3c7;
    border-color: #fde68a;
}

.card-avatar svg {
    width: 20px;
    height: 20px;
}

.card-info {
    flex: 1;
    min-width: 0;
}

.card-name {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 0.25rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-email {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: white;
    border-radius: 6px;
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
}

.detail-icon {
    width: 14px;
    height: 14px;
    color: #94a3b8;
    flex-shrink: 0;
}

.detail-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.detail-link:hover {
    color: #2563eb;
    text-decoration: underline;
}

.detail-text {
    color: #64748b;
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.card-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.db-badge-small {
    padding: 0.25rem 0.5rem;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}

.card-action-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.view-btn {
    background: #f8fafc;
    color: #3b82f6;
    border: 1px solid #e2e8f0;
}

.view-btn:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.approve-btn {
    background: #f8fafc;
    color: #10b981;
    border: 1px solid #e2e8f0;
}

.approve-btn:hover {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.delete-btn {
    background: #f8fafc;
    color: #ef4444;
    border: 1px solid #e2e8f0;
}

.delete-btn:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    width: 64px;
    height: 64px;
    color: #cbd5e1;
    margin: 0 auto 1rem;
}

.empty-text {
    font-size: 1.1rem;
    font-weight: 600;
    color: #475569;
    margin: 0 0 0.5rem 0;
}

.empty-hint {
    font-size: 0.9rem;
    color: #94a3b8;
    margin: 0;
}

.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.2s ease-in;
}

.slide-fade-enter-from {
    transform: translateY(-20px);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: translateY(-10px);
    opacity: 0;
}

@media (max-width: 1024px) {
    .stats-row {
        display: none;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
    }

    .header-right {
        width: 100%;
        justify-content: stretch;
    }

    .btn-primary {
        flex: 1;
        justify-content: center;
    }

    .vendors-grid {
        grid-template-columns: 1fr;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>