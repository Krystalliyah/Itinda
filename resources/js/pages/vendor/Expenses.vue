<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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

const search = ref('');
const selectedCategory = ref('All');
const selectedStatus = ref('All');

const expenses = ref<ExpenseItem[]>([
    {
        id: 1,
        title: 'Rice and dry goods restock',
        category: 'Inventory',
        amount: 5200,
        date: '2026-03-01',
        method: 'GCash',
        status: 'Paid',
        note: 'Weekly pantry replenishment',
    },
    {
        id: 2,
        title: 'Milk tea cups and lids',
        category: 'Packaging',
        amount: 1850,
        date: '2026-03-03',
        method: 'Cash',
        status: 'Paid',
        note: 'For drink station packaging',
    },
    {
        id: 3,
        title: 'Electricity contribution',
        category: 'Utilities',
        amount: 2400,
        date: '2026-03-05',
        method: 'Bank Transfer',
        status: 'Pending',
        note: 'March shared stall utilities',
    },
    {
        id: 4,
        title: 'Promo posters and menu print',
        category: 'Marketing',
        amount: 1250,
        date: '2026-03-06',
        method: 'Cash',
        status: 'Paid',
        note: 'Counter display refresh',
    },
    {
        id: 5,
        title: 'Condiments and sauces',
        category: 'Inventory',
        amount: 980,
        date: '2026-03-07',
        method: 'GCash',
        status: 'Paid',
        note: 'Restock for fast-moving items',
    },
    {
        id: 6,
        title: 'Freezer maintenance',
        category: 'Maintenance',
        amount: 3200,
        date: '2026-03-09',
        method: 'Bank Transfer',
        status: 'Scheduled',
        note: 'Scheduled technician visit',
    },
    {
        id: 7,
        title: 'Paper bags and stickers',
        category: 'Packaging',
        amount: 1460,
        date: '2026-03-10',
        method: 'GCash',
        status: 'Paid',
        note: 'Branded takeaway supplies',
    },
    {
        id: 8,
        title: 'Staff meal allowance',
        category: 'Operations',
        amount: 1100,
        date: '2026-03-11',
        method: 'Cash',
        status: 'Paid',
        note: 'Weekend extended hours',
    },
]);

const categories = computed(() => [
    'All',
    ...new Set(expenses.value.map((expense) => expense.category)),
]);

