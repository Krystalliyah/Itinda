<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    BarChart3,
    Clock3,
    Package,
    ReceiptText,
    ShoppingBag,
    Sparkles,
    TrendingUp,
    Wallet,
} from 'lucide-vue-next';

import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

const weeklySales = ref([
    { day: 'Mon', revenue: 4200, orders: 32 },
    { day: 'Tue', revenue: 5100, orders: 38 },
    { day: 'Wed', revenue: 4800, orders: 35 },
    { day: 'Thu', revenue: 6200, orders: 44 },
    { day: 'Fri', revenue: 7600, orders: 56 },
    { day: 'Sat', revenue: 6900, orders: 49 },
    { day: 'Sun', revenue: 3100, orders: 22 },
]);

const categoryMix = ref([
    { label: 'Meals', value: 46 },
    { label: 'Drinks', value: 31 },
    { label: 'Snacks', value: 15 },
    { label: 'Essentials', value: 8 },
]);

const peakWindows = ref([
    { label: '11:00 AM - 1:00 PM', orders: 48 },
    { label: '2:00 PM - 4:00 PM', orders: 31 },
    { label: '5:00 PM - 7:00 PM', orders: 24 },
    { label: '8:00 AM - 10:00 AM', orders: 18 },
]);

const topProducts = ref([
    { name: 'Chicken Rice Bowl', orders: 84, revenue: 7560, growth: '+12%' },
    { name: 'Iced Matcha Latte', orders: 72, revenue: 5760, growth: '+9%' },
    { name: 'Spicy Tuna Sandwich', orders: 58, revenue: 5220, growth: '+6%' },
    { name: 'Chocolate Milk Tea', orders: 55, revenue: 4675, growth: '+11%' },
    { name: 'Bottled Water Pack', orders: 39, revenue: 1950, growth: '+3%' },
]);

const recentInsights = ref([
    'Friday remains your strongest sales day, driven by lunch and afternoon pick-up orders.',
    'Meals continue to lead revenue, while drinks maintain strong repeat purchase volume.',
    'Midday pick-up windows show the highest traffic, so prep capacity should stay focused before noon.',
]);

const totalRevenue = computed(() =>
    weeklySales.value.reduce((sum, item) => sum + item.revenue, 0),
);

const totalOrders = computed(() =>
    weeklySales.value.reduce((sum, item) => sum + item.orders, 0),
);

const averageOrderValue = computed(() =>
    totalOrders.value ? totalRevenue.value / totalOrders.value : 0,
);

const bestDay = computed(() =>
    weeklySales.value.reduce((best, current) =>
        current.revenue > best.revenue ? current : best,
    ),
);

const maxRevenue = computed(() =>
    Math.max(...weeklySales.value.map((item) => item.revenue)),
);

const maxPeakOrders = computed(() =>
    Math.max(...peakWindows.value.map((item) => item.orders)),
);

const formatPeso = (value: number) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value);
</script>

