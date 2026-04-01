<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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
    Calendar,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';

import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import { useSidebar } from '@/composables/useSidebar';

interface WeeklySale {
    day: string;
    revenue: number;
    orders: number;
}

interface CategoryMixItem {
    label: string;
    value: number;
}

interface PeakWindowItem {
    label: string;
    orders: number;
}

interface TopProductItem {
    name: string;
    orders: number;
    revenue: number;
    growth: string;
}

interface HistoricalTrend {
    period: string;
    revenue: number;
}

interface DailyRevenue {
    date: string;
    order_count: number;
    revenue: number;
    percentage: number;
}

interface MonthlyRevenue {
    month: string;
    full_month: string;
    revenue: number;
}

const props = withDefaults(defineProps<{
    weeklySales?: WeeklySale[];
    categoryMix?: CategoryMixItem[];
    peakWindows?: PeakWindowItem[];
    topProducts?: TopProductItem[];
    recentInsights?: string[];
    growthSignal?: string;
    rangeLabel?: string;
    activePreset?: string;
    activeFrom?: string;
    activeTo?: string;
    totalRevenue?: number;
    totalOrders?: number;
    averageOrderValue?: number;
    historicalTrends?: HistoricalTrend[];
    dailyRevenue?: DailyRevenue[];
    monthlyRevenue?: MonthlyRevenue[];
}>(), {
    weeklySales: () => [],
    categoryMix: () => [],
    peakWindows: () => [],
    topProducts: () => [],
    recentInsights: () => [],
    growthSignal: '0%',
    rangeLabel: 'This week',
    activePreset: 'this_week',
    activeFrom: '',
    activeTo: '',
    totalRevenue: 0,
    totalOrders: 0,
    averageOrderValue: 0,
    historicalTrends: () => [],
    dailyRevenue: () => [],
    monthlyRevenue: () => [],
});

const { isCollapsed } = useSidebar();

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

// Date range filter state
const selectedPreset = ref(props.activePreset);
const customFrom = ref(props.activeFrom);
const customTo = ref(props.activeTo);
const showDatePicker = ref(false);

// Date presets for the filter bar
const datePresets = [
    { key: 'today', label: 'Today' },
    { key: 'yesterday', label: 'Yesterday' },
    { key: 'this_week', label: 'This Week' },
    { key: 'last_week', label: 'Last Week' },
    { key: 'last_7', label: 'Last 7 Days' },
    { key: 'this_month', label: 'This Month' },
    { key: 'last_month', label: 'Last Month' },
    { key: 'last_30', label: 'Last 30 Days' },
    { key: 'this_quarter', label: 'This Quarter' },
    { key: 'last_quarter', label: 'Last Quarter' },
    { key: 'this_year', label: 'This Year' },
    { key: 'last_year', label: 'Last Year' },
    { key: 'custom', label: 'Custom' },
];

