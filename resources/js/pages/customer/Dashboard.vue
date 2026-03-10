<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  ChevronRight,
  Clock3,
  Heart,
  MapPin,
  Package,
  Star,
  Store,
} from 'lucide-vue-next';

import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';

import { Button } from '@/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

const { isCollapsed } = useSidebar();

const contentClass = computed(() => ({
  'dashboard-content': true,
  'sidebar-collapsed': isCollapsed.value,
}));

type PickupStatus = 'Preparing' | 'Ready for pickup' | 'Picked up';

type StoreCard = {
  name: string
  category: string
  meta: string
  rating: string
  note: string
};

type CurrentOrder = {
  id: string
  store: string
  pickupTime: string
  pickupLocation: string
  status: PickupStatus
};

type RecentOrder = {
  id: string
  store: string
  pickupTime: string
  status: PickupStatus
};

const currentOrder: CurrentOrder = {
  id: 'SK-4021',
  store: 'Emerald Fresh Market',
  pickupTime: 'Today, 4:00 PM - 5:00 PM',
  pickupLocation: 'StoreKoto Pickup Counter A',
  status: 'Ready for pickup',
};

const favoriteStores: StoreCard[] = [
  {
    name: 'Emerald Fresh Market',
    category: 'Groceries & produce',
    meta: 'Open now',
    rating: '4.9',
    note: 'Your most ordered store this month.',
  },
  {
    name: 'Golden Basket',
    category: 'Pantry essentials',
    meta: 'Open now',
    rating: '4.8',
    note: 'Great for staple bundles and quick reorders.',
  },
  {
    name: 'Bayan Brew & Bakes',
    category: 'Coffee & pastries',
    meta: 'Open now',
    rating: '4.7',
    note: 'A favorite for snack and drink pickups.',
  },
];

const recentOrders: RecentOrder[] = [
  {
    id: 'SK-4019',
    store: 'Golden Basket',
    pickupTime: 'Yesterday, 3:00 PM - 4:00 PM',
    status: 'Picked up',
  },
  {
    id: 'SK-4018',
    store: 'Bayan Brew & Bakes',
    pickupTime: 'Mar 08, 1:00 PM - 2:00 PM',
    status: 'Picked up',
  },
];

const recommendedStores: StoreCard[] = [
  {
    name: 'Harvest Corner',
    category: 'Fresh produce',
    meta: 'Open until 8:00 PM',
    rating: '4.9',
    note: 'Known for fruit boxes and fresh vegetable bundles.',
  },
  {
    name: 'Koto Daily Goods',
    category: 'Household essentials',
    meta: 'Open until 9:00 PM',
    rating: '4.8',
    note: 'A solid pick for everyday home essentials.',
  },
  {
    name: 'Bayan Brew & Bakes',
    category: 'Coffee & pastries',
    meta: 'Open until 7:00 PM',
    rating: '4.7',
    note: 'Good for snack pickups and coffee bundles.',
  },
];

const showRecentOrders = computed(() => recentOrders.length > 0);

const bottomSectionTitle = computed(() =>
  showRecentOrders.value ? 'Recent Orders' : 'Recommended Stores',
);

const bottomSectionDescription = computed(() =>
  showRecentOrders.value
    ? 'Quickly reorder from your latest pickup activity.'
    : 'Suggested stores to explore next.',
);

const pickupSteps = computed(() => {
  if (currentOrder.status === 'Picked up') {
    return [
      { label: 'Order placed', done: true, current: false },
      { label: 'Preparing', done: true, current: false },
      { label: 'Ready for pickup', done: true, current: false },
      { label: 'Picked up', done: true, current: true },
    ];
  }

  if (currentOrder.status === 'Ready for pickup') {
    return [
      { label: 'Order placed', done: true, current: false },
      { label: 'Preparing', done: true, current: false },
      { label: 'Ready for pickup', done: true, current: true },
      { label: 'Picked up', done: false, current: false },
    ];
  }

  return [
    { label: 'Order placed', done: true, current: false },
    { label: 'Preparing', done: true, current: true },
    { label: 'Ready for pickup', done: false, current: false },
    { label: 'Picked up', done: false, current: false },
  ];
});

const pickupProgressWidth = computed(() => {
  if (currentOrder.status === 'Picked up') return 100;
  if (currentOrder.status === 'Ready for pickup') return 78;
  return 46;
});

const currentOrderBadgeClass = computed(() => {
  if (currentOrder.status === 'Picked up') {
    return 'bg-emerald-100 text-emerald-800';
  }

  if (currentOrder.status === 'Ready for pickup') {
    return 'bg-amber-100 text-amber-800';
  }

  return 'bg-white/10 text-white';
});

