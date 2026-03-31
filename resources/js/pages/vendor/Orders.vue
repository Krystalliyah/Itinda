<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'completed' | 'cancelled';

interface OrderItem { product_name: string; quantity: number; unit_price: number; }
interface Order {
    id: number; order_number: string;
    status: OrderStatus; total_amount: number; is_paid: boolean;
    placed_at: string; created_at: string; items: OrderItem[];
}

interface Stats {
    pending: number; active: number; completed: number; revenue: number;
}

const props = defineProps<{
    orders: Order[];
    stats: Stats;
}>();

const STATUS_CONFIG: Record<OrderStatus, { label:string; color:string; bg:string; next:OrderStatus|null }> = {
    pending:   { label:'Pending',   color:'#b45309', bg:'#fffbeb', next:'confirmed' },
    confirmed: { label:'Confirmed', color:'#1d4ed8', bg:'#eff6ff', next:'preparing' },
    preparing: { label:'Preparing', color:'#7c3aed', bg:'#f5f3ff', next:'ready'     },
    ready:     { label:'Ready',     color:'#059669', bg:'#ecfdf5', next:'completed'  },
    completed: { label:'Completed', color:'#245c4a', bg:'#f0f9f6', next:null         },
    cancelled: { label:'Cancelled', color:'#ef4444', bg:'#fef2f2', next:null         },
};
const STATUS_ORDER: OrderStatus[] = ['pending','confirmed','preparing','ready','completed','cancelled'];

const searchQuery   = ref('');
const filterStatus  = ref<OrderStatus | 'all'>('all');
const filterPayment = ref<'all'|'paid'|'unpaid'>('all');
const detailOrder   = ref<Order | null>(null);
const confirmCancel = ref<Order | null>(null);

const filtered = computed(() => {
    let list = props.orders;
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(o => o.order_number.toLowerCase().includes(q));
    }
    if (filterStatus.value !== 'all')     list = list.filter(o => o.status === filterStatus.value);
    if (filterPayment.value === 'paid')   list = list.filter(o => o.is_paid);
    if (filterPayment.value === 'unpaid') list = list.filter(o => !o.is_paid);
    return list;
});

const statusCountMap = computed(() => {
    const m: Record<string,number> = { all: props.orders.length };
    STATUS_ORDER.forEach(s => { m[s] = props.orders.filter(o => o.status === s).length; });
    return m;
});

function advanceStatus(order: Order) {
    router.post(`/vendor/orders/${order.id}/advance`, {}, {
        onSuccess: () => { if (detailOrder.value?.id === order.id) detailOrder.value = null; },
    });
}

function cancelOrder(order: Order) {
    router.post(`/vendor/orders/${order.id}/cancel`, {}, {
        onSuccess: () => { confirmCancel.value = null; if (detailOrder.value?.id === order.id) detailOrder.value = null; },
    });
}

function statusBadgeClass(status: OrderStatus): string {
    return `status-badge--${status}`;
}

function formatPrice(v: number | null | undefined) { return '₱' + (v ?? 0).toLocaleString('en-PH', { minimumFractionDigits:2 }); }
function formatDate(dt: string | null | undefined)  { if (!dt) return '—'; return new Date(dt).toLocaleString('en-PH', { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }); }
</script>

