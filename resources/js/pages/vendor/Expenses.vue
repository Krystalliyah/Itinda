<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
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
    Plus,
    Edit,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import Header from '@/components/Header.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import Sidebar from '@/components/Sidebar.vue';
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
    supplier_id?: number | null;
    reference_number?: string | null;
};

type Supplier = {
    id: number;
    name: string;
};

type InventoryStats = {
    total_spend: number;
    total_transactions: number;
    average_per_transaction: number;
    largest_purchase: number;
};

type SupplierExpense = {
    supplier_name: string;
    total: number;
    count: number;
};

type DailyBreakdown = {
    date: string;
    total: number;
    count: number;
};

type MonthlySpend = {
    label: string;
    amount: number;
};

const props = withDefaults(defineProps<{
    expenses?: ExpenseItem[];
    monthlySpend?: MonthlySpend[];
    inventoryStats?: InventoryStats;
    expensesBySupplier?: Record<string, SupplierExpense>;
    dailyBreakdown?: DailyBreakdown[];
    suppliers?: Supplier[];
    filters?: {
        start_date: string;
        end_date: string;
        category_filter: string;
        date_preset?: string;
    };
}>(), {
    expenses: () => [],
    monthlySpend: () => [],
    inventoryStats: () => ({
        total_spend: 0,
        total_transactions: 0,
        average_per_transaction: 0,
        largest_purchase: 0,
    }),
    expensesBySupplier: () => ({}),
    dailyBreakdown: () => [],
    suppliers: () => [],
    filters: () => ({
        start_date: '',
        end_date: '',
        category_filter: 'all',
        date_preset: 'this_month',
    }),
});

// Modal states
const showAddModal = ref(false);
const showEditModal = ref(false);
const editingExpense = ref<ExpenseItem | null>(null);

// Search and filter states
const search = ref('');
const selectedCategory = ref('All');
const selectedStatus = ref('All');

// Date range filter
type DatePreset = 'all' | 'this_month' | 'last_30' | 'custom';
const datePreset = ref<DatePreset>((props.filters.date_preset as DatePreset) || 'this_month');
const customFrom = ref('');
const customTo = ref('');
const categoryFilter = ref(props.filters.category_filter);

const today = new Date();
const todayStr = [
    today.getFullYear(),
    String(today.getMonth() + 1).padStart(2, '0'),
    String(today.getDate()).padStart(2, '0'),
].join('-');

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

// Watch for changes to expenses prop
watch(() => props.expenses, () => {
    // Force recalculation of computed properties
}, { deep: true });

