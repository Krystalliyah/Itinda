<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import VendorLayout from '@/layouts/VendorLayout.vue';
import InputError from '@/components/InputError.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import { useSidebar } from '@/composables/useSidebar';
import Header from '@/components/Header.vue';

const { isCollapsed } = useSidebar();
const page = usePage();
const auth = computed(() => page.props.auth as { user: any; hasStore: boolean; storeIsApproved: boolean });
const hasStore = computed(() => auth.value.hasStore);
const storeIsApproved = computed(() => auth.value.storeIsApproved);

const tenantInfo = computed(() => (page.props as any).tenantInfo);
const products = computed(() => (page.props as any).products || []);
const productCount = computed(() => (page.props as any).productCount || 0);

// Stat
const stats = computed(() => {
  const p = page.props as any;
  const s = p.stats || {};

  return {
    totalSales: s.totalSales ?? p.totalSales ?? 0,

    orders:
      s.orders ??
      p.orderCount ??
      p.ordersCount ??
      p.totalOrders ??
      (p.recentOrders?.length ?? 0),

    products: s.products ?? p.productCount ?? (p.products?.length ?? 0),

    customers: s.customers ?? p.customerCount ?? p.customersCount ?? (p.customers?.length ?? 0),
  };
});

const recentOrders = computed(() => (page.props as any).recentOrders || []);
const lowStockItems = computed(() => (page.props as any).lowStockItems || []);

/** ✅ Modal state */
const showStoreSetupModal = ref(false);

/** ✅ Store setup form */
const form = useForm({
  store_name: '',
  domain_slug: '',
  address: '',
  city: '',
  phone: '',
  operating_hours: '',
});

/** Optional: lock body scroll when modal is open */
watch(showStoreSetupModal, (open) => {
  document.body.style.overflow = open ? 'hidden' : '';
});

function openStoreSetupModal() {
  showStoreSetupModal.value = true;
}

function closeStoreSetupModal() {
  showStoreSetupModal.value = false;
  form.reset();
  form.clearErrors();
}

function submitStoreSetup() {
  form.post('/vendor/store/setup', {
    onSuccess: () => {
      closeStoreSetupModal();
    },
  });
}

function statusClass(status: string) {
  const map: Record<string, string> = {
    pending: 'badge-status-pending',
    preparing: 'badge-status-preparing',
    completed: 'badge-status-done',
    cancelled: 'badge-status-cancelled',
  };
  return map[status?.toLowerCase()] ?? 'badge-status-pending';
}

function stockClass(level: string) {
  return level?.toLowerCase() === 'critical' ? 'badge-destructive' : 'badge-warning';
}
</script>