<template>
    <Head title="Sales Analytics" />

    <div class="dashboard-wrapper">
        <Header />

        <Sidebar role="vendor">
            <VendorNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 sm:gap-6 px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
                <section
                    class="overflow-hidden rounded-[30px] border border-[#DCE8E1] bg-white shadow-sm dark:border-gray-700 dark:bg-slate-800"
                >
                    <div class="bg-[linear-gradient(135deg,#1B4A3D_0%,#2C725E_100%)] px-5 py-7 sm:px-7 sm:py-8">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                            <div class="max-w-2xl">
                                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#F7E8C6]">
                                    <Sparkles class="h-3.5 w-3.5" />
                                    Sales analytics
                                </div>

                                <h1 class="text-2xl font-semibold tracking-tight !text-white sm:text-3xl lg:text-4xl">
                                    Track revenue, orders, and top-performing products
                                </h1>

                                <p class="mt-3 max-w-xl text-sm leading-7 text-[#F2F7F4] sm:text-base">
                                    Get a clear view of store performance, customer order patterns, and your strongest-selling items.
                                </p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[430px]">
                                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">
                                        Weekly revenue
                                    </p>
                                    <p class="mt-2 text-xl font-semibold !text-white">{{ formatPeso(totalRevenue) }}</p>
                                </div>

                                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">
                                        Total orders
                                    </p>
                                    <p class="mt-2 text-xl font-semibold !text-white">{{ totalOrders }}</p>
                                </div>

                                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">
                                        Best day
                                    </p>
                                    <p class="mt-2 text-xl font-semibold !text-white">{{ bestDay.day }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">
                                    Revenue this week
                                </p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ formatPeso(totalRevenue) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#EEF6F2] p-3 text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <Wallet class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            Total earnings from completed pick-up orders across the current week.
                        </p>
                    </div>

                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">
                                    Orders completed
                                </p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ totalOrders }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#EEF6F2] p-3 text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <ShoppingBag class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            Total fulfilled customer orders tracked during the same period.
                        </p>
                    </div>

                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">
                                    Average order value
                                </p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ formatPeso(averageOrderValue) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#FFF5E8] p-3 text-[#A56A16] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <ReceiptText class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            Average amount spent per completed order this week.
                        </p>
                    </div>

                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">Growth signal</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">+14%</p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#EEF2FF] p-3 text-[#4253B5] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <TrendingUp class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            Estimated week-over-week improvement based on current order flow.
                        </p>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_360px]">
                    <div class="grid gap-6">
                        <section
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm sm:p-6 dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-5">
                                <div
                                    class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#EDF6F1] px-3 py-1 text-xs font-semibold text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200"
                                >
                                    <BarChart3 class="h-3.5 w-3.5" />
                                    Revenue overview
                                </div>
                                <h2 class="text-base sm:text-lg font-semibold text-[#183D34] dark:text-slate-100">
                                    Weekly revenue trend
                                </h2>
                                <p class="mt-1 text-xs sm:text-sm text-[#6E817A] dark:text-slate-300">
                                    Compare daily sales and quickly spot your strongest business days.
                                </p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-7 sm:items-end">
                                <div
                                    v-for="item in weeklySales"
                                    :key="item.day"
                                    class="rounded-2xl border border-[#E5EEEA] bg-[#FBFCFC] p-4 sm:flex sm:h-[260px] sm:flex-col sm:justify-end dark:border-gray-700 dark:bg-slate-900"
                                >
                                    <div class="flex items-end gap-3 sm:block">
                                        <div class="sm:flex sm:h-[150px] sm:items-end">
                                            <div
                                                class="w-14 rounded-2xl bg-[#245C4A] sm:w-full dark:bg-amber-300"
                                                :style="{
                                                    height: `${(item.revenue / maxRevenue) * 100}%`,
                                                    minHeight: '42px',
                                                }"
                                            ></div>
                                        </div>

                                        <div class="mt-0 flex-1 sm:mt-4">
                                            <p class="text-sm font-semibold text-[#183D34] dark:text-slate-100">
                                                {{ item.day }}
                                            </p>
                                            <p class="mt-1 text-sm text-[#5F756D] dark:text-slate-300">
                                                {{ formatPeso(item.revenue) }}
                                            </p>
                                            <p class="mt-1 text-xs text-[#80928B] dark:text-slate-400">
                                                {{ item.orders }} orders
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm sm:p-6 dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-5">
                                <div
                                    class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#EDF6F1] px-3 py-1 text-xs font-semibold text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200"
                                >
                                    <Package class="h-3.5 w-3.5" />
                                    Product performance
                                </div>
                                <h2 class="text-base sm:text-lg font-semibold text-[#183D34] dark:text-slate-100">
                                    Top-selling products
                                </h2>
                                <p class="mt-1 text-xs sm:text-sm text-[#6E817A] dark:text-slate-300">
                                    Review which items are driving the most orders and revenue.
                                </p>
                            </div>

                            <div class="hidden overflow-hidden rounded-2xl border border-[#E4ECE8] bg-white lg:block dark:border-gray-700 dark:bg-slate-900">
                                <div class="grid grid-cols-[1.6fr_0.8fr_1fr_0.7fr] bg-[#F7FAF8] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#789087] dark:bg-slate-800 dark:text-slate-300">
                                    <span>Product</span>
                                    <span>Orders</span>
                                    <span>Revenue</span>
                                    <span>Growth</span>
                                </div>

                                <div class="divide-y divide-[#E7EFEB] dark:divide-slate-700">
                                    <div
                                        v-for="product in topProducts"
                                        :key="product.name"
                                        class="grid grid-cols-[1.6fr_0.8fr_1fr_0.7fr] items-center px-5 py-4"
                                    >
                                        <p class="font-semibold text-[#183D34] dark:text-slate-100">
                                            {{ product.name }}
                                        </p>
                                        <p class="text-sm text-[#5F756D] dark:text-slate-300">
                                            {{ product.orders }}
                                        </p>
                                        <p class="font-semibold text-[#183D34] dark:text-slate-100">
                                            {{ formatPeso(product.revenue) }}
                                        </p>
                                        <span
                                            class="inline-flex w-fit rounded-full bg-[#EAF7F0] px-3 py-1 text-sm font-semibold text-[#1D6A4F] dark:bg-emerald-500/10 dark:text-emerald-300"
                                        >
                                            {{ product.growth }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 lg:hidden">
                                <div
                                    v-for="product in topProducts"
                                    :key="product.name"
                                    class="rounded-2xl border border-[#E4ECE8] bg-[#FBFCFC] p-4 dark:border-gray-700 dark:bg-slate-900"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-[#183D34] dark:text-slate-100">
                                                {{ product.name }}
                                            </p>
                                            <p class="mt-1 text-sm text-[#70827B] dark:text-slate-300">
                                                {{ product.orders }} orders
                                            </p>
                                        </div>

                                        <span
                                            class="inline-flex rounded-full bg-[#EAF7F0] px-3 py-1 text-xs font-semibold text-[#1D6A4F] dark:bg-emerald-500/10 dark:text-emerald-300"
                                        >
                                            {{ product.growth }}
                                        </span>
                                    </div>

                                    <div class="mt-4 rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">
                                            Revenue
                                        </p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">
                                            {{ formatPeso(product.revenue) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="grid gap-6">
                        <section
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <ShoppingBag class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34] dark:text-slate-100">
                                    Category mix
                                </h3>
                            </div>

                            <div class="space-y-4">
                                <div v-for="item in categoryMix" :key="item.label">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-[#355B50] dark:text-slate-200">
                                            {{ item.label }}
                                        </p>
                                        <p class="text-sm font-semibold text-[#183D34] dark:text-slate-100">
                                            {{ item.value }}%
                                        </p>
                                    </div>

                                    <div class="h-2 rounded-full bg-[#EBF1EE] dark:bg-slate-700">
                                        <div
                                            class="h-2 rounded-full bg-[#245C4A] dark:bg-amber-300"
                                            :style="{ width: `${item.value}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <Clock3 class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34] dark:text-slate-100">
                                    Peak pick-up windows
                                </h3>
                            </div>

                            <div class="space-y-4">
                                <div
                                    v-for="slot in peakWindows"
                                    :key="slot.label"
                                    class="grid grid-cols-[minmax(0,1fr)_56px] items-center gap-3"
                                >
                                    <div>
                                        <p class="text-sm font-medium text-[#355B50] dark:text-slate-200">
                                            {{ slot.label }}
                                        </p>
                                        <div class="mt-2 h-2 rounded-full bg-[#ECF2EF] dark:bg-slate-700">
                                            <div
                                                class="h-2 rounded-full bg-[#245C4A] dark:bg-amber-300"
                                                :style="{ width: `${(slot.orders / maxPeakOrders) * 100}%` }"
                                            ></div>
                                        </div>
                                    </div>

                                    <p class="text-right text-sm font-semibold text-[#183D34] dark:text-slate-100">
                                        {{ slot.orders }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section
                            class="rounded-[28px] border border-[#DCE8E1] bg-[#F7FAF8] p-5 shadow-sm dark:border-gray-700 dark:bg-slate-900"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <TrendingUp class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34] dark:text-slate-100">
                                    Quick insights
                                </h3>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="insight in recentInsights"
                                    :key="insight"
                                    class="rounded-2xl border border-dashed border-[#CBD9D2] bg-white p-4 text-sm leading-6 text-[#5F756D] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    {{ insight }}
                                </div>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</template>