// Apply filter to server
const applyFilter = () => {
    router.get('/vendor/expenses', {
        start_date: activeDateFrom.value || '',
        end_date: activeDateTo.value || '',
        category_filter: categoryFilter.value,
        date_preset: datePreset.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Reset filter
const resetFilter = () => {
    datePreset.value = 'this_month';
    categoryFilter.value = 'all';
    applyFilter();
};

// Apply preset
const applyPreset = (preset: DatePreset) => {
    datePreset.value = preset;
    applyFilter();
};

const datePresetLabel = computed(() => {
    const map: Record<DatePreset, string> = {
        all: 'All time',
        this_month: 'This month',
        last_30: 'Last 30 days',
        custom: 'Custom range',
    };
    return map[datePreset.value];
});

// Form for adding/editing expenses
const form = useForm({
    title: '',
    category: '',
    amount: '',
    date: todayStr,
    method: 'cash',
    status: 'Paid' as ExpenseStatus,
    note: '',
    supplier_id: null as number | null,
    reference_number: '',
});

// Get unique categories from expenses
const categories = computed(() => [
    'All',
    ...new Set(props.expenses.map((expense) => expense.category)),
]);

// Filter expenses by date range (client-side)
const dateFilteredExpenses = computed(() => {
    return props.expenses.filter((expense) => {
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

const totalExpenses = computed(() => {
    const total = dateFilteredExpenses.value.reduce((sum, item) => sum + (item.amount || 0), 0);
    return total;
});

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
        .filter((item) => item.category.toLowerCase() === 'inventory')
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
    Math.max(...monthlySpend.value.map((item) => item.amount), 1),
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

// Modal functions
const openAddModal = () => {
    editingExpense.value = null;
    form.reset();
    form.date = todayStr;
    form.method = 'cash';
    form.status = 'Paid';
    showAddModal.value = true;
};

const openEditModal = (expense: ExpenseItem) => {
    editingExpense.value = expense;
    form.title = expense.title;
    form.category = expense.category;
    form.amount = expense.amount.toString();
    form.date = expense.date;
    form.method = expense.method;
    form.status = expense.status;
    form.note = expense.note || '';
    form.supplier_id = expense.supplier_id || null;
    form.reference_number = expense.reference_number || '';
    showEditModal.value = true;
};

const submitForm = () => {
    if (editingExpense.value) {
        form.put(`/vendor/expenses/${editingExpense.value.id}`, {
            onSuccess: () => {
                showEditModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/vendor/expenses', {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteExpense = (id: number) => {
    if (confirm('Are you sure you want to delete this expense?')) {
        router.delete(`/vendor/expenses/${id}`);
    }
};

// Helper functions
const formatPeso = (value: number | undefined | null) => {
    if (value === undefined || value === null || isNaN(value)) {
        return '₱0';
    }
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0,
    }).format(value);
};

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

const getMethodBadge = (method: string) => {
    const badges: Record<string, string> = {
        cash: 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300',
        bank_transfer: 'bg-purple-100 text-purple-800 dark:bg-purple-500/15 dark:text-purple-300',
        credit_card: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300',
        check: 'bg-gray-100 text-gray-800 dark:bg-gray-500/15 dark:text-gray-300',
    };
    return badges[method] || 'bg-gray-100 text-gray-800 dark:bg-gray-500/15 dark:text-gray-300';
};

const getMethodName = (method: string) => {
    const names: Record<string, string> = {
        cash: 'Cash',
        bank_transfer: 'Bank Transfer',
        credit_card: 'Credit Card',
        check: 'Check',
    };
    return names[method] || method;
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
                <!-- Header Section -->
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

                                <p class="mt-3 max-w-xl text-sm leading-7 !text-emerald-50 sm:text-base">
                                    Monitor operational costs, review category spending, and quickly spot pending payments.
                                </p>
                            </div>

                            <button
                                @click="openAddModal"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-[#245C4A] transition-all hover:bg-white/90"
                            >
                                <Plus class="h-4 w-4" />
                                Record Expense
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Date Range Filter Bar -->
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
                                @click="applyPreset(preset.value)"
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
                                <button
                                    @click="applyFilter"
                                    class="rounded-xl bg-[#245C4A] px-4 py-1.5 text-xs font-semibold text-white hover:bg-[#1d4a3a]"
                                >
                                    Apply
                                </button>
                            </div>
                        </template>

                        <div class="ml-auto flex items-center gap-2">
                            <select v-model="categoryFilter" @change="applyFilter" class="h-9 rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-3 text-sm text-[#1E4138] dark:border-gray-700 dark:bg-slate-900 dark:text-slate-100">
                                <option value="all">All Categories</option>
                                <option value="inventory">Inventory Only</option>
                            </select>
                            <button
                                @click="resetFilter"
                                class="rounded-xl border border-[#D7E3DC] px-4 py-1.5 text-xs font-semibold text-[#355B50] hover:bg-[#EDF6F1] dark:border-gray-700 dark:text-slate-300"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Stats Cards -->
                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">{{ datePresetLabel }}</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ formatPeso(totalExpenses) }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#EEF6F2] p-3 text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200">
                                <Receipt class="h-5 w-5" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">Inventory spend</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ formatPeso(inventoryShare) }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#EEF6F2] p-3 text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200">
                                <Package class="h-5 w-5" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">Pending payouts</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ formatPeso(pendingExpenses) }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#FFF5E8] p-3 text-[#A56A16] dark:bg-amber-100/15 dark:text-amber-200">
                                <CalendarDays class="h-5 w-5" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#73867F] dark:text-slate-300">Expense trend</p>
                                <p class="mt-2 text-2xl font-semibold text-[#183D34] dark:text-slate-100">
                                    {{ expenseTrend ?? '—' }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#EEF2FF] p-3 text-[#4253B5] dark:bg-amber-100/15 dark:text-amber-200">
                                <TrendingDown class="h-5 w-5" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Inventory Stats (when inventory filter is active) -->
                <div v-if="categoryFilter === 'inventory'" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Inventory Spend</p>
                        <p class="text-2xl font-semibold mt-1" style="color:#245c4a">{{ formatPeso(inventoryStats.total_spend) }}</p>
                    </div>
                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Transactions</p>
                        <p class="text-2xl font-semibold mt-1" style="color:#C5A059">{{ inventoryStats.total_transactions }}</p>
                    </div>
                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Average Per Transaction</p>
                        <p class="text-2xl font-semibold mt-1" style="color:#245c4a">{{ formatPeso(inventoryStats.average_per_transaction) }}</p>
                    </div>
                    <div class="rounded-[26px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Largest Purchase</p>
                        <p class="text-2xl font-semibold mt-1" style="color:#C5A059">{{ formatPeso(inventoryStats.largest_purchase) }}</p>
                    </div>
                </div>

                <!-- Top Suppliers Section (when inventory filter is active) -->
                <div v-if="categoryFilter === 'inventory' && Object.keys(expensesBySupplier).length > 0" class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                    <h3 class="text-sm font-semibold text-[#183D34] dark:text-slate-100 mb-4">Top Suppliers by Spend</h3>
                    <div class="space-y-3">
                        <div v-for="supplier in Object.values(expensesBySupplier)" :key="supplier.supplier_name" class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-foreground">{{ supplier.supplier_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ supplier.count }} transactions</p>
                            </div>
                            <p class="text-sm font-semibold" style="color:#245c4a">{{ formatPeso(supplier.total) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Daily Breakdown (when inventory filter is active) -->
                <div v-if="categoryFilter === 'inventory' && dailyBreakdown.length > 0" class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                    <h3 class="text-sm font-semibold text-[#183D34] dark:text-slate-100 mb-4">Daily Inventory Spend</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-[#E4ECE8] dark:border-gray-700">
                                    <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground py-2">Date</th>
                                    <th class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground py-2">Total</th>
                                    <th class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground py-2">Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="day in dailyBreakdown" :key="day.date" class="border-b border-[#E4ECE8] last:border-0 dark:border-gray-700">
                                    <td class="py-3 text-sm">{{ formatDate(day.date) }}</td>
                                    <td class="py-3 text-right font-semibold text-[#245c4a]">{{ formatPeso(day.total) }}</td>
                                    <td class="py-3 text-right text-muted-foreground">{{ day.count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_360px]">
                    <!-- Expense Records Table -->
                    <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm sm:p-6 dark:border-gray-700 dark:bg-slate-800">
                        <div class="mb-5 flex flex-col gap-3 sm:gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#EDF6F1] px-3 py-1 text-xs font-semibold text-[#245C4A] dark:bg-amber-100/15 dark:text-amber-200">
                                    <BadgeDollarSign class="h-3.5 w-3.5" />
                                    Expense records
                                </div>
                                <h2 class="text-base sm:text-lg font-semibold text-[#183D34] dark:text-slate-100">
                                    Recent transactions
                                </h2>
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

                        <!-- Desktop Table -->
                        <div class="hidden overflow-hidden rounded-2xl border border-[#E4ECE8] bg-white lg:block dark:border-gray-700 dark:bg-slate-900">
                            <div class="grid grid-cols-[1.5fr_1fr_0.9fr_0.8fr_0.8fr_0.9fr] bg-[#F7FAF8] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#789087] dark:bg-slate-800 dark:text-slate-300">
                                <span>Expense</span>
                                <span>Category</span>
                                <span>Date</span>
                                <span>Method</span>
                                <span>Amount</span>
                                <span>Status</span>
                            </div>

                            <div v-if="filteredExpenses.length" class="divide-y divide-[#E7EFEB] dark:divide-slate-700">
                                <div
                                    v-for="expense in filteredExpenses"
                                    :key="expense.id"
                                    class="grid grid-cols-[1.5fr_1fr_0.9fr_0.8fr_0.8fr_0.9fr] items-center px-5 py-4"
                                >
                                    <div class="pr-4">
                                        <p class="font-semibold text-[#183D34] dark:text-slate-100">
                                            {{ expense.title }}
                                        </p>
                                        <p v-if="expense.note" class="mt-1 text-sm text-[#70827B] dark:text-slate-300 truncate">
                                            {{ expense.note }}
                                        </p>
                                    </div>

                                    <div>
                                        <span class="inline-flex rounded-full bg-[#F0F5F2] px-3 py-1 text-sm font-medium text-[#355B50] dark:bg-slate-800 dark:text-slate-100">
                                            {{ expense.category }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-[#5F756D] dark:text-slate-300">
                                        {{ formatDate(expense.date) }}
                                    </p>

                                    <div>
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs" :class="getMethodBadge(expense.method)">
                                            {{ getMethodName(expense.method) }}
                                        </span>
                                    </div>

                                    <p class="font-semibold text-[#183D34] dark:text-slate-100">
                                        {{ formatPeso(expense.amount) }}
                                    </p>

                                    <div>
                                        <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold" :class="statusClass(expense.status)">
                                            {{ expense.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="px-5 py-12 text-center text-sm text-[#70827B] dark:text-slate-400" v-else>
                                No expenses match your current filters.
                            </div>
                        </div>

                        <!-- Mobile Cards -->
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
                                    <span class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(expense.status)">
                                        {{ expense.status }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">Category</p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">{{ expense.category }}</p>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">Method</p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">{{ getMethodName(expense.method) }}</p>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">Date</p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">{{ formatDate(expense.date) }}</p>
                                    </div>
                                    <div class="rounded-xl bg-white p-3 dark:bg-slate-900">
                                        <p class="text-xs uppercase tracking-[0.16em] text-[#7A8E86] dark:text-slate-400">Amount</p>
                                        <p class="mt-1 font-medium text-[#183D34] dark:text-slate-100">{{ formatPeso(expense.amount) }}</p>
                                    </div>
                                </div>

                                <div class="mt-3 flex justify-end gap-2">
                                    <button @click="openEditModal(expense)" class="rounded-lg p-2 text-[#245C4A] hover:bg-[#EDF6F1]">
                                        <Edit class="h-4 w-4" />
                                    </button>
                                    <button @click="deleteExpense(expense.id)" class="rounded-lg p-2 text-red-600 hover:bg-red-50">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <div v-if="!filteredExpenses.length" class="rounded-2xl border border-[#E4ECE8] bg-[#FBFCFC] px-5 py-10 text-center text-sm text-[#70827B] dark:border-gray-700 dark:bg-slate-900 dark:text-slate-400">
                                No expenses match your current filters.
                            </div>
                        </div>
                    </section>

                    <aside class="grid gap-6">
                        <!-- Category Breakdown -->
                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                            <div class="mb-4 flex items-center gap-2">
                                <Filter class="h-5 w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-lg font-semibold text-[#183D34] dark:text-slate-100">Category breakdown</h3>
                            </div>
                            <div class="space-y-4">
                                <div v-for="item in categorySummary" :key="item.category">
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-[#355B50] dark:text-slate-200">{{ item.category }}</p>
                                        <p class="text-sm font-semibold text-[#183D34] dark:text-slate-100">{{ formatPeso(item.amount) }}</p>
                                    </div>
                                    <div class="h-2 rounded-full bg-[#EBF1EE] dark:bg-slate-700">
                                        <div class="h-2 rounded-full bg-[#245C4A] dark:bg-amber-300" :style="{ width: `${item.width}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Monthly Spend -->
                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-slate-800">
                            <div class="mb-4 flex items-center gap-2">
                                <CircleDollarSign class="h-5 w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-lg font-semibold text-[#183D34] dark:text-slate-100">Monthly spend</h3>
                            </div>
                            <div class="space-y-4">
                                <div v-for="month in monthlySpend" :key="month.label" class="grid grid-cols-[44px_minmax(0,1fr)_80px] items-center gap-3">
                                    <span class="text-sm font-medium text-[#5F756D] dark:text-slate-300">{{ month.label }}</span>
                                    <div class="h-3 rounded-full bg-[#ECF2EF] dark:bg-slate-700">
                                        <div class="h-3 rounded-full bg-[#245C4A] dark:bg-amber-300" :style="{ width: `${(month.amount / maxMonthlySpend) * 100}%` }"></div>
                                    </div>
                                    <span class="text-right text-sm font-semibold text-[#183D34] dark:text-slate-100">{{ formatPeso(month.amount) }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Expense Notes -->
                        <section class="rounded-[28px] border border-[#DCE8E1] bg-[#F7FAF8] p-5 shadow-sm dark:border-gray-700 dark:bg-slate-900">
                            <div class="mb-4 flex items-center gap-2">
                                <CreditCard class="h-5 w-5 text-[#245C4A] dark:text-amber-200" />
                                <h3 class="text-lg font-semibold text-[#183D34] dark:text-slate-100">Expense notes</h3>
                            </div>
                            <div v-if="filteredExpenses.filter(e => e.note).length" class="space-y-2">
                                <div v-for="expense in filteredExpenses.filter(e => e.note)" :key="expense.id" class="rounded-2xl border border-[#E4ECE8] bg-white p-3 dark:border-slate-700 dark:bg-slate-800">
                                    <p class="text-xs font-semibold text-[#245C4A] dark:text-amber-300 truncate">{{ expense.title }}</p>
                                    <p class="mt-1 text-sm leading-5 text-[#5F756D] dark:text-slate-300">{{ expense.note }}</p>
                                </div>
                            </div>
                            <p v-else class="text-sm text-[#7A8E86] dark:text-slate-400">No notes for the current filters.</p>
                        </section>
                    </aside>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Expense Modal -->
    <Teleport to="body">
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="background:rgba(0,0,0,0.5)">
            <div class="bg-white dark:bg-slate-800 rounded-xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-5 py-4 border-b border-border flex justify-between items-center flex-shrink-0 bg-white dark:bg-slate-800 rounded-t-xl z-10">
                    <h3 class="text-sm font-semibold" style="color:#245c4a">Record Expense</h3>
                    <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                
                <form @submit.prevent="submitForm" class="p-5 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Title *</label>
                        <input v-model="form.title" type="text" required class="w-full px-3 py-2 rounded-xl border border-border" placeholder="e.g., Office Supplies" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Category *</label>
                        <select v-model="form.category" required class="w-full px-3 py-2 rounded-xl border border-border">
                            <option value="">Select category</option>
                            <option value="inventory">Inventory</option>
                            <option value="rent">Rent</option>
                            <option value="utilities">Utilities</option>
                            <option value="marketing">Marketing</option>
                            <option value="equipment">Equipment</option>
                            <option value="supplies">Supplies</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm">₱</span>
                            <input v-model="form.amount" type="number" step="0.01" required class="w-full pl-7 pr-3 py-2 rounded-xl border border-border" placeholder="0.00" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Date *</label>
                        <input v-model="form.date" type="date" required class="w-full px-3 py-2 rounded-xl border border-border" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Supplier (optional)</label>
                        <select v-model="form.supplier_id" class="w-full px-3 py-2 rounded-xl border border-border">
                            <option :value="null">Select supplier</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Reference Number</label>
                        <input v-model="form.reference_number" type="text" class="w-full px-3 py-2 rounded-xl border border-border" placeholder="Invoice or PO number" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Payment Method</label>
                        <select v-model="form.method" class="w-full px-3 py-2 rounded-xl border border-border">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Status</label>
                        <select v-model="form.status" class="w-full px-3 py-2 rounded-xl border border-border">
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                            <option value="Scheduled">Scheduled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Note</label>
                        <textarea v-model="form.note" rows="3" class="w-full px-3 py-2 rounded-xl border border-border resize-none" placeholder="Additional notes..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl border border-border">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 text-xs font-semibold rounded-xl text-white" style="background:#245c4a">
                            {{ form.processing ? 'Saving...' : 'Save Expense' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>

    <!-- Edit Expense Modal -->
    <Teleport to="body">
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="background:rgba(0,0,0,0.5)">
            <div class="bg-white dark:bg-slate-800 rounded-xl max-w-md w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="px-5 py-4 border-b border-border flex justify-between items-center flex-shrink-0 bg-white dark:bg-slate-800 rounded-t-xl z-10">
                    <h3 class="text-sm font-semibold" style="color:#245c4a">Edit Expense</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                
                <form @submit.prevent="submitForm" class="p-5 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Title *</label>
                        <input v-model="form.title" type="text" required class="w-full px-3 py-2 rounded-xl border border-border" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Category *</label>
                        <select v-model="form.category" required class="w-full px-3 py-2 rounded-xl border border-border">
                            <option value="inventory">Inventory</option>
                            <option value="rent">Rent</option>
                            <option value="utilities">Utilities</option>
                            <option value="marketing">Marketing</option>
                            <option value="equipment">Equipment</option>
                            <option value="supplies">Supplies</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm">₱</span>
                            <input v-model="form.amount" type="number" step="0.01" required class="w-full pl-7 pr-3 py-2 rounded-xl border border-border" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Date *</label>
                        <input v-model="form.date" type="date" required class="w-full px-3 py-2 rounded-xl border border-border" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Supplier</label>
                        <select v-model="form.supplier_id" class="w-full px-3 py-2 rounded-xl border border-border">
                            <option :value="null">Select supplier</option>
                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Reference Number</label>
                        <input v-model="form.reference_number" type="text" class="w-full px-3 py-2 rounded-xl border border-border" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Payment Method</label>
                        <select v-model="form.method" class="w-full px-3 py-2 rounded-xl border border-border">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Status</label>
                        <select v-model="form.status" class="w-full px-3 py-2 rounded-xl border border-border">
                            <option value="Paid">Paid</option>
                            <option value="Pending">Pending</option>
                            <option value="Scheduled">Scheduled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted-foreground mb-1.5">Note</label>
                        <textarea v-model="form.note" rows="3" class="w-full px-3 py-2 rounded-xl border border-border resize-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl border border-border">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 text-xs font-semibold rounded-xl text-white" style="background:#245c4a">
                            {{ form.processing ? 'Updating...' : 'Update Expense' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>