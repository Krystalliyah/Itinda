<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
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

import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { Button } from '@/components/ui/button';

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
                {{ stores.length }} open
              </span>
            </div>

            <div class="divide-y divide-border">
              <!-- Empty state -->
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
                <!-- Avatar -->
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
                  <span class="hidden rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 sm:inline-flex">
                    Open
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
          <div class="overflow-hidden rounded-xl border-0 shadow-[0_18px_40px_rgba(23,73,61,0.18)]" style="background:#17493D">
            <div class="flex items-start justify-between px-5 py-4">
              <div>
                <h2 class="text-sm font-semibold text-white">Current Order</h2>
                <p class="mt-0.5 text-xs text-white/60">Your latest pickup at a glance.</p>
              </div>
              <span
                v-if="currentOrder"
                :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', currentOrderHeaderBadge]"
              >
                {{ statusLabel[currentOrder.status] }}
              </span>
            </div>

            <div class="px-5 pb-5 space-y-3">
              <!-- Empty state -->
              <div v-if="!currentOrder" class="rounded-2xl border border-white/10 bg-white/5 p-6 text-center">
                <Package class="mx-auto mb-3 h-8 w-8 text-white/30" />
                <p class="text-sm font-medium text-white/80">No active orders</p>
                <p class="mt-1 text-xs text-white/50">Place an order from any store to get started.</p>
                <button
                  class="mt-4 inline-flex h-9 items-center gap-2 rounded-xl bg-white px-4 text-sm font-medium text-[#17493D] transition-colors hover:bg-[#F4F0E8]"
                  @click="$inertia.visit('/customer/stores')"
                >
                  Browse stores <ArrowRight class="h-4 w-4" />
                </button>
              </div>

              <!-- Active order card -->
              <template v-else>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <p class="text-[11px] uppercase tracking-[0.18em] text-white/50">
                        {{ currentOrder.order_number }}
                      </p>
                      <p class="mt-1.5 text-lg font-semibold leading-tight text-white">
                        {{ currentOrder.store }}
                      </p>
                      <p class="mt-1 text-sm font-semibold text-[#FFD88A]">
                        ₱{{ currentOrder.total.toFixed(2) }}
                      </p>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
                      <Package class="h-5 w-5 text-white" />
                    </div>
                  </div>

                  <div class="mt-3 flex items-center gap-2 rounded-xl bg-white/5 px-3 py-2.5">
                    <Clock3 class="h-4 w-4 shrink-0 text-white/60" />
                    <div>
                      <p class="text-[10px] uppercase tracking-wide text-white/50">Ordered</p>
                      <p class="text-xs font-medium text-white">{{ formatDate(currentOrder.ordered_at) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Progress tracker -->
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                  <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-semibold text-white">Pickup progress</p>
                    <p class="text-xs text-white/55">{{ statusLabel[currentOrder.status] }}</p>
                  </div>

                  <div class="h-1.5 rounded-full bg-white/10">
                    <div
                      class="h-1.5 rounded-full bg-[#FFD88A] transition-all duration-700"
                      :style="{ width: `${pickupProgressWidth}%` }"
                    />
                  </div>

                  <div class="mt-3 grid grid-cols-2 gap-2">
                    <div
                      v-for="(step, i) in pickupSteps"
                      :key="step"
                      class="flex items-center gap-2 rounded-xl px-3 py-2 text-xs"
                      :class="step === currentOrder.status
                        ? 'bg-[#FFF1CB] text-[#8A5200]'
                        : i < currentStepIndex
                          ? 'bg-white text-[#17493D]'
                          : 'border border-white/10 bg-white/5 text-white/55'"
                    >
                      <span
                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                        :class="step === currentOrder.status
                          ? 'bg-[#F7D98C] text-[#7A4700]'
                          : i < currentStepIndex
                            ? 'bg-[#EAF3EE] text-[#17493D]'
                            : 'bg-white/10 text-white/55'"
                      >
                        <CheckCircle2 v-if="i < currentStepIndex" class="h-3.5 w-3.5" />
                        <span v-else>{{ i + 1 }}</span>
                      </span>
                      <span class="truncate">{{ statusLabel[step] }}</span>
                    </div>
                  </div>
                </div>

                <button
                  class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-white text-sm font-medium text-[#17493D] transition-colors hover:bg-[#F4F0E8]"
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

            <!-- Recent orders grid -->
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

            <!-- Stores grid fallback -->
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
                  <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                    Open
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
