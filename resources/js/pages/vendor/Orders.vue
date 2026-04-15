<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import { useSidebar } from '@/composables/useSidebar';
import { Clock, CheckCircle, ChefHat, Package, CheckCircle2, XCircle, Eye, ArrowRight, Trash2 } from 'lucide-vue-next';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

type OrderStatus = 'pending' | 'confirmed' | 'preparing' | 'ready' | 'completed' | 'cancelled';

interface OrderItem { 
    product_name: string; 
    quantity: number; 
    unit_price: number; 
}
interface Order {
    id: number; 
    order_number: string;
    status: OrderStatus; 
    total_amount: number; 
    is_paid: boolean;
    placed_at: string; 
    created_at: string; 
    items: OrderItem[];
}

interface Stats {
    pending: number; 
    active: number; 
    completed: number; 
    revenue: number;
}

const props = defineProps<{
    orders: Order[];
    stats: Stats;
}>();

const STATUS_CONFIG: Record<OrderStatus, { label:string; color:string; bg:string; next:OrderStatus|null; icon:any }> = {
    pending:   { label:'Pending',   color:'#b45309', bg:'#fffbeb', next:'confirmed', icon: Clock },
    confirmed: { label:'Confirmed', color:'#1d4ed8', bg:'#eff6ff', next:'preparing', icon: CheckCircle },
    preparing: { label:'Preparing', color:'#7c3aed', bg:'#f5f3ff', next:'ready',     icon: ChefHat },
    ready:     { label:'Ready',     color:'#059669', bg:'#ecfdf5', next:'completed', icon: Package },
    completed: { label:'Completed', color:'#245c4a', bg:'#f0f9f6', next:null,        icon: CheckCircle2 },
    cancelled: { label:'Cancelled', color:'#ef4444', bg:'#fef2f2', next:null,        icon: XCircle },
};

const STATUS_ORDER: OrderStatus[] = ['pending','confirmed','preparing','ready','completed','cancelled'];

const searchQuery   = ref('');
const filterStatus  = ref<OrderStatus | 'all'>('all');
const filterPayment = ref<'all'|'paid'|'unpaid'>('all');
const detailOrder   = ref<Order | null>(null);
const confirmCancel = ref<Order | null>(null);

// Constants for item display
const INITIAL_ITEMS_SHOW = 2;

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

// Helper functions for item preview
const getVisibleItems = (items: OrderItem[]) => {
    return items.slice(0, INITIAL_ITEMS_SHOW);
};

const getRemainingCount = (items: OrderItem[]) => {
    return Math.max(0, items.length - INITIAL_ITEMS_SHOW);
};

const getRemainingItems = (items: OrderItem[]) => {
    return items.slice(INITIAL_ITEMS_SHOW);
};

// Get next status label
const getNextStatusLabel = (status: OrderStatus): string => {
    const next = STATUS_CONFIG[status].next;
    return next ? STATUS_CONFIG[next].label : '';
};

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

function exportOrders() {
    window.location.href = '/vendor/orders/export';
}

function formatPrice(v: number | null | undefined) { 
    return '₱' + (v ?? 0).toLocaleString('en-PH', { minimumFractionDigits:2 }); 
}

function formatDate(dt: string | null | undefined) { 
    if (!dt) return '—'; 
    return new Date(dt).toLocaleString('en-PH', { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }); 
}
</script>

