<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
  ArrowRight,
  CheckCircle2,
  ChevronRight,
  Clock3,
  Package,
  RotateCcw,
  ShoppingBag,
  Store,
  TrendingUp,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, watch } from 'vue';

import Header from '@/components/Header.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import Sidebar from '@/components/Sidebar.vue';
import { Button } from '@/components/ui/button';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();

const contentClass = computed(() => ({
  'dashboard-content': true,
  'sidebar-collapsed': isCollapsed.value,
}));

type OrderStatus = 'pending' | 'preparing' | 'ready_for_pickup' | 'picked_up' | 'completed' | 'cancelled';

type CurrentOrder = {
  id: number
  order_number: string
  store: string
  store_id: string
  status: OrderStatus
  total: number
  ordered_at: string | null
};

type RecentOrder = {
  id: number
  order_number: string
  store: string
  store_id: string
  status: OrderStatus
  total: number
  ordered_at: string | null
};

type StoreItem = {
  id: string
  name: string
  domain: string | null
  hours: Record<string, string> | null
  logo: string | null
  isOpen: boolean
};

const props = defineProps<{
  currentOrder: CurrentOrder | null
  recentOrders: RecentOrder[]
  stores: StoreItem[]
}>();

const statusLabel: Record<OrderStatus, string> = {
  pending: 'Order Placed',
  preparing: 'Preparing',
  ready_for_pickup: 'Ready for Pickup',
  picked_up: 'Picked Up',
  completed: 'Completed',
  cancelled: 'Cancelled',
};

const pickupSteps: OrderStatus[] = ['pending', 'preparing', 'ready_for_pickup', 'picked_up'];

const currentStepIndex = computed(() =>
  props.currentOrder ? pickupSteps.indexOf(props.currentOrder.status) : -1,
);

const pickupProgressWidth = computed(() => {
  const widths = [12, 46, 78, 100];
  return widths[currentStepIndex.value] ?? 0;
});

const statusBadgeClass = (status: OrderStatus) => {
  if (status === 'picked_up' || status === 'completed')
    return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
  if (status === 'ready_for_pickup')
    return 'bg-amber-50 text-amber-700 border border-amber-200';
  if (status === 'cancelled')
    return 'bg-red-50 text-red-700 border border-red-200';
  if (status === 'preparing')
    return 'bg-blue-50 text-blue-700 border border-blue-200';
  return 'bg-slate-50 text-slate-600 border border-slate-200';
};

const currentOrderHeaderBadge = computed(() => {
  const s = props.currentOrder?.status;
  if (s === 'picked_up' || s === 'completed') return 'bg-emerald-100 text-emerald-800';
  if (s === 'ready_for_pickup') return 'bg-amber-100 text-amber-800';
  if (s === 'preparing') return 'bg-blue-100 text-blue-800';
  return 'bg-white/15 text-white';
});

const formatDate = (iso: string | null) => {
  if (!iso) return '—';
  return new Date(iso).toLocaleDateString('en-PH', {
    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
  });
};

const storeInitials = (name: string) =>
  name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();

const showRecentOrders = computed(() => props.recentOrders.length > 0);

const totalSpent = computed(() =>
  props.recentOrders.reduce((sum, o) => sum + o.total, 0),
);

// Poll for live order status when there's an active order
let pollTimer: ReturnType<typeof setInterval> | null = null;

function startPolling() {
  stopPolling();
  pollTimer = setInterval(() => {
    if (props.currentOrder && !['picked_up', 'cancelled'].includes(props.currentOrder.status)) {
      router.reload({ only: ['currentOrder'] });
    } else {
      stopPolling();
    }
  }, 8000); // every 8 seconds
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
}

onMounted(() => {
  if (props.currentOrder) startPolling();
});

onUnmounted(() => stopPolling());

// Start/stop polling based on whether there's an active order
watch(() => props.currentOrder, (order) => {
  if (order) startPolling();
  else stopPolling();
});

</script>