const filteredExpenses = computed(() => {
    return expenses.value.filter((expense) => {
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
    expenses.value.reduce((sum, item) => sum + item.amount, 0),
);

const paidExpenses = computed(() =>
    expenses.value
        .filter((item) => item.status === 'Paid')
        .reduce((sum, item) => sum + item.amount, 0),
);

const pendingExpenses = computed(() =>
    expenses.value
        .filter((item) => item.status === 'Pending' || item.status === 'Scheduled')
        .reduce((sum, item) => sum + item.amount, 0),
);

const inventoryShare = computed(() =>
    expenses.value
        .filter((item) => item.category === 'Inventory')
        .reduce((sum, item) => sum + item.amount, 0),
);

const categorySummary = computed(() => {
    const totals = expenses.value.reduce<Record<string, number>>((acc, item) => {
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

const monthlySpend = ref([
    { label: 'Oct', amount: 7800 },
    { label: 'Nov', amount: 9200 },
    { label: 'Dec', amount: 11500 },
    { label: 'Jan', amount: 8900 },
    { label: 'Feb', amount: 10400 },
    { label: 'Mar', amount: 13440 },
]);

const maxMonthlySpend = computed(() =>
    Math.max(...monthlySpend.value.map((item) => item.amount)),
);

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
        return 'bg-[#EAF7F0] text-[#1D6A4F]';
    }

    if (status === 'Pending') {
        return 'bg-[#FFF4E5] text-[#A56A16]';
    }

    return 'bg-[#EEF2FF] text-[#4253B5]';
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
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <section class="overflow-hidden rounded-[30px] border border-[#DCE8E1] bg-white shadow-sm">
    <div class="bg-[linear-gradient(135deg,#1B4A3D_0%,#2C725E_100%)] px-5 py-7 sm:px-7 sm:py-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#F7E8C6]">
                    <Wallet class="h-3.5 w-3.5" />
                    Expense tracking
                </div>

                <h1 class="text-3xl font-semibold tracking-tight !text-white sm:text-4xl">
                    Keep store spending clear and organized
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-7 text-[#F2F7F4] sm:text-base">
                    Monitor operational costs, review category spending, and quickly spot pending payments.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[430px]">
                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">
                        Total expenses
                    </p>
                    <p class="mt-2 text-xl font-semibold !text-white">{{ formatPeso(totalExpenses) }}</p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">
                        Paid
                    </p>
                    <p class="mt-2 text-xl font-semibold !text-white">{{ formatPeso(paidExpenses) }}</p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">
                        Pending
                    </p>
                    <p class="mt-2 text-xl font-semibold !text-white">{{ formatPeso(pendingExpenses) }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F]">This month</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34]">
                                    {{ formatPeso(totalExpenses) }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#EEF6F2] p-3 text-[#245C4A]">
                                <Receipt class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A]">
                            Combined expenses recorded across inventory, utilities, packaging, and operations.
                        </p>
                    </div>

                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F]">Inventory spend</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34]">
                                    {{ formatPeso(inventoryShare) }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#EEF6F2] p-3 text-[#245C4A]">
                                <Package class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A]">
                            Your highest recurring cost category for stock and ingredients.
                        </p>
                    </div>

                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F]">Pending payouts</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34]">
                                    {{ formatPeso(pendingExpenses) }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#FFF5E8] p-3 text-[#A56A16]">
                                <CalendarDays class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A]">
                            Includes both pending and scheduled operational payments.
                        </p>
                    </div>

                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F]">Expense trend</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34]">+12%</p>
                            </div>
                            <div class="rounded-2xl bg-[#EEF2FF] p-3 text-[#4253B5]">
                                <TrendingDown class="h-5 w-5" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-[#6C817A]">
                            Slight increase compared with last month due to restocking and maintenance.
                        </p>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_360px]">
                    <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#EDF6F1] px-3 py-1 text-xs font-semibold text-[#245C4A]">
                                    <BadgeDollarSign class="h-3.5 w-3.5" />
                                    Expense records
                                </div>
                                <h2 class="text-xl font-semibold text-[#183D34]">Recent transactions</h2>
                                <p class="mt-1 text-sm text-[#6E817A]">
                                    Review purchases, utilities, and operations expenses in one place.
                                </p>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <div class="relative min-w-[220px]">
                                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#6C817A]" />
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search expense..."
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] pl-10 pr-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </div>

                                <select
                                    v-model="selectedCategory"
                                    class="h-11 rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                >
                                    <option v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </option>
                                </select>

                                <select
                                    v-model="selectedStatus"
                                    class="h-11 rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                >
                                    <option value="All">All status</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Scheduled">Scheduled</option>
                                </select>
                            </div>
                        </div>

                        <div class="hidden overflow-hidden rounded-2xl border border-[#E4ECE8] lg:block">
                            <div class="grid grid-cols-[1.5fr_1fr_0.9fr_0.9fr_0.8fr] bg-[#F7FAF8] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#789087]">
                                <span>Expense</span>
                                <span>Category</span>
                                <span>Date</span>
                                <span>Amount</span>
                                <span>Status</span>
                            </div>

                            <div v-if="filteredExpenses.length" class="divide-y divide-[#E7EFEB]">
                                <div
                                    v-for="expense in filteredExpenses"
                                    :key="expense.id"
                                    class="grid grid-cols-[1.5fr_1fr_0.9fr_0.9fr_0.8fr] items-center px-5 py-4"
                                >
                                    <div class="pr-4">
                                        <p class="font-semibold text-[#183D34]">{{ expense.title }}</p>
                                        <p class="mt-1 text-sm text-[#70827B]">
                                            {{ expense.method }} • {{ expense.note }}
                                        </p>
                                    </div>

                                    <div>
                                        <span class="inline-flex rounded-full bg-[#F0F5F2] px-3 py-1 text-sm font-medium text-[#355B50]">
                                            {{ expense.category }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-[#5F756D]">{{ formatDate(expense.date) }}</p>

                                    <p class="font-semibold text-[#183D34]">
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

                            <div v-else class="px-5 py-12 text-center text-sm text-[#70827B]">
                                No expenses match your current filters.
                            </div>
                        </div>

                        <div class="grid gap-4 lg:hidden">
                            <div
                                v-for="expense in filteredExpenses"
                                :key="expense.id"
                                class="rounded-2xl border border-[#E4ECE8] bg-[#FBFCFC] p-4"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-[#183D34]">{{ expense.title }}</p>
                                        <p class="mt-1 text-sm text-[#70827B]">{{ expense.note }}</p>
                                    </div>

                                    <span
                                        class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="statusClass(expense.status)"
                                    >
                                        {{ expense.status }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86]">Category</p>
                                        <p class="mt-1 font-medium text-[#183D34]">{{ expense.category }}</p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86]">Amount</p>
                                        <p class="mt-1 font-medium text-[#183D34]">{{ formatPeso(expense.amount) }}</p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86]">Date</p>
                                        <p class="mt-1 font-medium text-[#183D34]">{{ formatDate(expense.date) }}</p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86]">Method</p>
                                        <p class="mt-1 font-medium text-[#183D34]">{{ expense.method }}</p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="!filteredExpenses.length"
                                class="rounded-2xl border border-[#E4ECE8] bg-[#FBFCFC] px-5 py-10 text-center text-sm text-[#70827B]"
                            >
                                No expenses match your current filters.
                            </div>
                        </div>
                    </section>

                    <aside class="grid gap-6">
                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <Filter class="h-5 w-5 text-[#245C4A]" />
                                <h3 class="text-lg font-semibold text-[#183D34]">Category breakdown</h3>
                            </div>

                            <div class="space-y-4">
                                <div v-for="item in categorySummary" :key="item.category">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-[#355B50]">{{ item.category }}</p>
                                        <p class="text-sm font-semibold text-[#183D34]">{{ formatPeso(item.amount) }}</p>
                                    </div>

                                    <div class="h-2 rounded-full bg-[#EBF1EE]">
                                        <div
                                            class="h-2 rounded-full bg-[#245C4A]"
                                            :style="{ width: `${item.width}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <CircleDollarSign class="h-5 w-5 text-[#245C4A]" />
                                <h3 class="text-lg font-semibold text-[#183D34]">Monthly spend</h3>
                            </div>

                            <div class="space-y-4">
                                <div
                                    v-for="month in monthlySpend"
                                    :key="month.label"
                                    class="grid grid-cols-[44px_minmax(0,1fr)_80px] items-center gap-3"
                                >
                                    <span class="text-sm font-medium text-[#5F756D]">{{ month.label }}</span>

                                    <div class="h-3 rounded-full bg-[#ECF2EF]">
                                        <div
                                            class="h-3 rounded-full bg-[#245C4A]"
                                            :style="{ width: `${(month.amount / maxMonthlySpend) * 100}%` }"
                                        ></div>
                                    </div>

                                    <span class="text-right text-sm font-semibold text-[#183D34]">
                                        {{ formatPeso(month.amount) }}
                                    </span>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-[#F7FAF8] p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <CreditCard class="h-5 w-5 text-[#245C4A]" />
                                <h3 class="text-lg font-semibold text-[#183D34]">Expense notes</h3>
                            </div>

                            <div class="space-y-3 text-sm leading-6 text-[#6C8079]">
                                <p>
                                    Inventory and packaging remain your highest-repeat expenses this month.
                                </p>
                                <p>
                                    Consider grouping supplier purchases weekly to reduce small, frequent cash outflows.
                                </p>
                                <p class="rounded-2xl border border-dashed border-[#CBD9D2] bg-white p-4 text-[#5F756D]">
                                    Tip: connect this page later to your real expense records so totals and category charts update automatically.
                                </p>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</template>