const orderStatusClass = (status: PickupStatus) => {
  if (status === 'Picked up') {
    return 'border border-emerald-200 bg-emerald-50 text-emerald-700';
  }

  if (status === 'Ready for pickup') {
    return 'border border-amber-200 bg-amber-50 text-amber-700';
  }

  return 'border border-slate-200 bg-slate-50 text-slate-600';
};
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
      <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#163F35]">
              Dashboard
            </h1>
            <p class="mt-1 text-sm text-[#5F766E]">
              Keep track of pickups, jump back into your favorites, and reorder faster.
            </p>
          </div>

          <div
            class="inline-flex items-center gap-2 self-start rounded-full border border-[#D7E3DC] bg-white px-3 py-1.5 text-sm font-medium text-[#315B4F] shadow-sm"
          >
            <Package class="h-4 w-4 text-[#17493D]" />
            Pickup only
          </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_380px]">
          <Card class="border-[#DCE7E0] bg-white shadow-[0_12px_32px_rgba(23,73,61,0.06)]">
            <CardHeader class="pb-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <CardTitle class="text-lg text-[#173F35]">
                    Favorite Stores
                  </CardTitle>
                  <CardDescription class="text-[#657C74]">
                    Your fastest route to repeat orders.
                  </CardDescription>
                </div>

                <span
                  class="inline-flex items-center rounded-full border border-[#D8E4DD] bg-[#F7FAF8] px-2.5 py-1 text-xs font-semibold text-[#3C6658]"
                >
                  {{ favoriteStores.length }} saved
                </span>
              </div>
            </CardHeader>

            <CardContent class="space-y-3">
              <div
                v-for="store in favoriteStores"
                :key="store.name"
                class="group rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4 transition hover:-translate-y-[1px] hover:border-[#BED0C5] hover:shadow-[0_10px_24px_rgba(23,73,61,0.08)]"
              >
                <div class="flex items-start justify-between gap-4">
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                      <Heart class="h-4 w-4 shrink-0 fill-[#E25563] text-[#E25563]" />
                      <p class="truncate font-semibold text-[#1D463B]">
                        {{ store.name }}
                      </p>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-2">
                      <span class="text-sm text-[#60756D]">
                        {{ store.category }}
                      </span>
                      <span
                        class="inline-flex items-center gap-1 rounded-full border border-[#D7E8DD] bg-white px-2 py-0.5 text-xs font-medium text-[#3C6658]"
                      >
                        <Store class="h-3.5 w-3.5 text-[#5E7A70]" />
                        {{ store.meta }}
                      </span>
                    </div>

                    <p class="mt-2 text-sm text-[#446258]">
                      {{ store.note }}
                    </p>
                  </div>

                  <div class="flex shrink-0 flex-col items-end gap-2">
                    <span
                      class="inline-flex items-center gap-1 rounded-full bg-[#FFF4D7] px-2.5 py-1 text-sm font-semibold text-[#A05C00]"
                    >
                      <Star class="h-3.5 w-3.5 fill-[#D5A43B] text-[#D5A43B]" />
                      {{ store.rating }}
                    </span>

                    <Button
                      variant="ghost"
                      class="h-8 px-2 text-[#17493D] hover:bg-[#EEF6F2] hover:text-[#10362D]"
                    >
                      Open
                      <ChevronRight class="ml-1 h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="overflow-hidden border-0 bg-[#17493D] shadow-[0_18px_40px_rgba(23,73,61,0.18)]">
            <CardHeader class="pb-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <CardTitle class="text-lg text-white">
                    Current Order
                  </CardTitle>
                  <CardDescription class="text-white/70">
                    Your latest pickup at a glance.
                  </CardDescription>
                </div>

                <span
                  :class="[
                    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                    currentOrderBadgeClass,
                  ]"
                >
                  {{ currentOrder.status }}
                </span>
              </div>
            </CardHeader>

            <CardContent class="space-y-4">
              <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <p class="text-xs uppercase tracking-[0.18em] text-white/55">
                      Order {{ currentOrder.id }}
                    </p>
                    <p class="mt-2 text-xl font-semibold leading-tight text-white">
                      {{ currentOrder.store }}
                    </p>
                  </div>

                  <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
                    <Package class="h-5 w-5 text-white" />
                  </div>
                </div>

                <div class="mt-4 space-y-3">
                  <div class="rounded-xl bg-white/5 px-3 py-3">
                    <div class="flex items-start gap-2.5">
                      <Clock3 class="mt-0.5 h-4 w-4 text-white/75" />
                      <div>
                        <p class="text-[11px] uppercase tracking-wide text-white/55">
                          Pickup time
                        </p>
                        <p class="mt-1 text-sm font-medium text-white">
                          {{ currentOrder.pickupTime }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="rounded-xl bg-white/5 px-3 py-3">
                    <div class="flex items-start gap-2.5">
                      <MapPin class="mt-0.5 h-4 w-4 text-white/75" />
                      <div>
                        <p class="text-[11px] uppercase tracking-wide text-white/55">
                          Pickup location
                        </p>
                        <p class="mt-1 text-sm font-medium text-white">
                          {{ currentOrder.pickupLocation }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <div class="flex items-center justify-between gap-3">
                  <p class="text-sm font-medium text-white">
                    Pickup progress
                  </p>
                  <p class="text-xs text-white/65">
                    {{ currentOrder.status }}
                  </p>
                </div>

                <div class="mt-3 h-2 rounded-full bg-white/10">
                  <div
                    class="h-2 rounded-full bg-[#FFD88A] transition-all"
                    :style="{ width: `${pickupProgressWidth}%` }"
                  />
                </div>

                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                  <div
                    v-for="(step, index) in pickupSteps"
                    :key="step.label"
                    class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm"
                    :class="step.current
                      ? 'bg-[#FFF1CB] text-[#8A5200]'
                      : step.done
                        ? 'bg-white text-[#17493D]'
                        : 'border border-white/10 bg-white/5 text-white/70'"
                  >
                    <span
                      class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold"
                      :class="step.current
                        ? 'bg-[#F7D98C] text-[#7A4700]'
                        : step.done
                          ? 'bg-[#EAF3EE] text-[#17493D]'
                          : 'bg-white/10 text-white/70'"
                    >
                      {{ step.done ? '✓' : index + 1 }}
                    </span>
                    <span class="truncate">{{ step.label }}</span>
                  </div>
                </div>
              </div>

              <Button class="h-11 w-full bg-white text-[#17493D] hover:bg-[#F4F0E8]">
                View pickup details
              </Button>
            </CardContent>
          </Card>

          <Card class="border-[#DCE7E0] bg-white shadow-[0_12px_32px_rgba(23,73,61,0.06)] xl:col-span-2">
            <CardHeader class="pb-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <CardTitle class="text-lg text-[#173F35]">
                    {{ bottomSectionTitle }}
                  </CardTitle>
                  <CardDescription class="text-[#657C74]">
                    {{ bottomSectionDescription }}
                  </CardDescription>
                </div>

                <span
                  class="inline-flex items-center rounded-full border border-[#D8E4DD] bg-[#F7FAF8] px-2.5 py-1 text-xs font-semibold text-[#3C6658]"
                >
                  {{ showRecentOrders ? recentOrders.length : recommendedStores.length }} items
                </span>
              </div>
            </CardHeader>

            <CardContent>
              <div
                v-if="showRecentOrders"
                class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"
              >
                <div
                  v-for="order in recentOrders"
                  :key="order.id"
                  class="rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4 transition hover:border-[#BED0C5] hover:shadow-[0_10px_24px_rgba(23,73,61,0.08)]"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <p class="truncate font-semibold text-[#1D463B]">
                        {{ order.store }}
                      </p>
                      <p class="mt-1 text-sm text-[#60756D]">
                        {{ order.id }}
                      </p>
                    </div>

                    <span
                      :class="[
                        'inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                        orderStatusClass(order.status),
                      ]"
                    >
                      {{ order.status }}
                    </span>
                  </div>

                  <div class="mt-4 flex items-start gap-2 text-sm text-[#536B63]">
                    <Clock3 class="mt-0.5 h-4 w-4 text-[#6B7C75]" />
                    <span>{{ order.pickupTime }}</span>
                  </div>

                  <Button
                    variant="ghost"
                    class="mt-3 h-9 px-0 text-[#17493D] hover:bg-transparent hover:text-[#10362D]"
                  >
                    Reorder
                    <ChevronRight class="ml-1 h-4 w-4" />
                  </Button>
                </div>
              </div>

              <div
                v-else
                class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"
              >
                <div
                  v-for="store in recommendedStores"
                  :key="store.name"
                  class="rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4 transition hover:border-[#BED0C5] hover:shadow-[0_10px_24px_rgba(23,73,61,0.08)]"
                >
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <p class="truncate font-semibold text-[#1D463B]">
                        {{ store.name }}
                      </p>
                      <p class="mt-1 text-sm text-[#60756D]">
                        {{ store.category }}
                      </p>
                    </div>

                    <span
                      class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#FFF4D7] px-2.5 py-1 text-sm font-semibold text-[#A05C00]"
                    >
                      <Star class="h-3.5 w-3.5 fill-[#D5A43B] text-[#D5A43B]" />
                      {{ store.rating }}
                    </span>
                  </div>

                  <div class="mt-3 flex items-center gap-2 text-sm text-[#536B63]">
                    <Store class="h-4 w-4 text-[#6B7C75]" />
                    <span>{{ store.meta }}</span>
                  </div>

                  <p class="mt-2 text-sm text-[#446258]">
                    {{ store.note }}
                  </p>

                  <Button
                    variant="ghost"
                    class="mt-3 h-9 px-0 text-[#17493D] hover:bg-transparent hover:text-[#10362D]"
                  >
                    Browse store
                    <ChevronRight class="ml-1 h-4 w-4" />
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </main>
  </div>
</template>