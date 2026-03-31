<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    BadgeDollarSign,
    CalendarDays,
    CircleDollarSign,
    CreditCard,
    Filter,
    Package,
    Receipt,
    Search,
    TrendingDown,
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

type ExpenseStatus = 'Paid' | 'Pending' | 'Scheduled';

type ExpenseItem = {
    id: number;
    title: string;
    category: string;
    amount: number;
    date: string;
    method: string;
    status: ExpenseStatus;
    note: string;
};

const props = withDefaults(defineProps<{
    expenses?: ExpenseItem[];
    monthlySpend?: { label: string; amount: number }[];
}>(), {
    expenses: () => [],
    monthlySpend: () => [],
});

const search = ref('');
const selectedCategory = ref('All');
const selectedStatus = ref('All');

// --- Date range filter ---
type DatePreset = 'all' | 'this_month' | 'last_30' | 'custom';

const datePreset = ref<DatePreset>('all');
const customFrom = ref('');
const customTo = ref('');

const today = new Date();
const todayStr = today.toISOString().slice(0, 10);

// Derive the active from/to dates based on the selected preset
const activeDateFrom = computed<string | null>(() => {
    if (datePreset.value === 'this_month') {
        return new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0, 10);
    }
    if (datePreset.value === 'last_30') {
        const d = new Date(today);
        d.setDate(d.getDate() - 29);
        return d.toISOString().slice(0, 10);
    }
    if (datePreset.value === 'custom') return customFrom.value || null;
    return null;
});

const activeDateTo = computed<string | null>(() => {
    if (datePreset.value === 'this_month' || datePreset.value === 'last_30') return todayStr;
    if (datePreset.value === 'custom') return customTo.value || null;
    return null;
});

// Reset custom inputs when switching away from custom
watch(datePreset, (val) => {
    if (val !== 'custom') {
        customFrom.value = '';
        customTo.value = '';
    }
});

const datePresetLabel = computed(() => {
    const map: Record<DatePreset, string> = {
        all: 'All time',
        this_month: 'This month',
        last_30: 'Last 30 days',
        custom: 'Custom range',
    };
    return map[datePreset.value];
});

const expenses = computed(() => props.expenses);

const categories = computed(() => [
    'All',
    ...new Set(expenses.value.map((expense) => expense.category)),
]);

// Base set filtered only by date — used for all summary stats and charts
const dateFilteredExpenses = computed(() => {
    return expenses.value.filter((expense) => {
        if (activeDateFrom.value && expense.date < activeDateFrom.value) return false;
        if (activeDateTo.value && expense.date > activeDateTo.value) return false;
        return true;
    });
});

const filteredExpenses = computed(() => {
    return dateFilteredExpenses.value.filter((expense) => {
        const matchesSearch =
            expense.title.toLowerCase().includes(search.value.toLowerCase()) ||
            expense.category.toLowerCase().includes(search.value.toLowerCase()) ||
            expense.method.toLowerCase().includes(search.value.toLowerCase());

        const matchesCategory =
            selectedCategory.value === 'All' || expense.category === selectedCategory.value;

        const matchesStatus =
            selectedStatus.value === 'All' || expense.status === selectedStatus.value;

        return matchesSearch && matchesCategory && matchesStatus;
    });
});

const totalExpenses = computed(() =>
    dateFilteredExpenses.value.reduce((sum, item) => sum + item.amount, 0),
);

const paidExpenses = computed(() =>
    dateFilteredExpenses.value
        .filter((item) => item.status === 'Paid')
        .reduce((sum, item) => sum + item.amount, 0),
);

const pendingExpenses = computed(() =>
    dateFilteredExpenses.value
        .filter((item) => item.status === 'Pending' || item.status === 'Scheduled')
        .reduce((sum, item) => sum + item.amount, 0),
);

const inventoryShare = computed(() =>
    dateFilteredExpenses.value
        .filter((item) => item.category === 'Inventory')
        .reduce((sum, item) => sum + item.amount, 0),
);

const categorySummary = computed(() => {
    const totals = dateFilteredExpenses.value.reduce<Record<string, number>>((acc, item) => {
        acc[item.category] = (acc[item.category] ?? 0) + item.amount;
        return acc;
    }, {});

    const highest = Math.max(...Object.values(totals));

    return Object.entries(totals)
        .map(([category, amount]) => ({
            category,
            amount,
            width: highest ? (amount / highest) * 100 : 0,
        }))
        .sort((a, b) => b.amount - a.amount);
});