<template>
  <Head title="Customer Dashboard" />

  <div class="dashboard-wrapper">
    <Header />
    <Sidebar role="customer">
      <CustomerNav />
      <template #icons><CustomerNavIcons /></template>
    </Sidebar>

    <main :class="contentClass">
      <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">

        <!-- Page header -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-[#245c4a] flex items-center gap-1 mb-1">
              <span class="text-[#C5A059]">✦</span> Customer Dashboard
            </p>
            <h1 class="text-2xl font-semibold tracking-tight text-[#163F35]">
              Welcome back
            </h1>
            <p class="mt-1 text-sm text-[#5F766E]">
              Track your pickups and browse available stores.
            </p>
          </div>

          <div class="inline-flex items-center gap-2 self-start rounded-full border border-[#D7E3DC] bg-white px-3 py-1.5 text-sm font-medium text-[#315B4F] shadow-sm">
            <Package class="h-4 w-4 text-[#17493D]" />
            Pickup only
          </div>
        </div>

        <!-- Stat strip -->
        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
          <!-- Orders placed -->
          <div class="relative overflow-hidden rounded-xl border border-border bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
            <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background:linear-gradient(90deg,#245c4a,#3d7a5c)" />
            <div class="mb-3 flex items-start justify-between">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl" style="background:rgba(36,92,74,.1)">
                <ShoppingBag class="h-4 w-4" style="color:#245c4a" />
              </div>
              <span class="inline-flex items-center rounded-full border border-[#bbf7d0] bg-[#f0fdf4] px-2 py-0.5 text-xs font-semibold text-[#166534]">
                Total
              </span>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Orders</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-foreground">
              {{ recentOrders.length + (currentOrder ? 1 : 0) }}
            </p>
          </div>

          <!-- Total spent -->
          <div class="relative overflow-hidden rounded-xl border border-border bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
            <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background:linear-gradient(90deg,#C5A059,#d9b87a)" />
            <div class="mb-3 flex items-start justify-between">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl" style="background:rgba(197,160,89,.14)">
                <TrendingUp class="h-4 w-4" style="color:#7a5800" />
              </div>
              <span class="inline-flex items-center rounded-full border border-[#bbf7d0] bg-[#f0fdf4] px-2 py-0.5 text-xs font-semibold text-[#166534]">
                Lifetime
              </span>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Spent</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-foreground">
              ₱{{ totalSpent.toLocaleString('en-PH', { minimumFractionDigits: 0 }) }}
            </p>
          </div>

          <!-- Stores available -->
          <div class="relative col-span-2 overflow-hidden rounded-xl border border-border bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md sm:col-span-1">
            <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background:linear-gradient(90deg,hsl(197 37% 24%),hsl(197 37% 44%))" />
            <div class="mb-3 flex items-start justify-between">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#EDF6F1]">
                <Store class="h-4 w-4 text-[#245C4A]" />
              </div>
              <span class="inline-flex items-center rounded-full border border-[#bbf7d0] bg-[#f0fdf4] px-2 py-0.5 text-xs font-semibold text-[#166534]">
                Active
              </span>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Stores</p>
            <p class="mt-1 text-3xl font-semibold leading-none text-foreground">{{ stores.length }}</p>
          </div>
        </div>

        <!-- Main grid -->
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_380px]">

          <!-- Available Stores -->
          <div class="rounded-xl border border-border bg-white shadow-sm overflow-hidden">
            <div class="flex items-start justify-between border-b border-border px-5 py-4">
              <div>
                <h2 class="flex items-center gap-2 text-sm font-semibold text-[#245c4a]">
                  <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#C5A059]" />
                  Available Stores
                </h2>
                <p class="mt-0.5 text-xs text-muted-foreground">Browse and order from active stores.</p>
              </div>
              <span class="inline-flex items-center rounded-full border border-[#D8E4DD] bg-[#F7FAF8] px-2.5 py-1 text-xs font-semibold text-[#3C6658]">
                {{ stores.filter(s => s.isOpen).length }} open
              </span>
            </div>

            <div class="divide-y divide-border">
              <div v-if="stores.length === 0" class="flex flex-col items-center justify-center py-14 text-center px-6">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                  <Store class="h-5 w-5 text-muted-foreground" />
                </div>
                <p class="text-sm font-medium text-foreground">No stores available yet</p>
                <p class="mt-1 text-xs text-muted-foreground">Check back soon for new stores.</p>
              </div>

              <div
                v-for="store in stores"
                :key="store.id"
                class="group flex items-center gap-4 px-5 py-3.5 transition-colors hover:bg-accent cursor-pointer"
                @click="$inertia.visit(`/customer/stores/${store.id}`)"
              >
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EAF4EF] text-sm font-semibold text-[#17493D]">
                  {{ storeInitials(store.name) }}
                </div>

                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-semibold text-[#1D463B]">{{ store.name }}</p>
                  <p v-if="store.domain" class="mt-0.5 truncate text-xs text-muted-foreground">
                    {{ store.domain }}
                  </p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                  <span class="hidden rounded-full border px-2 py-0.5 text-xs font-medium sm:inline-flex"
                    :class="store.isOpen
                      ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                      : 'border-red-200 bg-red-50 text-red-600'"
                  >
                    {{ store.isOpen ? 'Open' : 'Closed' }}
                  </span>
                  <ChevronRight class="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                </div>
              </div>
            </div>

            <div v-if="stores.length > 0" class="border-t border-border px-5 py-3">
              <button
                class="flex items-center gap-1 text-xs font-semibold text-[#C5A059] transition-colors hover:text-[#a07a30]"
                @click="$inertia.visit('/customer/stores')"
              >
                View all stores <ArrowRight class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>

          <!-- Current Order -->
          <div class="current-order-container">
            <div class="current-order-header">
              <div>
                <h2 class="current-order-title">Current Order</h2>
                <p class="current-order-subtitle">Your latest pickup at a glance.</p>
              </div>
              <span
                v-if="currentOrder"
                :class="['current-order-status-badge', currentOrderHeaderBadge]"
              >
                {{ statusLabel[currentOrder.status] }}
              </span>
            </div>

            <div class="current-order-content">
              <div v-if="!currentOrder" class="current-order-empty">
                <Package class="current-order-empty-icon" />
                <p class="current-order-empty-title">No active orders</p>
                <p class="current-order-empty-text">Place an order from any store to get started.</p>
                <button
                  class="current-order-browse-btn"
                  @click="$inertia.visit('/customer/stores')"
                >
                  Browse stores <ArrowRight class="h-4 w-4" />
                </button>
              </div>

              <template v-else>
                <div class="current-order-card">
                  <div class="current-order-card-inner">
                    <div class="current-order-card-info">
                      <p class="current-order-number">
                        {{ currentOrder.order_number }}
                      </p>
                      <p class="current-order-store">
                        {{ currentOrder.store }}
                      </p>
                      <p class="current-order-price">
                        ₱{{ currentOrder.total.toFixed(2) }}
                      </p>
                    </div>
                    <div class="current-order-icon-wrapper">
                      <Package class="current-order-icon" />
                    </div>
                  </div>

                  <div class="current-order-date">
                    <Clock3 class="current-order-date-icon" />
                    <div>
                      <p class="current-order-date-label">Ordered</p>
                      <p class="current-order-date-value">{{ formatDate(currentOrder.ordered_at) }}</p>
                    </div>
                  </div>
                </div>

                <div class="current-order-progress">
                  <div class="current-order-progress-header">
                    <p class="current-order-progress-title">Pickup progress</p>
                    <p class="current-order-progress-status">{{ statusLabel[currentOrder.status] }}</p>
                  </div>

                  <div class="current-order-progress-bar">
                    <div
                      class="current-order-progress-fill"
                      :style="{ width: `${pickupProgressWidth}%` }"
                    />
                  </div>

                  <div class="current-order-steps">
                    <div
                      v-for="(step, i) in pickupSteps"
                      :key="step"
                      class="current-order-step"
                      :class="{
                        'step-active': step === currentOrder.status,
                        'step-completed': i < currentStepIndex && step !== currentOrder.status,
                        'step-pending': i > currentStepIndex
                      }"
                    >
                      <span class="current-order-step-number" :class="{
                        'step-number-active': step === currentOrder.status,
                        'step-number-completed': i < currentStepIndex && step !== currentOrder.status,
                        'step-number-pending': i > currentStepIndex
                      }">
                        <CheckCircle2 v-if="i < currentStepIndex" class="h-3.5 w-3.5" />
                        <span v-else>{{ i + 1 }}</span>
                      </span>
                      <span class="current-order-step-label">{{ statusLabel[step] }}</span>
                    </div>
                  </div>
                </div>

                <button
                  class="current-order-details-btn"
                  @click="$inertia.visit('/customer/orders')"
                >
                  View order details <ChevronRight class="h-4 w-4" />
                </button>
              </template>
            </div>
          </div>

          <!-- Bottom: Recent Orders or Explore Stores -->
          <div class="rounded-xl border border-border bg-white shadow-sm overflow-hidden xl:col-span-2">
            <div class="flex items-start justify-between border-b border-border px-5 py-4">
              <div>
                <h2 class="flex items-center gap-2 text-sm font-semibold text-[#245c4a]">
                  <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#C5A059]" />
                  {{ showRecentOrders ? 'Recent Orders' : 'Explore Stores' }}
                </h2>
                <p class="mt-0.5 text-xs text-muted-foreground">
                  {{ showRecentOrders
                    ? 'Your latest completed pickups.'
                    : 'Discover stores available for pickup.' }}
                </p>
              </div>
              <button
                class="text-xs font-semibold text-[#C5A059] transition-colors hover:text-[#a07a30]"
                @click="$inertia.visit(showRecentOrders ? '/customer/orders' : '/customer/stores')"
              >
                View all →
              </button>
            </div>

            <div v-if="showRecentOrders" class="grid gap-0 divide-y divide-border sm:grid-cols-2 sm:divide-y-0 sm:divide-x xl:grid-cols-3">
              <div
                v-for="order in recentOrders"
                :key="order.id"
                class="group flex flex-col gap-3 p-5 transition-colors hover:bg-accent cursor-pointer"
                @click="$inertia.visit(`/customer/stores/${order.store_id}`)"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#EAF4EF] text-xs font-bold text-[#17493D]">
                      {{ storeInitials(order.store) }}
                    </div>
                    <div class="min-w-0">
                      <p class="truncate text-sm font-semibold text-[#1D463B]">{{ order.store }}</p>
                      <p class="text-xs text-muted-foreground">{{ order.order_number }}</p>
                    </div>
                  </div>
                  <span :class="['inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-semibold', statusBadgeClass(order.status)]">
                    {{ statusLabel[order.status] }}
                  </span>
                </div>

                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <div class="flex items-center gap-1.5">
                    <Clock3 class="h-3.5 w-3.5" />
                    {{ formatDate(order.ordered_at) }}
                  </div>
                  <span class="font-semibold text-[#1D463B]">₱{{ order.total.toFixed(2) }}</span>
                </div>

                <button class="flex items-center gap-1 text-xs font-semibold text-[#17493D] transition-colors hover:text-[#10362D]">
                  <RotateCcw class="h-3.5 w-3.5" /> Reorder
                </button>
              </div>
            </div>

            <div v-else class="grid gap-0 divide-y divide-border sm:grid-cols-2 sm:divide-y-0 sm:divide-x xl:grid-cols-3">
              <div
                v-for="store in stores.slice(0, 6)"
                :key="store.id"
                class="group flex flex-col gap-3 p-5 transition-colors hover:bg-accent cursor-pointer"
                @click="$inertia.visit(`/customer/stores/${store.id}`)"
              >
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EAF4EF] text-sm font-bold text-[#17493D]">
                    {{ storeInitials(store.name) }}
                  </div>
                  <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-[#1D463B]">{{ store.name }}</p>
                    <p v-if="store.domain" class="truncate text-xs text-muted-foreground">{{ store.domain }}</p>
                  </div>
                </div>

                <div class="flex items-center justify-between">
                  <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium"
                    :class="store.isOpen
                      ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                      : 'border-red-200 bg-red-50 text-red-600'"
                  >
                    <span class="h-1.5 w-1.5 rounded-full"
                      :class="store.isOpen ? 'bg-emerald-500' : 'bg-red-400'"
                    />
                    {{ store.isOpen ? 'Open' : 'Closed' }}
                  </span>
                  <button class="flex items-center gap-1 text-xs font-semibold text-[#17493D] transition-colors hover:text-[#10362D]">
                    Browse <ChevronRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.current-order-container {
  overflow: hidden;
  border-radius: 0.75rem;
  border-width: 0;
  box-shadow: 0 18px 40px rgba(23, 73, 61, 0.18);
  background: #17493D;
}

.current-order-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 1rem 1.25rem;
}

.current-order-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
}