<template>
    <Head title="Orders" />
    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="vendor">
            <VendorNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="ord-page">

                <!-- Page Header -->
                <div class="ord-header">
                    <div>
                        <h1 class="ord-title">Order Management</h1>
                        <p class="ord-sub">View and process customer pre-orders in real-time</p>
                    </div>
                    <button class="btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span class="btn-label">Export</span>
                    </button>
                </div>

                <!-- Stat Cards -->
                <div class="stat-grid">
                    <div class="stat-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="stat-icon bg-amber-50 text-amber-600 dark:bg-amber-100/15 dark:text-amber-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                        <div><p class="stat-label">Awaiting Action</p><p class="stat-value">{{ stats.pending }}</p></div>
                    </div>
                    <div class="stat-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="stat-icon bg-blue-50 text-blue-600 dark:bg-amber-100/15 dark:text-amber-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
                        <div><p class="stat-label">In Progress</p><p class="stat-value">{{ stats.active }}</p></div>
                    </div>
                    <div class="stat-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="stat-icon bg-emerald-50 text-emerald-600 dark:bg-amber-100/15 dark:text-amber-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                        <div><p class="stat-label">Completed</p><p class="stat-value">{{ stats.completed }}</p></div>
                    </div>
                    <div class="stat-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="stat-icon bg-amber-50 text-amber-600 dark:bg-amber-100/15 dark:text-amber-200"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                        <div><p class="stat-label">Revenue Collected</p><p class="stat-value">{{ formatPrice(stats.revenue) }}</p></div>
                    </div>
                </div>

                <!-- Status Tab Strip -->
                <div class="tab-strip-wrap">
                    <div class="tab-strip">
                        <button v-for="s in ['all', ...STATUS_ORDER]" :key="s" class="tab-btn" :class="{'tab-btn--active': filterStatus === s}" @click="filterStatus = s as any">
                            {{ s === 'all' ? 'All' : STATUS_CONFIG[s as OrderStatus].label }}
                            <span class="tab-count">{{ statusCountMap[s] }}</span>
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-bar">
                    <div class="search-wrap">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input v-model="searchQuery" type="text" placeholder="Search order # or customer…" class="search-input" />
                    </div>
                    <div class="filter-row">
                        <select v-model="filterPayment" class="filter-select">
                            <option value="all">All Payments</option>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                        <span class="result-count">{{ filtered.length }} order{{ filtered.length !== 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="table-card desktop-only bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <table class="ord-table">
                        <thead>
                            <tr>
                                <th>Order</th><th>Placed At</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filtered.length === 0">
                                <td colspan="7" class="empty-state">
                                    <div class="empty-inner">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <p>No orders match your filters</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="order in filtered" :key="order.id">
                                <td>
                                    <p class="order-num">{{ order.order_number }}</p>
                                    <p class="order-time">{{ formatDate(order.created_at) }}</p>
                                </td>
                                <td>
                                    <p class="order-time">{{ formatDate(order.placed_at) }}</p>
                                </td>
                                <td>
                                    <div class="items-preview">
                                        <span v-for="item in order.items.slice(0,2)" :key="item.product_name" class="item-chip bg-gray-100 text-gray-700 dark:bg-slate-900 dark:text-slate-200">
                                            {{ item.quantity }}× {{ item.product_name }}
                                        </span>
                                        <span v-if="order.items.length > 2" class="item-chip item-chip--more bg-[#f0f9f6] text-[#245c4a]">
                                            +{{ order.items.length - 2 }} more
                                        </span>
                                    </div>
                                </td>
                                <td class="total-cell">{{ formatPrice(order.total_amount) }}</td>
                                <td>
                                    <span class="payment-badge" :class="order.is_paid ? 'payment-badge--paid' : 'payment-badge--unpaid'">
                                        {{ order.is_paid ? 'Paid' : 'Unpaid' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge" :class="statusBadgeClass(order.status)">
                                        {{ STATUS_CONFIG[order.status].label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-row">
                                        <button class="action-btn" title="View Details" @click="detailOrder = order"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                                        <button v-if="STATUS_CONFIG[order.status].next" class="action-btn action-btn--advance" :title="`Mark as ${STATUS_CONFIG[STATUS_CONFIG[order.status].next!].label}`" @click="advanceStatus(order)"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></button>
                                        <button v-if="!['completed','cancelled'].includes(order.status)" class="action-btn action-btn--cancel" title="Cancel Order" @click="confirmCancel = order"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Order Cards -->
                <div class="mobile-list mobile-only">
                    <div v-if="filtered.length === 0" class="empty-state-mobile">
                        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <p>No orders match your filters</p>
                    </div>

                    <div v-for="order in filtered" :key="order.id" class="oc bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="oc-head">
                            <div>
                                <p class="order-num">{{ order.order_number }}</p>
                                <p class="order-time">{{ formatDate(order.created_at) }}</p>
                            </div>
                            <div class="oc-badges">
                                <span class="status-badge" :class="statusBadgeClass(order.status)">{{ STATUS_CONFIG[order.status].label }}</span>
                                <span class="payment-badge" :class="order.is_paid ? 'payment-badge--paid' : 'payment-badge--unpaid'">{{ order.is_paid ? 'Paid' : 'Unpaid' }}</span>
                            </div>
                        </div>

                        <div class="oc-meta">
                            <div class="oc-meta-item">
                                <span class="oc-label">Placed</span>
                                <span class="oc-value">{{ formatDate(order.placed_at) }}</span>
                            </div>
                            <div class="oc-meta-item">
                                <span class="oc-label">Total</span>
                                <span class="oc-value oc-value--price">{{ formatPrice(order.total_amount) }}</span>
                            </div>
                        </div>

                        <div class="oc-items">
                            <span v-for="item in order.items.slice(0,2)" :key="item.product_name" class="item-chip">{{ item.quantity }}× {{ item.product_name }}</span>
                            <span v-if="order.items.length > 2" class="item-chip item-chip--more">+{{ order.items.length - 2 }} more</span>
                        </div>

                        <div class="oc-actions">
                            <button class="oc-view-btn" @click="detailOrder = order">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View Details
                            </button>
                            <div class="oc-action-btns">
                                <button v-if="STATUS_CONFIG[order.status].next" class="oc-advance-btn" @click="advanceStatus(order)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Mark {{ STATUS_CONFIG[STATUS_CONFIG[order.status].next!].label }}
                                </button>
                                <button v-if="!['completed','cancelled'].includes(order.status)" class="action-btn action-btn--cancel" @click="confirmCancel = order">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Order Detail Modal -->
    <Teleport to="body">
        <div v-if="detailOrder" class="modal-backdrop" @click.self="detailOrder = null">
            <div class="modal modal--detail">
                <div class="modal-header">
                    <div><h2 class="modal-title">{{ detailOrder.order_number }}</h2><p class="modal-sub">Placed {{ formatDate(detailOrder.created_at) }}</p></div>
                    <button class="modal-close" @click="detailOrder = null"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                </div>

                <!-- Pipeline -->
                <div class="pipeline">
                    <template v-for="(s, idx) in ['pending','confirmed','preparing','ready','completed'] as OrderStatus[]" :key="s">
                        <div class="pipe-step" :class="{'pipe-step--done': STATUS_ORDER.indexOf(detailOrder.status) > idx || detailOrder.status === s,'pipe-step--current': detailOrder.status === s,'pipe-step--cancelled': detailOrder.status === 'cancelled'}">
                            <div class="pipe-dot"></div>
                            <span class="pipe-label">{{ STATUS_CONFIG[s].label }}</span>
                        </div>
                        <div v-if="idx < 4" class="pipe-line" :class="{'pipe-line--done': STATUS_ORDER.indexOf(detailOrder.status) > idx && detailOrder.status !== 'cancelled'}"></div>
                    </template>
                </div>

                <div class="detail-grid">
                    <div class="detail-section">
                        <p class="section-label">Placed At</p>
                        <p class="detail-value">{{ formatDate(detailOrder.placed_at) }}</p>
                    </div>
                    <div class="detail-section">
                        <p class="section-label">Payment</p>
                        <span class="payment-badge" :class="detailOrder.is_paid?'payment-badge--paid':'payment-badge--unpaid'">{{ detailOrder.is_paid ? 'Paid' : 'Unpaid' }}</span>
                    </div>
                </div>

                <div class="items-table-wrap">
                    <p class="section-label" style="margin-bottom:10px">Order Items</p>
                    <table class="items-table">
                        <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            <tr v-for="item in detailOrder.items" :key="item.product_name">
                                <td>{{ item.product_name }}</td>
                                <td>{{ item.quantity }}</td>
                                <td>{{ formatPrice(item.unit_price) }}</td>
                                <td>{{ formatPrice(item.quantity * item.unit_price) }}</td>
                            </tr>
                        </tbody>
                        <tfoot><tr><td colspan="3" class="total-label">Total</td><td class="total-amount">{{ formatPrice(detailOrder.total_amount) }}</td></tr></tfoot>
                    </table>
                </div>

                <div class="modal-footer">
                    <button class="btn-ghost" @click="detailOrder = null">Close</button>
                    <button v-if="STATUS_CONFIG[detailOrder.status].next" class="btn-primary" @click="advanceStatus(detailOrder)">
                        Mark as {{ STATUS_CONFIG[STATUS_CONFIG[detailOrder.status].next!].label }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Cancel Confirm Modal -->
    <Teleport to="body">
        <div v-if="confirmCancel" class="modal-backdrop" @click.self="confirmCancel = null">
            <div class="modal modal--confirm">
                <div class="confirm-icon"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                <h2 class="confirm-title">Cancel Order?</h2>
                <p class="confirm-body">Are you sure you want to cancel <strong>{{ confirmCancel.order_number }}</strong>? This cannot be undone.</p>
                <div class="modal-footer">
                    <button class="btn-ghost" @click="confirmCancel = null">Keep Order</button>
                    <button class="btn-danger" @click="cancelOrder(confirmCancel!)">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* ─── Base ─── */
.ord-page {
    padding: 20px 16px;
    max-width: 1400px;
    /* NOTE: no container-type here — it creates a BFC that kills overflow-x scroll on children */
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}

/* ─── Page Header ─── */
.ord-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:24px; }
.ord-title  { font-size:1.6rem; font-weight:600; color:#245c4a; margin:0 0 4px; line-height:1.2; }
.ord-sub    { font-size:0.875rem; color:#737373; margin:0; }

/* ─── Buttons ─── */
.btn-primary {
    display:inline-flex; align-items:center; gap:7px;
    background:#1B4D3E; color:#fff; border:none;
    padding:9px 18px; border-radius:8px;
    font-size:0.875rem; font-weight:500; cursor:pointer;
    transition:background 0.18s; font-family:inherit; flex-shrink:0;
}
.btn-primary:hover { background:#245c4a; }
.btn-outline {
    display:inline-flex; align-items:center; gap:7px; flex-shrink:0;
    background:#fff; color:#245c4a; border:1px solid #245c4a;
    padding:9px 18px; border-radius:8px;
    font-size:0.875rem; font-weight:500; cursor:pointer;
    transition:background 0.15s; font-family:inherit;
}
.btn-outline:hover { background:#f0f9f6; }
.btn-ghost {
    background:transparent; border:1px solid #e5e5e5; color:#737373;
    padding:9px 18px; border-radius:8px;
    font-size:0.875rem; font-weight:500; cursor:pointer;
    transition:border-color 0.18s,color 0.18s; font-family:inherit;
}
.btn-ghost:hover { border-color:#c5a059; color:#1B4D3E; }
.btn-danger {
    background:#ef4444; color:#fff; border:none;
    padding:9px 18px; border-radius:8px;
    font-size:0.875rem; font-weight:500; cursor:pointer;
    transition:background 0.18s; font-family:inherit;
}
.btn-danger:hover { background:#dc2626; }

/* ─── Stat Grid ─── */
.stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
.stat-card { border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
.stat-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.stat-label { font-size:0.75rem; color:#737373; margin:0 0 2px; font-weight:500; }
.stat-value { font-size:1.3rem; font-weight:700; color:#171717; margin:0; }

/* ─── Tab Strip ─── */
.tab-strip-wrap {
    /* Bleed out to page edges so the strip can scroll truly edge-to-edge */
    overflow-x: auto;
    margin: 0 -16px 20px;
    padding: 0 16px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.tab-strip-wrap::-webkit-scrollbar { display: none; }
.tab-strip {
    display: flex;
    gap: 6px;
    background: #f5f5f5;
    border-radius: 12px;
    padding: 6px;
    width: max-content;
    min-width: calc(100% - 0px);
}
.tab-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 18px; border-radius: 8px; border: none;
    background: transparent; color: #737373;
    font-size: 0.83rem; font-weight: 500; cursor: pointer;
    transition: background 0.15s, color 0.15s; font-family: inherit; white-space: nowrap;
}
.tab-btn:hover { background: #fff; color: #245c4a; }
.tab-btn--active { background: #fff; color: #245c4a; box-shadow: 0 1px 3px rgba(0,0,0,.1); font-weight: 600; }
.tab-count { background: #EDEDED; color: #737373; padding: 2px 8px; border-radius: 20px; font-size: 0.71rem; font-weight: 600; }
.tab-btn--active .tab-count { background: #f0f9f6; color: #245c4a; }

/* Dark mode overrides */
.dark .ord-page .tab-strip { background: var(--secondary); }
.dark .ord-page .tab-btn { color: var(--muted-foreground); background: transparent; }
.dark .ord-page .tab-btn:hover { background: var(--accent); color: var(--foreground); }
.dark .ord-page .tab-btn--active { background: var(--card); color: var(--foreground); box-shadow: 0 1px 3px rgba(0,0,0,.4); }
.dark .ord-page .tab-count { background: var(--secondary); color: var(--muted-foreground); }
.dark .ord-page .tab-btn--active .tab-count { background: rgba(212,183,106,0.15); color: var(--brand-gold); }

/* Page shell */
.dark .ord-page { background: var(--background); }

/* Header */
.dark .ord-title { color: var(--foreground); }
.dark .ord-sub   { color: var(--muted-foreground); }

/* Buttons */
.dark .btn-outline { background: var(--card); border-color: var(--brand-green); color: var(--foreground); }
.dark .btn-outline:hover { background: var(--accent); }
.dark .btn-ghost { border-color: var(--border); color: var(--muted-foreground); background: transparent; }
.dark .btn-ghost:hover { border-color: var(--brand-gold); color: var(--foreground); }

/* Stat cards */
.dark .stat-card  { background: var(--card); border: 1px solid var(--border); box-shadow: none; }
.dark .stat-label { color: var(--muted-foreground); }
.dark .stat-value { color: var(--foreground); }

/* Filter bar */
.dark .search-input  { background: var(--input); border-color: var(--border); color: var(--foreground); }
.dark .search-input::placeholder { color: var(--muted-foreground); }
.dark .search-input:focus { border-color: var(--ring); }
.dark .search-icon   { color: var(--muted-foreground); }
.dark .filter-select { background: var(--input); border-color: var(--border); color: var(--foreground); }
.dark .filter-select:focus { border-color: var(--ring); }
.dark .result-count  { color: var(--muted-foreground); }

/* Desktop table */
.dark .table-card { background: var(--card); box-shadow: none; }
.dark .ord-table thead tr { background: var(--secondary); border-bottom-color: var(--border); }
.dark .ord-table th { color: var(--muted-foreground); }
.dark .ord-table tbody tr { border-bottom-color: var(--border); }
.dark .ord-table tbody tr:hover { background: var(--accent); }
.dark .ord-table td { color: var(--foreground); }

/* Order number / time */
.dark .order-num  { color: var(--brand-gold); }
.dark .order-time { color: var(--muted-foreground); }

/* Customer cell */
.dark .customer-name  { color: var(--foreground); }
.dark .customer-phone { color: var(--muted-foreground); }

/* Item chips */
.dark .item-chip { background: var(--secondary); color: var(--foreground); }
.dark .item-chip--more { background: rgba(27,77,62,0.25); color: #6ee7b7; }

/* Pickup / total */
.dark .pickup-cell { color: var(--muted-foreground); }
.dark .total-cell  { color: var(--foreground); }

/* Status badges — colors defined with .status-badge--{status} below */
.dark .payment-badge--paid   { background: rgba(6,95,70,0.35);   color: #6ee7b7; }
.dark .payment-badge--unpaid { background: rgba(127,29,29,0.35); color: #fca5a5; }

/* Action buttons */
.dark .action-btn { background: var(--secondary); border-color: var(--border); color: var(--muted-foreground); }
.dark .action-btn:hover          { border-color: var(--brand-green); color: var(--foreground); background: var(--accent); }
.dark .action-btn--advance:hover { border-color: #10b981; color: #6ee7b7; background: rgba(6,95,70,0.2); }
.dark .action-btn--cancel:hover  { border-color: #ef4444; color: #f87171; background: rgba(127,29,29,0.2); }

/* Mobile order cards */
.dark .oc { background: var(--card); border: 1px solid var(--border); box-shadow: none; }
.dark .oc-meta { background: var(--secondary); }
.dark .oc-label { color: var(--muted-foreground); }
.dark .oc-value { color: var(--foreground); }
.dark .oc-value--price { color: var(--brand-gold); }
.dark .oc-items .item-chip { background: var(--secondary); color: var(--foreground); }
.dark .oc-view-btn { background: var(--secondary); color: var(--muted-foreground); }
.dark .oc-view-btn:hover { background: var(--accent); color: var(--foreground); }
.dark .oc-advance-btn { background: rgba(6,95,70,0.25); color: #6ee7b7; border-color: rgba(6,95,70,0.4); }
.dark .oc-advance-btn:hover { background: rgba(6,95,70,0.4); }

/* Empty states */
.dark .empty-inner { color: var(--muted-foreground); }
.dark .empty-state-mobile { color: var(--muted-foreground); }

/* Modal */
.dark .modal { background: var(--card); }
.dark .modal-title  { color: var(--foreground); }
.dark .modal-sub    { color: var(--muted-foreground); }
.dark .modal-close  { color: var(--muted-foreground); }
.dark .modal-close:hover { color: #f87171; }

/* Pipeline */
.dark .pipeline { background: var(--secondary); }
.dark .pipe-line { background: var(--border); }
.dark .pipe-line--done { background: var(--brand-green); }
.dark .pipe-label { color: var(--muted-foreground); }
.dark .pipe-step--done .pipe-label    { color: #6ee7b7; }
.dark .pipe-step--current .pipe-label { color: var(--brand-gold); }
.dark .pipe-dot { border-color: var(--border); background: var(--secondary); }

/* Detail grid */
.dark .section-label { color: var(--muted-foreground); }
.dark .detail-value  { color: var(--foreground); }
.dark .detail-note   { color: var(--muted-foreground); }

/* Items table in modal */
.dark .items-table thead tr { background: var(--secondary); }
.dark .items-table th { color: var(--muted-foreground); border-bottom-color: var(--border); }
.dark .items-table td { color: var(--foreground); border-bottom-color: var(--border); }
.dark .items-table tfoot td { border-top-color: var(--border); }
.dark .total-label  { color: var(--brand-gold); }
.dark .total-amount { color: var(--brand-gold); }

/* Confirm modal */
.dark .confirm-title { color: var(--foreground); }
.dark .confirm-body  { color: var(--muted-foreground); }

/* ─── Filters ─── */
.filter-bar  { display:flex; flex-direction:column; gap:12px; margin-bottom:20px; }
.search-wrap { position:relative; }
.search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#737373; pointer-events:none; }
.search-input {
    width:100%; padding:9px 12px 9px 36px;
    border:1px solid #e5e5e5; border-radius:8px;
    font-size:0.875rem; color:#171717; background:#fff;
    outline:none; transition:border-color 0.18s; font-family:inherit; box-sizing:border-box;
}
.search-input:focus { border-color:#245c4a; }
.search-input::placeholder { color:#a3a3a3; }
.filter-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.filter-select {
    flex:1; min-width:130px; padding:9px 12px;
    border:1px solid #e5e5e5; border-radius:8px;
    font-size:0.875rem; color:#171717; background:#fff;
    outline:none; cursor:pointer; font-family:inherit; transition:border-color 0.18s;
}
.filter-select:focus { border-color:#245c4a; }
.result-count { font-size:0.8rem; color:#737373; white-space:nowrap; margin-left:auto; }

/* ─── Desktop Table ─── */
.desktop-only { display:block; }
.mobile-only  { display:none; }



.table-card { border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.06); }
.ord-table  { width:100%; border-collapse:collapse; }
.ord-table thead tr { background:#f8f8f8; border-bottom:1px solid #EDEDED; }
.ord-table th { padding:12px 14px; font-size:0.78rem; font-weight:600; color:#737373; text-align:left; white-space:nowrap; }
.ord-table tbody tr { border-bottom:1px solid #f0f0f0; transition:background 0.12s; }
.ord-table tbody tr:last-child { border-bottom:none; }
.ord-table tbody tr:hover { background:#fafafa; }
.ord-table td { padding:13px 14px; font-size:0.875rem; color:#171717; vertical-align:middle; }

.order-num  { font-weight:600; margin:0 0 2px; font-size:0.82rem; color:#245c4a; font-family:monospace; }
.order-time { font-size:0.75rem; color:#a3a3a3; margin:0; }

.customer-cell { display:flex; align-items:center; gap:10px; }
.customer-avatar { width:34px; height:34px; border-radius:50%; flex-shrink:0; background:linear-gradient(135deg,#245c4a,#1B4D3E); color:#C5A059; font-weight:700; font-size:0.85rem; display:flex; align-items:center; justify-content:center; }
.customer-avatar--lg { width:42px; height:42px; font-size:1rem; }
.customer-name  { font-weight:500; margin:0 0 1px; font-size:0.875rem; }
.customer-phone { font-size:0.75rem; color:#a3a3a3; margin:0; }

.items-preview { display:flex; flex-direction:column; gap:3px; }
.item-chip { font-size:0.72rem; border-radius:4px; padding:2px 7px; display:inline-block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px; }
.item-chip--more { background:#f0f9f6; color:#245c4a; font-weight:600; }

.pickup-cell { display:flex; align-items:center; gap:5px; font-size:0.82rem; color:#555; }
.total-cell  { font-weight:600; }

.payment-badge { padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; }
.payment-badge--paid   { background:#ecfdf5; color:#059669; }
.payment-badge--unpaid { background:#fef2f2; color:#ef4444; }

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    line-height: 1.5;
}

/* Light mode status colors */
.status-badge--pending   { background: #fffbeb; color: #b45309; }
.status-badge--confirmed { background: #eff6ff; color: #1d4ed8; }
.status-badge--preparing { background: #f5f3ff; color: #7c3aed; }
.status-badge--ready     { background: #ecfdf5; color: #059669; }
.status-badge--completed { background: #f0f9f6; color: #245c4a; }
.status-badge--cancelled { background: #fef2f2; color: #ef4444; }

/* Dark mode status colors */
.dark .status-badge--pending   { background: rgba(180, 83,  9, 0.2);  color: #fcd34d; }
.dark .status-badge--confirmed { background: rgba(29,  78,216, 0.2);  color: #93c5fd; }
.dark .status-badge--preparing { background: rgba(124, 58,237, 0.2);  color: #c4b5fd; }
.dark .status-badge--ready     { background: rgba(5,  150, 105, 0.2); color: #6ee7b7; }
.dark .status-badge--completed { background: rgba(36,  92, 74, 0.25); color: #6ee7b7; }
.dark .status-badge--cancelled { background: rgba(239, 68, 68, 0.2);  color: #fca5a5; }

.action-row { display:flex; gap:4px; }
.action-btn { width:30px; height:30px; border-radius:6px; border:1px solid #e5e5e5; background:#fff; color:#737373; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:border-color 0.15s,color 0.15s,background 0.15s; }
.action-btn:hover          { border-color:#245c4a; color:#245c4a; background:#f0f9f6; }
.action-btn--advance:hover { border-color:#059669; color:#059669; background:#ecfdf5; }
.action-btn--cancel:hover  { border-color:#ef4444; color:#ef4444; background:#fef2f2; }

.empty-state { text-align:center; padding:60px 0 !important; }
.empty-inner { display:flex; flex-direction:column; align-items:center; gap:12px; color:#a3a3a3; }
.empty-inner p { margin:0; font-size:0.9rem; }

/* ─── Mobile Order Cards ─── */
.mobile-list { display: flex; flex-direction: column; gap: 14px; }

.empty-state-mobile { display: flex; flex-direction: column; align-items: center; gap: 12px; color: #a3a3a3; padding: 48px 0; text-align: center; }
.empty-state-mobile p { margin: 0; font-size: 0.9rem; }

/* Card shell — generous padding for Shopee-like feel */
.oc { border-radius: 14px; padding: 16px 16px 14px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }

/* Top row: order number + status badges */
.oc-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
.oc-badges { display: flex; flex-direction: row; align-items: flex-start; gap: 6px; flex-shrink: 0; flex-wrap: wrap; justify-content: flex-end; }

/* Ensure status + payment badges wrap cleanly, never clip */
.oc-badges .status-badge,
.oc-badges .payment-badge { white-space: nowrap; }

.oc-customer { margin-bottom: 14px; }

.oc-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8f8f8; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px; }
.oc-meta-item { display: flex; flex-direction: column; gap: 3px; }
.oc-label { font-size: 0.68rem; font-weight: 600; color: #a3a3a3; text-transform: uppercase; letter-spacing: 0.04em; }
.oc-value { font-size: 0.82rem; font-weight: 500; color: #171717; }
.oc-value--price { font-size: 1rem; font-weight: 700; color: #245c4a; }

.oc-items { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 14px; }
.oc-items .item-chip { max-width: none; background: #f0f0f0; color: #444; }

/* Action row — full-width buttons, comfortable height */
.oc-actions { display: flex; align-items: center; gap: 8px; }
.oc-action-btns { display: flex; align-items: center; gap: 6px; margin-left: auto; flex-shrink: 0; }

.oc-view-btn {
    flex: 1;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    background: #f5f5f5; color: #555; border: none;
    padding: 10px 14px; border-radius: 10px;
    font-size: 0.82rem; font-weight: 500; cursor: pointer;
    transition: background 0.15s; font-family: inherit;
}
.oc-view-btn:hover { background: #e8e8e8; }

.oc-advance-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: #ecfdf5; color: #059669; border: 1px solid rgba(5,150,105,0.2);
    padding: 10px 14px; border-radius: 10px;
    font-size: 0.82rem; font-weight: 600; cursor: pointer;
    transition: background 0.15s; font-family: inherit; white-space: nowrap;
}
.oc-advance-btn:hover { background: #d1fae5; }

/* ─── Modals ─── */
.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:flex-end; justify-content:center; z-index:1000; backdrop-filter:blur(2px); padding:0 16px; }
.modal { background:#fff; border-radius:16px 16px 0 0; padding:24px 20px; width:100%; max-width:calc(100vw - 32px); box-shadow:0 -8px 40px rgba(0,0,0,0.18); animation:slideUp 0.25s ease; max-height:92vh; overflow-y:auto; margin:0 auto; }
@keyframes slideUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }

.modal-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px; }
.modal-title  { font-size:1.1rem; font-weight:700; color:#245c4a; margin:0 0 2px; }
.modal-sub    { font-size:0.8rem; color:#a3a3a3; margin:0; }
.modal-close  { background:none; border:none; color:#a3a3a3; cursor:pointer; padding:4px; border-radius:6px; transition:color 0.15s; display:flex; flex-shrink:0; }
.modal-close:hover { color:#ef4444; }

/* Pipeline */
.pipeline { display:flex; align-items:center; background:#f8f8f8; border-radius:10px; padding:14px 18px; margin-bottom:20px; overflow-x:auto; }
.pipe-step { display:flex; flex-direction:column; align-items:center; gap:5px; flex-shrink:0; }
.pipe-dot  { width:12px; height:12px; border-radius:50%; border:2px solid #EDEDED; background:#fff; transition:all 0.2s; }
.pipe-step--done .pipe-dot    { background:#245c4a; border-color:#245c4a; }
.pipe-step--current .pipe-dot { background:#C5A059; border-color:#C5A059; box-shadow:0 0 0 3px rgba(197,160,89,0.25); }
.pipe-step--cancelled .pipe-dot { background:#ef4444; border-color:#ef4444; }
.pipe-label { font-size:0.62rem; color:#a3a3a3; white-space:nowrap; font-weight:500; }
.pipe-step--done .pipe-label    { color:#245c4a; }
.pipe-step--current .pipe-label { color:#C5A059; font-weight:700; }
.pipe-line { flex:1; height:2px; background:#EDEDED; margin:0 4px 16px; transition:background 0.3s; min-width:20px; }
.pipe-line--done { background:#245c4a; }

/* Detail grid */
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px; }
.section-label { font-size:0.7rem; font-weight:700; color:#a3a3a3; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 6px; }
.detail-value  { font-size:0.875rem; color:#171717; font-weight:500; margin:0; }
.detail-note   { font-size:0.85rem; color:#555; margin:0; font-style:italic; }

/* Items table in modal */
.items-table-wrap { margin-bottom:24px; overflow-x:auto; }
.items-table { width:100%; border-collapse:collapse; font-size:0.85rem; min-width:320px; }
.items-table thead tr { background:#f8f8f8; }
.items-table th { padding:9px 12px; text-align:left; font-size:0.75rem; font-weight:600; color:#737373; border-bottom:1px solid #EDEDED; }
.items-table td { padding:10px 12px; border-bottom:1px solid #f0f0f0; color:#171717; }
.items-table tfoot td { border-top:2px solid #EDEDED; border-bottom:none; }
.total-label  { font-weight:700; color:#245c4a; }
.total-amount { font-weight:700; color:#245c4a; }

.modal-footer { display:flex; justify-content:flex-end; gap:10px; }

/* Confirm modal */
.modal--confirm { text-align:center; }
.confirm-icon  { width:56px; height:56px; border-radius:50%; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
.confirm-title { font-size:1.15rem; font-weight:700; color:#171717; margin:0 0 8px; }
.confirm-body  { font-size:0.875rem; color:#737373; margin:0 0 24px; line-height:1.5; }
.modal--confirm .modal-footer { justify-content:center; }

/* ─── Responsive (standard media queries) ─── */
@media (max-width: 720px) {
    .ord-page  { padding: 16px; }
    .ord-title { font-size: 1.2rem; }
    .ord-sub   { font-size: 0.78rem; }
    .btn-label { display: none; }
    .btn-outline { padding: 9px 12px; }

    .stat-grid { grid-template-columns: repeat(2,1fr); gap: 10px; margin-bottom: 16px; }
    .stat-card { padding: 12px 14px; gap: 10px; }
    .stat-icon { width: 36px; height: 36px; border-radius: 8px; }
    .stat-value { font-size: 1.1rem; }
    .stat-label { font-size: 0.7rem; }

    .filter-bar  { flex-direction: column; }
    .filter-row  { gap: 8px; }
    .result-count { margin-left: 0; }

    .desktop-only { display: none; }
    .mobile-only  { display: block; }
}

@media (min-width: 721px) {
    .filter-bar  { flex-direction: row; align-items: center; }
    .search-wrap { flex: 1; }
    .filter-row  { flex-wrap: nowrap; }
    .tab-strip   { width: auto; min-width: 0; }
    .desktop-only { display: block; }
    .mobile-only  { display: none; }

    .tab-strip-wrap { margin: 0; padding: 0; }

    .modal-backdrop  { align-items: center; }
    .modal           { border-radius: 14px; padding: 28px; }
    .modal--detail   { max-width: 580px; }
    .modal--confirm  { max-width: 420px; }
}

@media (max-width: 380px) {
    .stat-grid { grid-template-columns: 1fr 1fr; }
    .oc-meta   { grid-template-columns: 1fr; }

    /* Even tighter tab buttons on very small phones */
    .tab-btn   { padding: 8px 12px; font-size: 0.79rem; }
    .tab-count { padding: 1px 6px; }
}
</style>