const monthlySpend = computed(() => props.monthlySpend);

const maxMonthlySpend = computed(() =>
    Math.max(...monthlySpend.value.map((item) => item.amount)),
);

const expenseTrend = computed(() => {
    const data = monthlySpend.value;
    if (data.length < 2) return null;
    const prev = data[data.length - 2].amount;
    const curr = data[data.length - 1].amount;
    if (prev === 0) return null;
    const pct = Math.round(((curr - prev) / prev) * 100);
    if (pct === 0) return '0%';
    return (pct > 0 ? '+' : '') + pct + '%';
});

const formatPeso = (value: number) =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value);

const formatDate = (value: string) =>
    new Date(value).toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });

const statusClass = (status: ExpenseStatus) => {
    if (status === 'Paid') {
        return 'bg-[#EAF7F0] text-[#1D6A4F] dark:bg-emerald-500/15 dark:text-emerald-300';
    }

    if (status === 'Pending') {
        return 'bg-[#FFF4E5] text-[#A56A16] dark:bg-amber-500/15 dark:text-amber-200';
    }

    return 'bg-[#EEF2FF] text-[#4253B5] dark:bg-indigo-500/15 dark:text-indigo-200';
};
</script>

<template>
    <Head title="Expense Tracking" />

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
                    <Wallet class="h-3.5 w-3.5" />
                    Expense tracking
                </div>

                <h1 class="text-xl sm:text-2xl lg:text-3xl font-semibold tracking-tight !text-white">
                    Keep store spending clear and organized
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-7 text-white sm:text-base">
                    Monitor operational costs, review category spending, and quickly spot pending payments.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[430px]">
                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">
                        Total expenses
                    </p>
                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ formatPeso(totalExpenses) }}</p>
                </div>

                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">
                        Paid
                    </p>
                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ formatPeso(paidExpenses) }}</p>
                </div>

                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">
                        Pending
                    </p>
                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ formatPeso(pendingExpenses) }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

                <!-- Date range filter bar -->
                <section
                    class="rounded-[26px] border border-[#DCE8E1] bg-white px-5 py-4 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                >
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2 text-sm font-medium text-[#355B50] dark:text-slate-300">
                            <CalendarDays class="h-4 w-4 shrink-0" />
                            <span>Date range:</span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="preset in ([
                                    { value: 'all', label: 'All time' },
                                    { value: 'this_month', label: 'This month' },
                                    { value: 'last_30', label: 'Last 30 days' },
                                    { value: 'custom', label: 'Custom' },
                                ] as const)"
                                :key="preset.value"
                                type="button"
                                @click="datePreset = preset.value"
                                :class="[
                                    'rounded-full px-4 py-1.5 text-xs font-semibold transition',
                                    datePreset === preset.value
                                        ? 'bg-[#245C4A] text-white dark:bg-amber-400 dark:text-slate-900'
                                        : 'bg-[#EDF6F1] text-[#355B50] hover:bg-[#D8EDE5] dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600',
                                ]"
                            >
                                {{ preset.label }}
                            </button>
                        </div>

                        <template v-if="datePreset === 'custom'">
                            <div class="flex flex-wrap items-center gap-2 ml-auto">
                                <input
                                    v-model="customFrom"
                                    type="date"
                                    :max="customTo || todayStr"
                                    class="h-9 rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-3 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10 dark:border-gray-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                                <span class="text-xs text-[#6C817A] dark:text-slate-400">to</span>
                                <input
                                    v-model="customTo"
                                    type="date"
                                    :min="customFrom || undefined"
                                    :max="todayStr"
                                    class="h-9 rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-3 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10 dark:border-gray-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>
                        </template>

                        <span
                            v-if="datePreset !== 'all'"
                            class="ml-auto text-xs text-[#6C817A] dark:text-slate-400"
                        >
                            Showing {{ dateFilteredExpenses.length }} expense{{ dateFilteredExpenses.length !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">{{ datePresetLabel }}</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ formatPeso(totalExpenses) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#EEF6F2] p-3 text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <Receipt class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            Combined expenses recorded across inventory, utilities, packaging, and operations.
                        </p>
                    </div>

                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">Inventory spend</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ formatPeso(inventoryShare) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#EEF6F2] p-3 text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <Package class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            Your highest recurring cost category for stock and ingredients.
                        </p>
                    </div>

                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">Pending payouts</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ formatPeso(pendingExpenses) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#FFF5E8] p-3 text-[#A56A16] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <CalendarDays class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            Includes both pending and scheduled operational payments.
                        </p>
                    </div>

                    <div
                        class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">Expense trend</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ expenseTrend ?? '—' }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl bg-[#EEF2FF] p-3 text-[#4253B5] dark:bg-amber-100/15 dark:text-amber-200"
                            >
                                <TrendingDown class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A] dark:text-slate-300">
                            <template v-if="expenseTrend === null">No trend data available yet.</template>
                            <template v-else-if="expenseTrend.startsWith('+')">Expenses increased compared to last month.</template>
                            <template v-else-if="expenseTrend.startsWith('-')">Expenses decreased compared to last month.</template>
                            <template v-else>Expenses are unchanged from last month.</template>
                        </p>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_360px]">
                    <section
                        class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm sm:p-6 dark:border-gray-700 dark:bg-slate-800"
                    >
                        <div class="mb-5 flex flex-col gap-3 sm:gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div
                                    class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#EDF6F1] px-3 py-1 text-xs font-semibold text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200"
                                >
                                    <BadgeDollarSign class="h-3.5 w-3.5" />
                                    Expense records
                                </div>
                                <h2 class="text-base sm:text-lg font-semibold text-[#183D34] dark:text-slate-100">
                                    Recent transactions
                                </h2>
                                <p class="mt-1 text-xs sm:text-sm text-[#6E817A] dark:text-slate-300">
                                    Review purchases, utilities, and operations expenses in one place.
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 sm:gap-3 sm:flex-row">
                                <div class="relative w-full sm:min-w-[220px]">
                                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6C817A]" />
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search expense..."
                                        class="h-10 sm:h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] pl-10 pr-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10 dark:border-gray-700 dark:bg-slate-900 dark:text-slate-100"
                                    />
                                </div>

                                <select
                                    v-model="selectedCategory"
                                    class="h-10 sm:h-11 w-full sm:w-auto rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10 dark:border-gray-700 dark:bg-slate-900 dark:text-slate-100"
                                >
                                    <option v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </option>
                                </select>

                                <select
                                    v-model="selectedStatus"
                                    class="h-10 sm:h-11 w-full sm:w-auto rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10 dark:border-gray-700 dark:bg-slate-900 dark:text-slate-100"
                                >
                                    <option value="All">All status</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Scheduled">Scheduled</option>
                                </select>
                            </div>
                        </div>

                        <div class="hidden overflow-hidden rounded-2xl border border-[#E4ECE8] bg-white lg:block dark:border-gray-700 dark:bg-slate-900">
                            <div class="grid grid-cols-[1.5fr_1fr_0.9fr_0.9fr_0.8fr] bg-[#F7FAF8] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#789087] dark:bg-slate-800 dark:text-slate-300">
                                <span>Expense</span>
                                <span>Category</span>
                                <span>Date</span>
                                <span>Amount</span>
                                <span>Status</span>
                            </div>

                            <div v-if="filteredExpenses.length" class="divide-y divide-[#E7EFEB] dark:divide-slate-700">
                                <div
                                    v-for="expense in filteredExpenses"
                                    :key="expense.id"
                                    class="grid grid-cols-[1.5fr_1fr_0.9fr_0.9fr_0.8fr] items-center px-5 py-4"
                                >
                                    <div class="pr-4">
                                        <p class="font-semibold text-[#183D34] dark:text-slate-100">
                                            {{ expense.title }}
                                        </p>
                                        <p class="mt-1 text-sm text-[#70827B] dark:text-slate-300">
                                            {{ expense.method }} • {{ expense.note }}
                                        </p>
                                    </div>

                                    <div>
                                        <span
                                            class="inline-flex rounded-full bg-[#F0F5F2] px-3 py-1 text-sm font-medium text-[#355B50] dark:bg-slate-800 dark:text-slate-100"
                                        >
                                            {{ expense.category }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-[#5F756D] dark:text-slate-300">
                                        {{ formatDate(expense.date) }}
                                    </p>

                                    <p class="font-semibold text-[#183D34] dark:text-slate-100">
                                        {{ formatPeso(expense.amount) }}
                                    </p>

                                    <div>
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                            :class="statusClass(expense.status)"
                                        >
                                            {{ expense.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="px-5 py-12 text-center text-sm text-[#70827B] dark:text-slate-400" v-else>
                                No expenses match your current filters.
                            </div>
                        </div>

                        <div class="grid gap-4 lg:hidden">
                            <div
                                v-for="expense in filteredExpenses"
                                :key="expense.id"
                                class="rounded-2xl border border-[#E4ECE8] bg-[#FBFCFC] p-4 dark:border-gray-700 dark:bg-slate-900"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-[#183D34] dark:text-slate-100">
                                            {{ expense.title }}
                                        </p>
                                        <p class="mt-1 text-sm text-[#70827B] dark:text-slate-300">
                                            {{ expense.note }}
                                        </p>
                                    </div>

                                    <span
                                        class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="statusClass(expense.status)"
                                    >
                                        {{ expense.status }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">
                                            Category
                                        </p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">
                                            {{ expense.category }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">
                                            Amount
                                        </p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">
                                            {{ formatPeso(expense.amount) }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">
                                            Date
                                        </p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">
                                            {{ formatDate(expense.date) }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">
                                            Method
                                        </p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">
                                            {{ expense.method }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="!filteredExpenses.length"
                                class="rounded-2xl border border-[#E4ECE8] bg-[#FBFCFC] px-5 py-10 text-center text-sm text-[#70827B] dark:border-gray-700 dark:bg-slate-900 dark:text-slate-400"
                            >
                                No expenses match your current filters.
                            </div>
                        </div>
                    </section>

                    <aside class="grid gap-6">
                        <section
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <Filter class="h-5 w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-lg font-semibold text-[#183D34] dark:text-slate-100">
                                    Category breakdown
                                </h3>
                            </div>

                            <div class="space-y-4">
                                <div v-for="item in categorySummary" :key="item.category">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-[#355B50] dark:text-slate-200">
                                            {{ item.category }}
                                        </p>
                                        <p class="text-sm font-semibold text-[#183D34] dark:text-slate-100">
                                            {{ formatPeso(item.amount) }}
                                        </p>
                                    </div>

                                    <div class="h-2 rounded-full bg-[#EBF1EE] dark:bg-slate-700">
                                        <div
                                            class="h-2 rounded-full bg-[#245C4A] dark:bg-amber-300"
                                            :style="{ width: `${item.width}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section
                            class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <CircleDollarSign class="h-5 w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-lg font-semibold text-[#183D34] dark:text-slate-100">
                                    Monthly spend
                                </h3>
                            </div>

                            <div class="space-y-4">
                                <div
                                    v-for="month in monthlySpend"
                                    :key="month.label"
                                    class="grid grid-cols-[44px_minmax(0,1fr)_80px] items-center gap-3"
                                >
                                    <span class="text-sm font-medium text-[#5F756D] dark:text-slate-300">
                                        {{ month.label }}
                                    </span>

                                    <div class="h-3 rounded-full bg-[#ECF2EF] dark:bg-slate-700">
                                        <div
                                            class="h-3 rounded-full bg-[#245C4A] dark:bg-amber-300"
                                            :style="{ width: `${(month.amount / maxMonthlySpend) * 100}%` }"
                                        ></div>
                                    </div>

                                    <span class="text-right text-sm font-semibold text-[#183D34] dark:text-slate-100">
                                        {{ formatPeso(month.amount) }}
                                    </span>
                                </div>
                            </div>
                        </section>

                        <section
                            class="rounded-[28px] border border-[#DCE8E1] bg-[#F7FAF8] p-5 shadow-sm dark:border-gray-700 dark:bg-slate-900"
                        >
                            <div class="mb-4 flex items-center gap-2">
                                <CreditCard class="h-5 w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-lg font-semibold text-[#183D34] dark:text-slate-100">
                                    Expense notes
                                </h3>
                            </div>

                            <div v-if="filteredExpenses.filter(e => e.note).length" class="space-y-2">
                                <div
                                    v-for="expense in filteredExpenses.filter(e => e.note)"
                                    :key="expense.id"
                                    class="rounded-2xl border border-[#E4ECE8] bg-white p-3 dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <p class="text-xs font-semibold text-[#245C4A] dark:text-amber-300 truncate">
                                        {{ expense.title }}
                                    </p>
                                    <p class="mt-1 text-sm leading-5 text-[#5F756D] dark:text-slate-300">
                                        {{ expense.note }}
                                    </p>
                                </div>
                            </div>

                            <p v-else class="text-sm text-[#7A8E86] dark:text-slate-400">
                                No notes for the current filters.
                            </p>
                        </section>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</template>