.current-order-subtitle {
  margin-top: 0.125rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
}

.current-order-status-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 9999px;
  padding: 0.25rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.current-order-content {
  padding: 0 1.25rem 1.25rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.current-order-empty {
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  padding: 1.5rem;
  text-align: center;
}

.current-order-empty-icon {
  margin-left: auto;
  margin-right: auto;
  margin-bottom: 0.75rem;
  height: 2rem;
  width: 2rem;
  color: rgba(255, 255, 255, 0.3);
}

.current-order-empty-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.8);
}

.current-order-empty-text {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.current-order-browse-btn {
  margin-top: 1rem;
  display: inline-flex;
  height: 2.25rem;
  align-items: center;
  gap: 0.5rem;
  border-radius: 0.75rem;
  background: white;
  padding-left: 1rem;
  padding-right: 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #17493D;
  transition-property: background-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.current-order-browse-btn:hover {
  background: #F4F0E8;
}

.current-order-card {
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  padding: 1rem;
}

.current-order-card-inner {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
}

.current-order-card-info {
  min-width: 0;
}

.current-order-number {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  color: rgba(255, 255, 255, 0.5);
}

.current-order-store {
  margin-top: 0.375rem;
  font-size: 1.125rem;
  font-weight: 600;
  line-height: 1.25;
  color: white;
}

.current-order-price {
  margin-top: 0.25rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #FFD88A;
}

.current-order-icon-wrapper {
  display: flex;
  height: 2.75rem;
  width: 2.75rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
}

.current-order-icon {
  height: 1.25rem;
  width: 1.25rem;
  color: white;
}

.current-order-date {
  margin-top: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  padding: 0.625rem 0.75rem;
}

.current-order-date-icon {
  height: 1rem;
  width: 1rem;
  flex-shrink: 0;
  color: rgba(255, 255, 255, 0.6);
}

.current-order-date-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  color: rgba(255, 255, 255, 0.5);
}

.current-order-date-value {
  font-size: 0.75rem;
  font-weight: 500;
  color: white;
}

.current-order-progress {
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  padding: 1rem;
}

.current-order-progress-header {
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.current-order-progress-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.current-order-progress-status {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.55);
}

.current-order-progress-bar {
  height: 0.375rem;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.1);
}

.current-order-progress-fill {
  height: 0.375rem;
  border-radius: 9999px;
  background: #FFD88A;
  transition-property: width;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 700ms;
}

.current-order-steps {
  margin-top: 0.75rem;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.5rem;
}

.current-order-step {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 0.75rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.75rem;
}

.current-order-step.step-active {
  background: #FFF1CB;
  color: #8A5200;
}

.current-order-step.step-completed {
  background: white;
  color: #17493D;
}

.current-order-step.step-pending {
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.55);
}

.current-order-step-number {
  display: flex;
  height: 1.25rem;
  width: 1.25rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  font-size: 10px;
  font-weight: 700;
}

.current-order-step-number.step-number-active {
  background: #F7D98C;
  color: #7A4700;
}

.current-order-step-number.step-number-completed {
  background: #EAF3EE;
  color: #17493D;
}

.current-order-step-number.step-number-pending {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.55);
}

.current-order-step-label {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.current-order-details-btn {
  display: flex;
  height: 2.75rem;
  width: 100%;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border-radius: 0.75rem;
  background: white;
  font-size: 0.875rem;
  font-weight: 500;
  color: #17493D;
  transition-property: background-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.current-order-details-btn:hover {
  background: #F4F0E8;
}
</style>