// Apply filter function
const applyFilter = () => {
    const params: Record<string, string> = { preset: selectedPreset.value };
    
    if (selectedPreset.value === 'custom') {
        if (customFrom.value) params.from = customFrom.value;
        if (customTo.value) params.to = customTo.value;
    }
    
    router.get('/vendor/analytics', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Apply preset
const applyPreset = (presetKey: string) => {
    selectedPreset.value = presetKey;
    if (presetKey !== 'custom') {
        customFrom.value = '';
        customTo.value = '';
    }
    applyFilter();
};

// Reset to default (this week)
const resetFilter = () => {
    selectedPreset.value = 'this_week';
    customFrom.value = '';
    customTo.value = '';
    applyFilter();
};

// Computed values
const weeklySales = computed(() => props.weeklySales);
const categoryMix = computed(() => props.categoryMix);
const peakWindows = computed(() => props.peakWindows);
const topProducts = computed(() => props.topProducts);
const recentInsights = computed(() => props.recentInsights);
const growthSignal = computed(() => props.growthSignal);
const historicalTrends = computed(() => props.historicalTrends);
const dailyRevenue = computed(() => props.dailyRevenue);
const monthlyRevenue = computed(() => props.monthlyRevenue);

const totalRevenue = computed(() => props.totalRevenue);
const totalOrders = computed(() => props.totalOrders);
const averageOrderValue = computed(() => props.averageOrderValue);

const bestDay = computed(() =>
    weeklySales.value.length
        ? weeklySales.value.reduce((best, current) =>
              current.revenue > best.revenue ? current : best,
          )
        : { day: '—', revenue: 0, orders: 0 },
);

const maxRevenue = computed(() =>
    Math.max(1, ...weeklySales.value.map((item) => item.revenue)),
);

const maxPeakOrders = computed(() =>
    Math.max(1, ...peakWindows.value.map((item) => item.orders)),
);

const maxMonthlyRevenue = computed(() =>
    Math.max(1, ...monthlyRevenue.value.map((item) => item.revenue)),
);

const maxHistoricalRevenue = computed(() =>
    Math.max(1, ...historicalTrends.value.map((item) => item.revenue)),
);

const formatPeso = (value: number) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value);

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
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
                <!-- Header Section -->
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

                                <p class="mt-3 max-w-xl text-sm leading-7 text-white sm:text-base">
                                    Get a clear view of store performance, customer order patterns, and your strongest-selling items.
                                </p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[430px]">
                                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">
                                        Total revenue
                                    </p>
                                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ formatPeso(totalRevenue) }}</p>
                                </div>

                                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">
                                        Total orders
                                    </p>
                                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ totalOrders }}</p>
                                </div>

                                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">
                                        Avg order value
                                    </p>
                                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ formatPeso(averageOrderValue) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Date Range Filter Bar -->
                <section class="bg-white rounded-xl border border-[#DCE8E1] shadow-sm p-4 dark:border-gray-700 dark:bg-slate-800">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="preset in datePresets"
                                :key="preset.key"
                                @click="applyPreset(preset.key)"
                                class="px-3 py-1.5 text-xs rounded-lg transition-colors"
                                :class="selectedPreset === preset.key
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600'"
                            >
                                {{ preset.label }}
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <div v-if="selectedPreset === 'custom'" class="flex items-center gap-2">
                                <input
                                    type="date"
                                    v-model="customFrom"
                                    class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-slate-700 dark:text-slate-100"
                                />
                                <span class="text-xs text-gray-500">to</span>
                                <input
                                    type="date"
                                    v-model="customTo"
                                    class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-slate-700 dark:text-slate-100"
                                />
                            </div>
                            <button
                                @click="applyFilter"
                                class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            >
                                Apply
                            </button>
                            <button
                                @click="resetFilter"
                                class="px-3 py-1.5 text-xs rounded-lg border border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:text-slate-300 dark:hover:bg-slate-700"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                    
                    <!-- Show current range label -->
                    <div class="mt-3 text-xs text-muted-foreground">
                        Showing data for: <span class="font-semibold text-emerald-600">{{ rangeLabel }}</span>
                    </div>
                </section>

                <!-- Stats Cards -->
                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">
                                    Revenue
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
                            Total earnings from completed orders.
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
                            Average amount spent per completed order this period.
                        </p>
                    </div>

                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">Growth signal</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">{{ growthSignal }}</p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#EEF2FF] p-3 text-[#4253B5] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <TrendingUp class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            Period-over-period revenue change based on completed orders.
                        </p>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_360px]">
                    <div class="grid gap-6">
                        <!-- Weekly Revenue Trend -->
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
                                    Daily revenue trend
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

                        <!-- Historical Trends Section -->
                        <section
                            v-if="historicalTrends.length > 0"
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm sm:p-6 dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-5">
                                <div
                                    class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#EDF6F1] px-3 py-1 text-xs font-semibold text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200"
                                >
                                    <TrendingUp class="h-3.5 w-3.5" />
                                    Historical trends
                                </div>
                                <h2 class="text-base sm:text-lg font-semibold text-[#183D34] dark:text-slate-100">
                                    Revenue comparison
                                </h2>
                                <p class="mt-1 text-xs sm:text-sm text-[#6E817A] dark:text-slate-300">
                                    Compare revenue across different time periods.
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div v-for="trend in historicalTrends" :key="trend.period" class="flex items-center justify-between">
                                    <span class="text-xs text-muted-foreground w-32">{{ trend.period }}</span>
                                    <div class="flex-1 mx-4">
                                        <div class="h-2 rounded-full bg-gray-100 dark:bg-slate-700">
                                            <div 
                                                class="h-2 rounded-full bg-emerald-500"
                                                :style="{ width: `${(trend.revenue / maxHistoricalRevenue) * 100}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold">{{ formatPeso(trend.revenue) }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Top Products Section -->
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
                        <!-- Category Mix -->
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

                        <!-- Peak Pick-up Windows -->
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

                        <!-- Monthly Revenue Chart -->
                        <section
                            v-if="monthlyRevenue.length > 0"
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <Calendar class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34] dark:text-slate-100">
                                    Monthly revenue
                                </h3>
                            </div>

                            <div class="space-y-4">
                                <div v-for="month in monthlyRevenue" :key="month.month" class="grid grid-cols-[44px_minmax(0,1fr)_80px] items-center gap-3">
                                    <span class="text-sm font-medium text-[#5F756D] dark:text-slate-300">{{ month.month }}</span>
                                    <div class="h-3 rounded-full bg-[#ECF2EF] dark:bg-slate-700">
                                        <div
                                            class="h-3 rounded-full bg-[#245C4A] dark:bg-amber-300"
                                            :style="{ width: `${(month.revenue / maxMonthlyRevenue) * 100}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-right text-sm font-semibold text-[#183D34] dark:text-slate-100">{{ formatPeso(month.revenue) }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Daily Revenue Breakdown Table -->
                        <section
                            v-if="dailyRevenue.length > 0"
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <ReceiptText class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34] dark:text-slate-100">
                                    Daily breakdown
                                </h3>
                            </div>

                            <div class="space-y-3 max-h-80 overflow-y-auto">
                                <div v-for="day in dailyRevenue" :key="day.date" class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">{{ formatDate(day.date) }}</span>
                                    <div class="flex-1 mx-4">
                                        <div class="h-2 rounded-full bg-gray-100 dark:bg-slate-700">
                                            <div 
                                                class="h-2 rounded-full bg-emerald-500"
                                                :style="{ width: `${day.percentage}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                    <span class="font-semibold">{{ formatPeso(day.revenue) }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Quick Insights -->
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