<template>
    <TooltipProvider>
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
                        <button type="button" class="btn-outline" @click="exportOrders">
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
                                <component v-if="s !== 'all'" :is="STATUS_CONFIG[s as OrderStatus].icon" class="tab-icon" />
                                <span>{{ s === 'all' ? 'All Orders' : STATUS_CONFIG[s as OrderStatus].label }}</span>
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
                        <div class="table-responsive">
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
                                            <!-- Visible items -->
                                            <span 
                                                v-for="item in getVisibleItems(order.items)" 
                                                :key="item.product_name" 
                                                class="item-chip"
                                            >
                                                {{ item.quantity }}× {{ item.product_name }}
                                            </span>
                                            
                                            <!-- +X more with Tooltip -->
                                            <Tooltip v-if="getRemainingCount(order.items) > 0">
                                                <TooltipTrigger as-child>
                                                    <span class="item-chip item-chip--more cursor-help">
                                                        +{{ getRemainingCount(order.items) }} more
                                                    </span>
                                                </TooltipTrigger>
                                                <TooltipContent side="bottom" align="start" class="tooltip-compact">
                                                    <div class="tooltip-items">
                                                        <p class="tooltip-title">Additional items</p>
                                                        <div class="tooltip-list">
                                                            <div
                                                                v-for="item in getRemainingItems(order.items)"
                                                                :key="item.product_name"
                                                                class="tooltip-item"
                                                            >
                                                                <span class="tooltip-qty">{{ item.quantity }}×</span>
                                                                <span class="tooltip-name">{{ item.product_name }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </TooltipContent>
                                            </Tooltip>
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
                                            <component :is="STATUS_CONFIG[order.status].icon" class="status-icon" />
                                            {{ STATUS_CONFIG[order.status].label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-row">
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <button class="action-btn action-btn--view" @click="detailOrder = order">
                                                        <Eye class="action-icon" />
                                                    </button>
                                                </TooltipTrigger>
                                                <TooltipContent side="top">
                                                    <p>View Details</p>
                                                </TooltipContent>
                                            </Tooltip>
                                            
                                            <Tooltip v-if="STATUS_CONFIG[order.status].next">
                                                <TooltipTrigger as-child>
                                                   <button class="action-btn action-btn--advance" @click="advanceStatus(order)">
                                                        <ArrowRight class="action-icon" />
                                                        <span> {{ getNextStatusLabel(order.status) }}</span>
                                                    </button>
                                                </TooltipTrigger>
                                                <TooltipContent side="top">
                                                    <p>Mark as {{ getNextStatusLabel(order.status) }}</p>
                                                </TooltipContent>
                                            </Tooltip>
                                            
                                            <Tooltip v-if="!['completed','cancelled'].includes(order.status)">
                                                <TooltipTrigger as-child>
                                                    <button class="action-btn action-btn--cancel" @click="confirmCancel = order">
                                                        <Trash2 class="action-icon" />
                                                    </button>
                                                </TooltipTrigger>
                                                <TooltipContent side="top">
                                                    <p>Cancel Order</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
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
                                    <span class="status-badge" :class="statusBadgeClass(order.status)">
                                        <component :is="STATUS_CONFIG[order.status].icon" class="status-icon" />
                                        {{ STATUS_CONFIG[order.status].label }}
                                    </span>
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
                                <!-- Visible items -->
                                <span 
                                    v-for="item in getVisibleItems(order.items)" 
                                    :key="item.product_name" 
                                    class="item-chip"
                                >
                                    {{ item.quantity }}× {{ item.product_name }}
                                </span>
                                
                                <!-- +X more with Tooltip for mobile -->
                                <Tooltip v-if="getRemainingCount(order.items) > 0">
                                    <TooltipTrigger as-child>
                                        <span class="item-chip item-chip--more cursor-help">
                                            +{{ getRemainingCount(order.items) }} more
                                        </span>
                                    </TooltipTrigger>
                                    <TooltipContent side="bottom" align="start" class="tooltip-compact">
                                        <div class="tooltip-items">
                                            <p class="tooltip-title">Additional items</p>
                                            <div class="tooltip-list">
                                                <div
                                                    v-for="item in getRemainingItems(order.items)"
                                                    :key="item.product_name"
                                                    class="tooltip-item"
                                                >
                                                    <span class="tooltip-qty">{{ item.quantity }}×</span>
                                                    <span class="tooltip-name">{{ item.product_name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </TooltipContent>
                                </Tooltip>
                            </div>

                            <div class="oc-actions">
                                <button class="oc-view-btn" @click="detailOrder = order">
                                    <Eye class="action-icon" />
                                    View Details
                                </button>
                                <div class="oc-action-btns">
                                   <button v-if="STATUS_CONFIG[order.status].next" class="oc-advance-btn" @click="advanceStatus(order)">
                                        <ArrowRight class="action-icon" />
                                        {{ getNextStatusLabel(order.status) }}
                                    </button>
                                    <button v-if="!['completed','cancelled'].includes(order.status)" class="action-btn action-btn--cancel" @click="confirmCancel = order">
                                        <Trash2 class="action-icon" />
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
                        <div class="detail-section">
                            <p class="section-label">Current Status</p>
                            <span class="status-badge" :class="statusBadgeClass(detailOrder.status)">
                                <component :is="STATUS_CONFIG[detailOrder.status].icon" class="status-icon" />
                                {{ STATUS_CONFIG[detailOrder.status].label }}
                            </span>
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
    </TooltipProvider>
</template>



<style scoped>
/* ─── Base ─── */
.ord-page {
    padding: 20px 16px;
    max-width: 1400px;
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    min-width: 320px;
    width: 100%;
}

/* ─── Page Header ─── */
.ord-header { 
    display: flex; 
    align-items: flex-start; 
    justify-content: space-between; 
    gap: 12px; 
    margin-bottom: 24px; 
    flex-wrap: wrap; 
}
.ord-title { 
    font-size: 1.6rem; 
    font-weight: 600; 
    color: #245c4a; 
    margin: 0 0 4px; 
    line-height: 1.2; 
}
.dark .ord-title { color: #6ee7b7; }
.ord-sub { 
    font-size: 0.875rem; 
    color: #737373; 
    margin: 0; 
}
.dark .ord-sub { color: #7a90a8; }

/* ─── Buttons ─── */
.btn-primary {
    display: inline-flex; 
    align-items: center; 
    gap: 7px;
    background: #1B4D3E; 
    color: #fff; 
    border: none;
    padding: 9px 18px; 
    border-radius: 8px;
    font-size: 0.875rem; 
    font-weight: 500; 
    cursor: pointer;
    transition: background 0.18s; 
    font-family: inherit; 
    flex-shrink: 0;
}
.btn-primary:hover { background: #245c4a; }

.btn-outline {
    display: inline-flex; 
    align-items: center; 
    gap: 7px; 
    flex-shrink: 0;
    background: #fff; 
    color: #245c4a; 
    border: 1px solid #245c4a;
    padding: 9px 18px; 
    border-radius: 8px;
    font-size: 0.875rem; 
    font-weight: 500; 
    cursor: pointer;
    transition: background 0.15s; 
    font-family: inherit;
}
.btn-outline:hover { background: #f0f9f6; }
.dark .btn-outline {
    background: #132032;
    color: #6ee7b7;
    border-color: #1e3048;
}
.dark .btn-outline:hover { background: #1a2e42; }

/* ─── Stat Grid ─── */
.stat-grid { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 14px; 
    margin-bottom: 20px; 
}
.stat-card { 
    border-radius: 12px; 
    padding: 16px 18px; 
    display: flex; 
    align-items: center; 
    gap: 14px; 
    box-shadow: 0 1px 3px rgba(0,0,0,.06); 
    background: white;
    border: 1px solid #e5e5e5;
}
.dark .stat-card {
    background: #132032;
    border-color: #1e3048;
    box-shadow: none;
}
.stat-icon { 
    width: 42px; 
    height: 42px; 
    border-radius: 10px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0; 
}
.stat-label { 
    font-size: 0.75rem; 
    color: #737373; 
    margin: 0 0 2px; 
    font-weight: 500; 
}
.dark .stat-label { color: #7a90a8; }
.stat-value { 
    font-size: 1.3rem; 
    font-weight: 700; 
    color: #171717; 
    margin: 0; 
}
.dark .stat-value { color: #e2eaf4; }

/* ─── Tab Strip ─── */
.tab-strip-wrap {
    overflow-x: auto;
    overflow-y: visible;
    margin: 0 0 20px 0;
    padding: 0;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    position: relative;
}
.tab-strip-wrap::-webkit-scrollbar { height: 4px; }
.tab-strip-wrap::-webkit-scrollbar-track { background: #e5e5e5; border-radius: 4px; }
.tab-strip-wrap::-webkit-scrollbar-thumb { background: #245c4a; border-radius: 4px; }
.dark .tab-strip-wrap::-webkit-scrollbar-track { background: #1e3048; }

.tab-strip {
    display: inline-flex;
    gap: 6px;
    background: #f5f5f5;
    border-radius: 12px;
    padding: 6px;
    min-width: 100%;
    width: max-content;
}
.dark .tab-strip { background: #1a2e42; }

.tab-btn {
    display: inline-flex; 
    align-items: center; 
    gap: 7px;
    padding: 9px 18px; 
    border-radius: 8px; 
    border: none;
    background: transparent; 
    color: #737373;
    font-size: 0.83rem; 
    font-weight: 500; 
    cursor: pointer;
    transition: background 0.15s, color 0.15s; 
    font-family: inherit; 
    white-space: nowrap;
    flex-shrink: 0;
}
.tab-btn:hover { background: #fff; color: #245c4a; }
.tab-btn--active { 
    background: #fff; 
    color: #245c4a; 
    box-shadow: 0 1px 3px rgba(0,0,0,.1); 
    font-weight: 600; 
}
.dark .tab-btn { color: #7a90a8; }
.dark .tab-btn:hover { background: #1e3048; color: #e2eaf4; }
.dark .tab-btn--active { 
    background: #132032; 
    color: #6ee7b7; 
    box-shadow: 0 1px 3px rgba(0,0,0,.3); 
}

.tab-icon { width: 16px; height: 16px; flex-shrink: 0; }

.tab-count { 
    background: #EDEDED; 
    color: #737373; 
    padding: 2px 8px; 
    border-radius: 20px; 
    font-size: 0.71rem; 
    font-weight: 600; 
}
.dark .tab-count { background: #1e3048; color: #7a90a8; }
.tab-btn--active .tab-count { background: #f0f9f6; color: #245c4a; }
.dark .tab-btn--active .tab-count { background: rgba(36,92,74,0.3); color: #6ee7b7; }

/* ─── Filters ─── */
.filter-bar { 
    display: flex; 
    flex-direction: column; 
    gap: 12px; 
    margin-bottom: 20px; 
}
.search-wrap { position: relative; flex: 1; }
.search-icon { 
    position: absolute; 
    left: 12px; 
    top: 50%; 
    transform: translateY(-50%); 
    color: #737373; 
    pointer-events: none; 
}
.dark .search-icon { color: #7a90a8; }
.search-input {
    width: 100%; 
    padding: 9px 12px 9px 36px;
    border: 1px solid #e5e5e5; 
    border-radius: 8px;
    font-size: 0.875rem; 
    color: #171717; 
    background: #fff;
    outline: none; 
    transition: border-color 0.18s; 
    font-family: inherit; 
    box-sizing: border-box;
}
.search-input:focus { border-color: #245c4a; }
.search-input::placeholder { color: #a3a3a3; }
.dark .search-input {
    background: #1a2e42;
    border-color: #1e3048;
    color: #e2eaf4;
}
.dark .search-input::placeholder { color: #7a90a8; }
.dark .search-input:focus { border-color: #6ee7b7; }

.filter-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.filter-select {
    flex: 1; 
    min-width: 130px; 
    padding: 9px 12px;
    border: 1px solid #e5e5e5; 
    border-radius: 8px;
    font-size: 0.875rem; 
    color: #171717; 
    background: #fff;
    outline: none; 
    cursor: pointer; 
    font-family: inherit; 
    transition: border-color 0.18s;
}
.filter-select:focus { border-color: #245c4a; }
.dark .filter-select {
    background: #1a2e42;
    border-color: #1e3048;
    color: #e2eaf4;
}
.result-count { font-size: 0.8rem; color: #737373; white-space: nowrap; }
.dark .result-count { color: #7a90a8; }

/* ─── Desktop Table ─── */
.desktop-only { display: block; }
.mobile-only  { display: none; }

.table-card { 
    border-radius: 12px; 
    overflow: hidden; 
    box-shadow: 0 1px 3px rgba(0,0,0,.06); 
    background: white;
    border: 1px solid #e5e5e5;
}
.dark .table-card {
    background: #132032;
    border-color: #1e3048;
    box-shadow: none;
}

.table-responsive {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    position: relative;
}

.ord-table {
    width: 100%;
    min-width: 1000px;
    border-collapse: collapse;
}
.ord-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #737373;
    background: #f8f8f8;
    border-bottom: 1px solid #e5e5e5;
}
.dark .ord-table th {
    background: #0f2235;
    color: #7a90a8;
    border-bottom-color: #1e3048;
}
.ord-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f0;
    color: #171717;
}
.dark .ord-table td {
    border-bottom-color: #1e3048;
    color: #e2eaf4;
}
.ord-table tbody tr:hover td { background: #fafafa; }
.dark .ord-table tbody tr:hover td { background: #1a2e42; }

.ord-table th:first-child, .ord-table td:first-child { min-width: 180px; }
.ord-table th:nth-child(2), .ord-table td:nth-child(2) { min-width: 120px; }
.ord-table th:nth-child(3), .ord-table td:nth-child(3) { min-width: 220px; }

.order-num { 
    font-weight: 600; 
    margin: 0 0 2px; 
    font-size: 0.82rem; 
    color: #245c4a; 
    font-family: monospace; 
}
.dark .order-num { color: #6ee7b7; }
.order-time { font-size: 0.75rem; color: #a3a3a3; margin: 0; }
.dark .order-time { color: #7a90a8; }

.items-preview { display: flex; flex-direction: column; gap: 3px; }
.total-cell { font-weight: 600; }
.dark .total-cell { color: #e2eaf4; }

/* ─── Badges ─── */
.payment-badge { 
    padding: 3px 10px; 
    border-radius: 20px; 
    font-size: 0.75rem; 
    font-weight: 600; 
    white-space: nowrap;
    display: inline-block;
}
.payment-badge--paid   { background: #ecfdf5; color: #059669; }
.payment-badge--unpaid { background: #fef2f2; color: #ef4444; }
.dark .payment-badge--paid   { background: rgba(6,95,70,0.35);    color: #6ee7b7; }
.dark .payment-badge--unpaid { background: rgba(127,29,29,0.35);  color: #fca5a5; }

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
    line-height: 1.5;
}
.status-icon { 
    width: 14px; 
    height: 14px; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0;
}

.status-badge--pending   { background: #fffbeb; color: #b45309; }
.status-badge--confirmed { background: #eff6ff; color: #1d4ed8; }
.status-badge--preparing { background: #f5f3ff; color: #7c3aed; }
.status-badge--ready     { background: #ecfdf5; color: #059669; }
.status-badge--completed { background: #f0f9f6; color: #245c4a; }
.status-badge--cancelled { background: #fef2f2; color: #ef4444; }

.dark .status-badge--pending   { background: rgba(180,83,9,0.25);   color: #fbbf24; }
.dark .status-badge--confirmed { background: rgba(29,78,216,0.25);  color: #93c5fd; }
.dark .status-badge--preparing { background: rgba(124,58,237,0.25); color: #c4b5fd; }
.dark .status-badge--ready     { background: rgba(5,150,105,0.25);  color: #6ee7b7; }
.dark .status-badge--completed { background: rgba(36,92,74,0.3);    color: #6ee7b7; }
.dark .status-badge--cancelled { background: rgba(239,68,68,0.25);  color: #fca5a5; }

/* ─── Action Buttons ─── */
.action-row { 
    display: flex; 
    gap: 8px; 
    flex-wrap: wrap; 
    align-items: center; 
}

.action-btn { 
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px; 
    border: 1px solid transparent;
    cursor: pointer; 
    transition: all 0.2s; 
    font-size: 0.75rem;
    font-weight: 500;
    font-family: inherit;
    white-space: nowrap;
}
.action-icon { width: 14px; height: 14px; flex-shrink: 0; }

.action-btn--view {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
}
.action-btn--view:hover { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
.dark .action-btn--view { background: rgba(29,78,216,0.2); color: #93c5fd; border-color: rgba(59,130,246,0.3); }
.dark .action-btn--view:hover { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }

.action-btn--advance {
    background: #ecfdf5;
    color: #059669;
    border-color: #a7f3d0;
}
.action-btn--advance:hover { background: #059669; color: #fff; border-color: #059669; }
.dark .action-btn--advance { background: rgba(5,150,105,0.2); color: #6ee7b7; border-color: rgba(167,243,208,0.3); }
.dark .action-btn--advance:hover { background: #059669; color: #fff; border-color: #059669; }

.action-btn--cancel {
    background: #fef2f2;
    color: #ef4444;
    border-color: #fecaca;
}
.action-btn--cancel:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
.dark .action-btn--cancel { background: rgba(239,68,68,0.2); color: #fca5a5; border-color: rgba(248,113,113,0.3); }
.dark .action-btn--cancel:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

.empty-state { text-align: center; padding: 60px 0 !important; }
.empty-inner { display: flex; flex-direction: column; align-items: center; gap: 12px; color: #a3a3a3; }
.dark .empty-inner { color: #7a90a8; }

/* ─── Item Chips ─── */
.item-chip {
    font-size: 0.72rem;
    border-radius: 4px;
    padding: 2px 7px;
    display: inline-block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
    background: #f3f4f6;
    color: #374151;
}
.dark .item-chip { background: #1e3048; color: #e2eaf4; }

.item-chip--more {
    background: #ecfdf5 !important;
    color: #059669 !important;
    font-weight: 600;
    cursor: help;
}
.dark .item-chip--more { background: #064e3b !important; color: #6ee7b7 !important; }

/* ─── Tooltip internals ─── */
.tooltip-items { min-width: 180px; }
.tooltip-title {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--muted-foreground);
    margin: 0 0 6px 0;
    padding-bottom: 4px;
    border-bottom: 1px solid var(--border);
}
.tooltip-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-height: 200px;
    overflow-y: auto;
}
.tooltip-item {
    display: flex;
    align-items: baseline;
    gap: 6px;
    font-size: 0.75rem;
    line-height: 1.4;
    padding: 2px 0;
}
.tooltip-qty { font-weight: 600; color: #10b981; min-width: 28px; }
.dark .tooltip-qty { color: #6ee7b7; }
.tooltip-name { color: var(--foreground); word-break: break-word; }

/* ─── Modals ─── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 16px;
}
.modal {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    max-width: 700px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}
.dark .modal { background: #132032; border: 1px solid #1e3048; }
.modal--detail { max-width: 800px; }
.modal--confirm { max-width: 420px; }

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e5e5;
}
.dark .modal-header { border-bottom-color: #1e3048; }
.modal-title { font-size: 1.25rem; font-weight: 600; color: #171717; margin: 0 0 4px; }
.dark .modal-title { color: #e2eaf4; }
.modal-sub { font-size: 0.8rem; color: #737373; margin: 0; }
.dark .modal-sub { color: #7a90a8; }

.modal-close {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    background: transparent; color: #737373; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.modal-close:hover { background: #f5f5f5; }
.dark .modal-close { color: #7a90a8; }
.dark .modal-close:hover { background: #1e3048; }

/* ─── Pipeline ─── */
.pipeline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24px;
    background: #fafafa;
}
.dark .pipeline { background: #0f2235; }

.pipe-step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.pipe-dot {
    width: 12px; height: 12px; border-radius: 50%;
    background: #d4d4d4; border: 2px solid white;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.dark .pipe-dot { background: #1e3048; border-color: #132032; }
.pipe-step--done .pipe-dot { background: #245c4a; }
.dark .pipe-step--done .pipe-dot { background: #6ee7b7; }
.pipe-step--current .pipe-dot { background: #C5A059; box-shadow: 0 0 0 3px rgba(197,160,89,0.2); }
.pipe-step--cancelled .pipe-dot { background: #ef4444; }

.pipe-label { font-size: 0.7rem; font-weight: 500; color: #737373; text-align: center; }
.dark .pipe-label { color: #7a90a8; }
.pipe-step--done .pipe-label,
.pipe-step--current .pipe-label { color: #171717; font-weight: 600; }
.dark .pipe-step--done .pipe-label,
.dark .pipe-step--current .pipe-label { color: #e2eaf4; }

.pipe-line { 
    flex: 1; height: 2px; background: #d4d4d4; 
    margin: 0 4px; margin-bottom: 24px; 
}
.dark .pipe-line { background: #1e3048; }
.pipe-line--done { background: #245c4a; }
.dark .pipe-line--done { background: #6ee7b7; }

/* ─── Detail Grid ─── */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e5e5;
}
.dark .detail-grid { border-bottom-color: #1e3048; }
.detail-section { display: flex; flex-direction: column; gap: 4px; }
.section-label {
    font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.03em; color: #a3a3a3; margin: 0;
}
.dark .section-label { color: #7a90a8; }
.detail-value { font-size: 0.9rem; font-weight: 500; color: #171717; margin: 0; }
.dark .detail-value { color: #e2eaf4; }

/* ─── Items Table (in modal) ─── */
.items-table-wrap { padding: 20px 24px; }
.items-table { width: 100%; border-collapse: collapse; }
.items-table th {
    text-align: left; padding: 8px 0;
    font-size: 0.7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.03em;
    color: #a3a3a3; border-bottom: 1px solid #e5e5e5;
}
.dark .items-table th { color: #7a90a8; border-bottom-color: #1e3048; }
.items-table td { 
    padding: 12px 0; font-size: 0.875rem; 
    color: #171717; border-bottom: 1px solid #f0f0f0; 
}
.dark .items-table td { color: #e2eaf4; border-bottom-color: #1e3048; }
.items-table tfoot tr { border-top: 2px solid #e5e5e5; }
.dark .items-table tfoot tr { border-top-color: #1e3048; }
.items-table .total-label { text-align: right; font-weight: 600; padding-top: 12px; }
.dark .items-table .total-label { color: #e2eaf4; }
.items-table .total-amount { font-weight: 700; color: #245c4a; font-size: 1rem; padding-top: 12px; }
.dark .items-table .total-amount { color: #6ee7b7; }

/* ─── Modal Footer Buttons ─── */
.modal-footer {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 12px; padding: 16px 24px 24px;
}
.btn-ghost {
    padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e5e5;
    background: white; color: #737373; font-size: 0.875rem; font-weight: 500;
    cursor: pointer; transition: all 0.15s; font-family: inherit;
}
.btn-ghost:hover { background: #f5f5f5; border-color: #d4d4d4; }
.dark .btn-ghost { background: #132032; border-color: #1e3048; color: #94a3b8; }
.dark .btn-ghost:hover { background: #1a2e42; }

.btn-danger {
    padding: 8px 16px; border-radius: 8px; border: none;
    background: #ef4444; color: white; font-size: 0.875rem; font-weight: 500;
    cursor: pointer; transition: background 0.15s; font-family: inherit;
}
.btn-danger:hover { background: #dc2626; }

/* ─── Confirm Modal ─── */
.confirm-icon {
    width: 48px; height: 48px; border-radius: 50%;
    background: #fef2f2; color: #ef4444;
    display: flex; align-items: center; justify-content: center;
    margin: 24px auto 16px;
}
.confirm-title { 
    font-size: 1.25rem; font-weight: 600; color: #171717; 
    text-align: center; margin: 0 0 8px; 
}
.dark .confirm-title { color: #e2eaf4; }
.confirm-body { 
    font-size: 0.875rem; color: #737373; 
    text-align: center; margin: 0 24px 24px; 
}
.dark .confirm-body { color: #94a3b8; }

/* ─── Mobile Order Cards ─── */
.mobile-list { display: flex; flex-direction: column; gap: 14px; }
.empty-state-mobile {
    display: flex; flex-direction: column; align-items: center;
    gap: 12px; color: #a3a3a3; padding: 48px 0; text-align: center;
}
.dark .empty-state-mobile { color: #7a90a8; }

.oc { 
    border-radius: 14px; padding: 16px; 
    box-shadow: 0 1px 4px rgba(0,0,0,.07); 
    background: white; border: 1px solid #e5e5e5; 
}
.dark .oc { background: #132032; border-color: #1e3048; box-shadow: none; }

.oc-head { 
    display: flex; align-items: flex-start; 
    justify-content: space-between; gap: 10px; margin-bottom: 14px; 
}
.oc-badges { 
    display: flex; flex-direction: row; align-items: flex-start; 
    gap: 6px; flex-shrink: 0; flex-wrap: wrap; justify-content: flex-end; 
}

.oc-meta { 
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
    background: #f8f8f8; border-radius: 10px; 
    padding: 12px 14px; margin-bottom: 12px;
}
.dark .oc-meta { background: #0f2235; }
.oc-meta-item { display: flex; flex-direction: column; gap: 3px; }
.oc-label { 
    font-size: 0.68rem; font-weight: 600; color: #a3a3a3; 
    text-transform: uppercase; letter-spacing: 0.04em; 
}
.dark .oc-label { color: #7a90a8; }
.oc-value { font-size: 0.82rem; font-weight: 500; color: #171717; }
.dark .oc-value { color: #e2eaf4; }
.oc-value--price { font-size: 1rem; font-weight: 700; color: #245c4a; }
.dark .oc-value--price { color: #6ee7b7; }

.oc-items { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 14px; }
.oc-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.oc-action-btns { display: flex; align-items: center; gap: 6px; margin-left: auto; flex-shrink: 0; }

.oc-view-btn {
    flex: 1; display: inline-flex; align-items: center; 
    justify-content: center; gap: 6px;
    background: #f5f5f5; color: #555; border: none;
    padding: 10px 14px; border-radius: 10px;
    font-size: 0.82rem; font-weight: 500; 
    cursor: pointer; transition: background 0.15s; font-family: inherit;
}
.oc-view-btn:hover { background: #e8e8e8; }
.dark .oc-view-btn { background: #1a2e42; color: #94a3b8; }
.dark .oc-view-btn:hover { background: #1e3048; }

.oc-advance-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;
    padding: 8px 12px; border-radius: 8px;
    font-size: 0.75rem; font-weight: 500; 
    cursor: pointer; transition: all 0.2s; white-space: nowrap;
    font-family: inherit;
}
.oc-advance-btn:hover { background: #059669; color: white; border-color: #059669; }
.dark .oc-advance-btn { background: rgba(5,150,105,0.2); color: #6ee7b7; border-color: rgba(167,243,208,0.3); }
.dark .oc-advance-btn:hover { background: #059669; color: white; border-color: #059669; }



/* ─── Responsive ─── */
@media (max-width: 768px) {
    .ord-page { padding: 12px; }
    .ord-title { font-size: 1.2rem; }
    .ord-sub { font-size: 0.75rem; }
    .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
    .stat-card { padding: 12px; gap: 10px; }
    .stat-icon { width: 36px; height: 36px; }
    .stat-value { font-size: 1.1rem; }
    .stat-label { font-size: 0.7rem; }
    .filter-bar { flex-direction: column; }
    .filter-row { gap: 8px; }
    .desktop-only { display: none; }
    .mobile-only { display: block; }
    .btn-label { display: none; }
    .btn-outline { padding: 9px 12px; }
}
@media (min-width: 769px) {
    .filter-bar { flex-direction: row; align-items: center; }
    .search-wrap { flex: 1; }
    .filter-row { flex-wrap: nowrap; }
    .desktop-only { display: block; }
    .mobile-only { display: none; }
}
@media (max-width: 480px) {
    .stat-grid { grid-template-columns: 1fr 1fr; }
    .oc-meta { grid-template-columns: 1fr; }
    .tab-btn { padding: 8px 12px; font-size: 0.75rem; }
    .tab-count { padding: 1px 6px; font-size: 0.65rem; }
}
</style>