<template>
  <Head title="Vendor Dashboard" />

  <div class="dashboard-wrapper">
    <Header />
    <Sidebar role="vendor">
            <VendorNav />
        </Sidebar>

  <VendorLayout>
    <!-- ── No store yet ── -->
    <div v-if="!hasStore" class="flex items-center justify-center min-h-[calc(100vh-4rem)] p-6">
      <div class="w-full max-w-md bg-white rounded-xl border border-border shadow-sm overflow-hidden">
        <div class="px-6 py-8 text-center" style="background:#245c4a">
          <div
            class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-4"
            style="background:rgba(197,160,89,0.2)"
          >
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
              />
            </svg>
          </div>
          <h2 class="text-xl font-semibold text-white mb-1">Complete Your Store Setup</h2>
          <p class="text-sm" style="color:rgba(255,255,255,0.75)">
            Get started by adding your store information
          </p>
        </div>

        <div class="p-6 space-y-4">
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#ecfdf5">
              <svg class="w-4 h-4" style="color:#245c4a" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-foreground">Manage Your Inventory</p>
              <p class="text-sm text-muted-foreground">Track products, stock levels, and pricing</p>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#fffbeb">
              <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-foreground">Accept Online Orders</p>
              <p class="text-sm text-muted-foreground">Customers can browse and pre-order</p>
            </div>
          </div>

          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-blue-50">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-foreground">Track Your Sales</p>
              <p class="text-sm text-muted-foreground">View analytics and insights</p>
            </div>
          </div>
        </div>

        <div class="px-6 pb-6">
          <button
            @click="openStoreSetupModal"
            class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-md text-sm font-medium text-white transition-opacity hover:opacity-90"
            style="background:#245c4a"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            Setup Store
          </button>
          <p class="text-center text-xs text-muted-foreground mt-2">Less than 2 minutes to complete</p>
        </div>
      </div>
    </div>

    <!-- ── Pending approval ── -->
    <div
      v-else-if="hasStore && !storeIsApproved"
      class="flex items-center justify-center min-h-[calc(100vh-4rem)] p-6"
    >
      <div class="w-full max-w-md bg-white rounded-xl border border-border shadow-sm text-center p-10">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 bg-amber-50">
          <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-foreground mb-2">Awaiting Approval</h2>
        <p class="text-sm text-muted-foreground mb-6 max-w-sm mx-auto">
          Your store registration has been submitted and is currently being reviewed by our administrators.
        </p>
        <div
          class="inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-full border"
          style="background:#fffbeb;color:#92400e;border-color:#fde68a"
        >
          <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
            />
          </svg>
          Pending Review
        </div>
      </div>
    </div>

    <!-- ── Active dashboard ── -->
    <div v-else class="p-6 flex flex-col gap-5">
      <!-- Heading -->
      <div class="flex items-start justify-between gap-5">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest mb-1 flex items-center gap-1" style="color:#245c4a">
            <span style="color:#C5A059">✦</span> Vendor Dashboard
          </p>
          <h1 class="text-2xl font-semibold tracking-tight" style="color:#245c4a">
            Welcome back, <em class="not-italic" style="color:#C5A059">{{ auth.user?.name }}</em>
          </h1>
          <p class="text-sm text-muted-foreground mt-1">Here's your store overview for today.</p>
        </div>

        <!-- Revenue summary -->
        <div class="rounded-xl px-5 py-3.5 flex-shrink-0 shadow-md relative overflow-hidden" style="background:#245c4a;min-width:160px">
          <div class="absolute -top-6 -right-6 w-20 h-20 rounded-full" style="background:radial-gradient(circle,rgba(197,160,89,.2) 0%,transparent 65%)"></div>
          <p class="text-xs font-semibold uppercase tracking-widest" style="color:rgba(197,160,89,0.6)">
            Monthly Revenue
          </p>
          <p class="text-2xl font-semibold mt-0.5" style="color:#d9b87a">
            ₱{{ (stats.totalSales ?? 0).toLocaleString() }}
          </p>
        </div>
      </div>

      <!-- Stat cards -->
      <div class="grid grid-cols-4 gap-3">
        <!-- Orders -->
        <div class="bg-white rounded-xl border border-border shadow-sm p-4 relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-default group">
          <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background:linear-gradient(90deg,#245c4a,#3d7a5c)"></div>
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-md flex items-center justify-center" style="background:rgba(36,92,74,.1)">
              <svg class="w-4 h-4" style="color:#245c4a" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0">
              This month
            </span>
          </div>
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Orders</p>
          <p class="text-3xl font-semibold text-foreground leading-none mb-2">{{ stats.orders ?? 0 }}</p>
          <div class="h-1 rounded-full" style="background:hsl(0 0% 92.1%)">
            <div class="h-full rounded-full transition-all duration-1000" style="width:58%;background:linear-gradient(90deg,#245c4a,#3d7a5c)"></div>
          </div>
        </div>

        <!-- Sales -->
        <div class="bg-white rounded-xl border border-border shadow-sm p-4 relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-default">
          <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background:linear-gradient(90deg,#C5A059,#d9b87a)"></div>
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-md flex items-center justify-center" style="background:rgba(197,160,89,.14)">
              <svg class="w-4 h-4" style="color:#7a5800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0">
              This month
            </span>
          </div>
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Total Sales</p>
          <p class="text-3xl font-semibold text-foreground leading-none mb-2">
            ₱{{ (stats.totalSales ?? 0).toLocaleString() }}
          </p>
          <div class="h-1 rounded-full" style="background:hsl(0 0% 92.1%)">
            <div class="h-full rounded-full transition-all duration-1000" style="width:74%;background:linear-gradient(90deg,#C5A059,#d9b87a)"></div>
          </div>
        </div>

        <!-- Products -->
        <div class="bg-white rounded-xl border border-border shadow-sm p-4 relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-default">
          <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background:linear-gradient(90deg,hsl(197 37% 24%),hsl(197 37% 44%))"></div>
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-md flex items-center justify-center bg-blue-50">
              <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <span v-if="lowStockItems.length > 0" class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a">
              {{ lowStockItems.length }} low stock
            </span>
          </div>
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Products</p>
          <p class="text-3xl font-semibold text-foreground leading-none mb-2">{{ stats.products ?? 0 }}</p>
          <div class="h-1 rounded-full" style="background:hsl(0 0% 92.1%)">
            <div class="h-full rounded-full transition-all duration-1000" style="width:42%;background:linear-gradient(90deg,hsl(197 37% 24%),hsl(197 37% 44%))"></div>
          </div>
        </div>

        <!-- Customers -->
        <div class="bg-white rounded-xl border border-border shadow-sm p-4 relative overflow-hidden transition-all hover:-translate-y-0.5 hover:shadow-md cursor-default">
          <div class="absolute bottom-0 left-0 right-0 h-[3px]" style="background:linear-gradient(90deg,hsl(12 76% 61%),hsl(27 87% 67%))"></div>
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-md flex items-center justify-center" style="background:hsl(12 76% 61% / 0.1)">
              <svg class="w-4 h-4" style="color:hsl(12 76% 38%)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0">
              This month
            </span>
          </div>
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">Customers</p>
          <p class="text-3xl font-semibold text-foreground leading-none mb-2">{{ stats.customers ?? 0 }}</p>
          <div class="h-1 rounded-full" style="background:hsl(0 0% 92.1%)">
            <div class="h-full rounded-full transition-all duration-1000" style="width:62%;background:linear-gradient(90deg,hsl(12 76% 61%),hsl(27 87% 67%))"></div>
          </div>
        </div>
      </div>

      <!-- Lower grid -->
      <div class="grid gap-4" style="grid-template-columns:1fr 310px">
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl border border-border shadow-sm overflow-hidden">
          <div class="px-5 py-4 flex items-start justify-between border-b border-border">
            <div>
              <h2 class="text-sm font-semibold flex items-center gap-2" style="color:#245c4a">
                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#C5A059"></span>
                Recent Orders
              </h2>
              <p class="text-xs text-muted-foreground mt-0.5">Latest customer orders</p>
            </div>
            <a
              href="/vendor/orders"
              class="text-xs font-semibold px-2 py-1 rounded transition-colors"
              style="color:#C5A059"
              onmouseover="this.style.background='#f5ead4'"
              onmouseout="this.style.background='transparent'"
            >
              View all →
            </a>
          </div>

          <div v-if="recentOrders.length === 0" class="flex flex-col items-center justify-center py-14 text-center px-6">
            <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3" style="background:hsl(0 0% 96.1%)">
              <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <p class="text-sm font-medium text-foreground">No orders yet</p>
            <p class="text-xs text-muted-foreground mt-1">Orders will appear here once customers start buying</p>
          </div>

          <table v-else class="w-full border-collapse">
            <thead>
              <tr style="background:hsl(0 0% 96.1%)">
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-2.5 border-b border-border">
                  Order ID
                </th>
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-2.5 border-b border-border">
                  Customer
                </th>
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-2.5 border-b border-border">
                  Items
                </th>
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-2.5 border-b border-border">
                  Amount
                </th>
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-2.5 border-b border-border">
                  Status
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="order in recentOrders"
                :key="order.id"
                class="border-b border-border last:border-0 cursor-pointer transition-colors hover:bg-accent"
              >
                <td class="px-5 py-3 text-xs font-semibold font-mono" style="color:#245c4a">#{{ order.id }}</td>
                <td class="px-5 py-3">
                  <div class="flex items-center gap-2">
                    <div
                      class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                      style="background:hsl(0 0% 92.1%);color:hsl(0 0% 45.1%)"
                    >
                      {{ order.customer?.name?.charAt(0) }}
                    </div>
                    <span class="text-sm">{{ order.customer?.name }}</span>
                  </div>
                </td>
                <td class="px-5 py-3 text-xs text-muted-foreground">{{ order.items_summary }}</td>
                <td class="px-5 py-3 text-sm font-semibold">₱{{ Number(order.total).toFixed(2) }}</td>
                <td class="px-5 py-3">
                  <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full" :class="statusClass(order.status)">
                    <span
                      class="w-1.5 h-1.5 rounded-full"
                      :class="{
                        'bg-amber-500': order.status === 'pending',
                        'bg-blue-500': order.status === 'preparing',
                        'bg-green-500': order.status === 'completed',
                        'bg-red-500': order.status === 'cancelled',
                      }"
                    ></span>
                    {{ order.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Right column -->
        <div class="flex flex-col gap-4">
          <!-- Quick Actions -->
          <div class="bg-white rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
              <h2 class="text-sm font-semibold flex items-center gap-2" style="color:#245c4a">
                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#C5A059"></span>
                Quick Actions
              </h2>
              <p class="text-xs text-muted-foreground mt-0.5">Shortcuts to common tasks</p>
            </div>

            <div class="grid grid-cols-2 gap-2 p-3">
              <a
                v-for="action in [
                  { label: 'Add Product', sub: 'Build inventory', href: '/vendor/products', icon: 'plus', color: 'emerald' },
                  { label: 'View Orders', sub: 'Manage queue', href: '/vendor/orders', icon: 'orders', color: 'gold' },
                  { label: 'Analytics', sub: 'View trends', href: '/vendor/analytics', icon: 'chart', color: 'blue' },
                  { label: 'Store Settings', sub: 'Update details', href: '/vendor/store-settings', icon: 'settings', color: 'coral' },
                ]"
                :key="action.label"
                :href="action.href"
                class="flex flex-col gap-1.5 p-3 rounded-lg border border-border bg-white
                       text-decoration-none transition-all duration-150 cursor-pointer
                       hover:border-[#C5A059] hover:-translate-y-px"
                style="text-decoration:none"
              >
                <div
                  class="w-7 h-7 rounded flex items-center justify-center"
                  :style="{
                    background:
                      action.color === 'emerald'
                        ? 'rgba(36,92,74,.1)'
                        : action.color === 'gold'
                        ? 'rgba(197,160,89,.14)'
                        : action.color === 'blue'
                        ? 'hsl(197 37% 24% / .1)'
                        : 'hsl(12 76% 61% / .1)',
                    color:
                      action.color === 'emerald'
                        ? '#245c4a'
                        : action.color === 'gold'
                        ? '#7a5800'
                        : action.color === 'blue'
                        ? 'hsl(197 37% 24%)'
                        : 'hsl(12 76% 38%)',
                  }"
                >
                  <svg v-if="action.icon === 'plus'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  <svg v-if="action.icon === 'orders'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                  </svg>
                  <svg v-if="action.icon === 'chart'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  <svg v-if="action.icon === 'settings'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>

                <p class="text-xs font-semibold text-foreground">{{ action.label }}</p>
                <p class="text-xs text-muted-foreground -mt-0.5">{{ action.sub }}</p>
              </a>
            </div>
          </div>

          <!-- Stock Alerts -->
          <div class="bg-white rounded-xl border border-border shadow-sm overflow-hidden flex-1">
            <div class="px-5 py-4 flex items-start justify-between border-b border-border">
              <div>
                <h2 class="text-sm font-semibold flex items-center gap-2" style="color:#245c4a">
                  <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 bg-amber-400"></span>
                  Stock Alerts
                </h2>
                <p class="text-xs text-muted-foreground mt-0.5">Items needing restock</p>
              </div>
              <a
                href="/vendor/inventory"
                class="text-xs font-semibold px-2 py-1 rounded transition-colors"
                style="color:#C5A059"
                onmouseover="this.style.background='#f5ead4'"
                onmouseout="this.style.background='transparent'"
              >
                Restock →
              </a>
            </div>

            <div v-if="lowStockItems.length === 0" class="flex flex-col items-center justify-center py-10 text-center px-4">
              <svg class="w-8 h-8 text-muted-foreground mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <p class="text-sm font-medium text-foreground">All stocked up</p>
              <p class="text-xs text-muted-foreground mt-0.5">No items are running low</p>
            </div>

            <div v-else>
              <div
                v-for="item in lowStockItems"
                :key="item.id"
                class="flex items-center gap-3 px-4 py-3 border-b border-border last:border-0 transition-colors cursor-pointer hover:bg-accent"
              >
                <div class="w-8 h-8 rounded flex items-center justify-center text-base flex-shrink-0" style="background:hsl(0 0% 96.1%)">
                  {{ item.emoji ?? '📦' }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-foreground truncate">{{ item.name }}</p>
                  <p class="text-xs text-muted-foreground mt-0.5">{{ item.sku }} · {{ item.stock }} left</p>
                </div>

                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                  <span
                    class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="stockClass(item.level)"
                    :style="
                      item.level === 'Critical'
                        ? 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca'
                        : 'background:#fffbeb;color:#92400e;border:1px solid #fde68a'
                    "
                  >
                    {{ item.level }}
                  </span>

                  <div class="w-10 h-1 rounded-full" style="background:hsl(0 0% 92.1%)">
                    <div
                      class="h-full rounded-full"
                      :style="{
                        width: item.stockPercent + '%',
                        background: item.level === 'Critical' ? 'hsl(0 84.2% 60.2%)' : '#f59e0b',
                      }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="px-4 py-3 border-t border-border flex items-center justify-end gap-2">
              <a
                href="/vendor/inventory"
                class="inline-flex items-center justify-center text-xs font-medium px-3 py-1.5 rounded-md border border-border bg-white transition-colors hover:bg-accent !opacity-100"
                style="text-decoration:none; opacity:1 !important; background:hsl(0 0% 9%) !important;"
              >
                View All
              </a>
              <a
                href="/vendor/inventory"
                class="inline-flex items-center justify-center text-xs font-medium px-3 py-1.5 rounded-md text-white transition-opacity hover:opacity-90"
                style="background:hsl(0 0% 9%);text-decoration:none"
              >
                Restock All
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Modal (RESPONSIVE + SCROLLABLE) -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showStoreSetupModal" class="fixed inset-0 z-[9999] flex items-center justify-center px-4 py-4 sm:py-6">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeStoreSetupModal"></div>

        <!-- Panel -->
        <div
          class="relative w-full sm:max-w-lg bg-white rounded-xl sm:rounded-2xl shadow-2xl overflow-hidden
                 max-h-[calc(100vh-2rem)] flex flex-col"
        >
          <!-- Header (fixed inside modal panel) -->
          <div class="px-6 pt-5 pb-5 flex-shrink-0" style="background:#245c4a">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                  style="background:rgba(255,255,255,0.15)"
                >
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    />
                  </svg>
                </div>
                <div>
                  <h3 class="text-base font-bold text-white">Setup Your Store</h3>
                  <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.8)">
                    Fill in the details below to get started
                  </p>
                </div>
              </div>

              <button
                type="button"
                @click="closeStoreSetupModal"
                class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors flex-shrink-0"
                style="background:rgba(255,255,255,0.10);color:#fff"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Body (SCROLLABLE) -->
          <div class="overflow-y-auto px-6 py-5">
            <form @submit.prevent="submitStoreSetup" class="space-y-4">
              <!-- Store Name -->
              <div>
                <label for="store_name" class="block text-sm font-semibold text-gray-800 mb-1.5">
                  Store Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="store_name"
                  v-model="form.store_name"
                  type="text"
                  required
                  placeholder="e.g. Maria's Sari-Sari Store"
                  :class="[
                    'w-full rounded-xl border px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 transition',
                    form.errors.store_name
                      ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-500/20'
                      : 'border-gray-200 bg-gray-50 focus:bg-white'
                  ]"
                />
                <InputError :message="form.errors.store_name" class="mt-1" />
              </div>

              <!-- Domain Slug -->
              <div>
                <label for="domain_slug" class="block text-sm font-semibold text-gray-800 mb-1.5">
                  Web Address (Domain Alias) <span class="text-red-500">*</span>
                </label>
                <div class="relative flex items-center">
                  <input
                    id="domain_slug"
                    v-model="form.domain_slug"
                    type="text"
                    required
                    placeholder="mariastore"
                    class="w-full rounded-l-xl border border-gray-200 border-r-0 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition"
                  />
                  <span class="inline-flex items-center px-3 py-2.5 rounded-r-xl border border-l-0 border-gray-200 bg-gray-100 text-gray-500 text-sm">
                    .storekoto.test
                  </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Letters, numbers, and hyphens only.</p>
                <InputError :message="form.errors.domain_slug" class="mt-1" />
              </div>

              <!-- Address -->
              <div>
                <label for="address" class="block text-sm font-semibold text-gray-800 mb-1.5">
                  Address <span class="text-red-500">*</span>
                </label>
                <textarea
                  id="address"
                  v-model="form.address"
                  required
                  placeholder="e.g. 123 Main Street, Barangay San Isidro"
                  rows="2"
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition resize-none"
                ></textarea>
                <InputError :message="form.errors.address" class="mt-1" />
              </div>

              <!-- City + Phone -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label for="city" class="block text-sm font-semibold text-gray-800 mb-1.5">City</label>
                  <input
                    id="city"
                    v-model="form.city"
                    type="text"
                    placeholder="e.g. Quezon City"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition"
                  />
                  <InputError :message="form.errors.city" class="mt-1" />
                </div>
                <div>
                  <label for="phone" class="block text-sm font-semibold text-gray-800 mb-1.5">Phone</label>
                  <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    placeholder="+63 912 345 6789"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition"
                  />
                  <InputError :message="form.errors.phone" class="mt-1" />
                </div>
              </div>

              <!-- Operating Hours -->
              <div>
                <label for="operating_hours" class="block text-sm font-semibold text-gray-800 mb-1.5">
                  Operating Hours
                </label>
                <input
                  id="operating_hours"
                  v-model="form.operating_hours"
                  type="text"
                  placeholder="e.g. Mon–Sat 8AM–8PM"
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 transition"
                />
                <InputError :message="form.errors.operating_hours" class="mt-1" />
              </div>

              <!-- Actions -->
              <div class="border-t border-gray-100 pt-3 flex items-center justify-end gap-2">
                <button
                  type="button"
                  @click="closeStoreSetupModal"
                  class="px-4 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                >
                  Cancel
                </button>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
                  style="background:#245c4a"
                >
                  <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  <span>{{ form.processing ? 'Creating...' : 'Create Store' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </VendorLayout>
  </div>
</template>

<style scoped>
.badge-status-pending   { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.badge-status-preparing { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.badge-status-done      { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.badge-status-cancelled { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }
</style>