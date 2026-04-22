<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import Header from '@/components/Header.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import Sidebar from '@/components/Sidebar.vue';
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

const selectedPreset = ref(props.activePreset);
const customFrom = ref(props.activeFrom);
const customTo = ref(props.activeTo);

// Trimmed presets
const datePresets = [
    { key: 'last_month',  label: 'Last month' },
    { key: 'this_month',  label: 'This month' },
    { key: 'this_week',   label: 'This week' },
    { key: 'custom',      label: 'Custom' },
];

const applyFilter = () => {
    const params: Record<string, string> = { preset: selectedPreset.value };
    if (selectedPreset.value === 'custom') {
        if (customFrom.value) params.from = customFrom.value;
        if (customTo.value)   params.to   = customTo.value;
    }
    router.get('/vendor/analytics', params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const applyPreset = (presetKey: string) => {
    selectedPreset.value = presetKey;
    if (presetKey !== 'custom') {
        customFrom.value = '';
        customTo.value = '';
        applyFilter();
    }
};

const weeklySales       = computed(() => props.weeklySales);
const categoryMix       = computed(() => props.categoryMix);
const peakWindows       = computed(() => props.peakWindows);
const topProducts       = computed(() => props.topProducts);
const recentInsights    = computed(() => props.recentInsights);
const growthSignal      = computed(() => props.growthSignal);
const historicalTrends  = computed(() => props.historicalTrends);
const dailyRevenue      = computed(() => props.dailyRevenue);
const monthlyRevenue    = computed(() => props.monthlyRevenue);
const totalRevenue      = computed(() => props.totalRevenue);
const totalOrders       = computed(() => props.totalOrders);
const averageOrderValue = computed(() => props.averageOrderValue);

const bestDay = computed(() =>
    weeklySales.value.length
        ? weeklySales.value.reduce((best, current) =>
              current.revenue > best.revenue ? current : best)
        : { day: '—', revenue: 0, orders: 0 },
);

const maxRevenue          = computed(() => Math.max(1, ...weeklySales.value.map(i => i.revenue)));
const maxPeakOrders       = computed(() => Math.max(1, ...peakWindows.value.map(i => i.orders)));
const maxMonthlyRevenue   = computed(() => Math.max(1, ...monthlyRevenue.value.map(i => i.revenue)));
const maxHistoricalRevenue= computed(() => Math.max(1, ...historicalTrends.value.map(i => i.revenue)));

const isDailyView = computed(() => {
    if (!props.activeFrom && !props.activeTo && props.activePreset) {
        return ['today', 'yesterday', 'this_week', 'last_week', 'last_7'].includes(props.activePreset);
    }
    return weeklySales.value.length <= 14;
});

const chartTitle = computed(() => isDailyView.value ? 'Daily revenue trend' : 'Weekly revenue trend');

const formatPeso = (value: number) =>
    new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(value);

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
</script>

<template>
    <Head title="Sales Analytics" />

    <div class="dashboard-wrapper">
        <Header />

        <Sidebar role="vendor">
            <VendorNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="analytics-container">

                <!-- Hero -->
                <section class="analytics-hero-section">
                    <div class="hero-gradient-bg">
                        <div class="hero-content">
                            <div class="hero-text">
                                <div class="hero-badge">
                                    <Sparkles class="badge-icon" />
                                    Sales analytics
                                </div>
                                <h1 class="hero-title">Track revenue, orders, and top-performing products</h1>
                                <p class="hero-description">Get a clear view of store performance, customer order patterns, and your strongest-selling items.</p>
                            </div>
                            <div class="hero-stats">
                                <div class="stat-card">
                                    <p class="stat-label">Total revenue</p>
                                    <p class="stat-value">{{ formatPeso(totalRevenue) }}</p>
                                </div>
                                <div class="stat-card">
                                    <p class="stat-label">Total orders</p>
                                    <p class="stat-value">{{ totalOrders }}</p>
                                </div>
                                <div class="stat-card">
                                    <p class="stat-label">Avg order value</p>
                                    <p class="stat-value">{{ formatPeso(averageOrderValue) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Filter Bar -->
                <section class="filter-section">
                    <div class="filter-row">
                        <!-- Preset pills -->
                        <div class="filter-pills">
                            <button
                                v-for="preset in datePresets"
                                :key="preset.key"
                                @click="applyPreset(preset.key)"
                                class="filter-pill"
                                :class="{ 'filter-pill--active': selectedPreset === preset.key }"
                            >
                                {{ preset.label }}
                            </button>
                        </div>

                        <!-- Custom date inputs — only visible when Custom selected -->
                        <transition name="fade-slide">
                            <div v-if="selectedPreset === 'custom'" class="custom-range">
                                <input type="date" v-model="customFrom" class="date-input" />
                                <span class="date-sep">–</span>
                                <input type="date" v-model="customTo"   class="date-input" />
                                <button @click="applyFilter" class="apply-btn">Apply</button>
                            </div>
                        </transition>
                    </div>

                    <p class="range-label">
                        Showing: <span class="range-value">{{ rangeLabel }}</span>
                    </p>
                </section>

                <!-- Stats Cards -->
                <section class="stats-grid">
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div>
                                <p class="stats-card-label">Revenue</p>
                                <p class="stats-card-value">{{ formatPeso(totalRevenue) }}</p>
                            </div>
                            <div class="stats-card-icon stats-card-icon-green">
                                <Wallet class="icon-size" />
                            </div>
                        </div>
                        <p class="stats-card-description">Total earnings from completed orders.</p>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div>
                                <p class="stats-card-label">Orders completed</p>
                                <p class="stats-card-value">{{ totalOrders }}</p>
                            </div>
                            <div class="stats-card-icon stats-card-icon-green">
                                <ShoppingBag class="icon-size" />
                            </div>
                        </div>
                        <p class="stats-card-description">Total fulfilled customer orders tracked during the same period.</p>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div>
                                <p class="stats-card-label">Average order value</p>
                                <p class="stats-card-value">{{ formatPeso(averageOrderValue) }}</p>
                            </div>
                            <div class="stats-card-icon stats-card-icon-orange">
                                <ReceiptText class="icon-size" />
                            </div>
                        </div>
                        <p class="stats-card-description">Average amount spent per completed order this period.</p>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div>
                                <p class="stats-card-label">Growth signal</p>
                                <p class="stats-card-value">{{ growthSignal }}</p>
                            </div>
                            <div class="stats-card-icon stats-card-icon-blue">
                                <TrendingUp class="icon-size" />
                            </div>
                        </div>
                        <p class="stats-card-description">Period-over-period revenue change based on completed orders.</p>
                    </div>
                </section>

                <div class="analytics-grid">
                    <div class="analytics-main">

                        <!-- Revenue Chart -->
                        <section class="chart-section">
                            <div class="section-header">
                                <div class="section-badge">
                                    <BarChart3 class="badge-icon" />
                                    Revenue overview
                                </div>
                                <div class="section-title-wrapper">
                                    <div>
                                        <h2 class="section-title">{{ chartTitle }}</h2>
                                        <p class="section-description">Compare daily sales and quickly spot your strongest business days.</p>
                                    </div>
                                    <div v-if="weeklySales.length > 7" class="scroll-indicator">
                                        <span>← Scroll →</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="weeklySales.length > 0 && weeklySales.every(item => item.revenue === 0)" class="no-data-state">
                                <div class="no-data-content">
                                    <div class="no-data-icon"><BarChart3 class="no-data-icon-size" /></div>
                                    <p class="no-data-title">No sales for this period</p>
                                    <p class="no-data-description">Try selecting a different date range</p>
                                </div>
                            </div>

                            <div v-else-if="weeklySales.length > 0 && weeklySales.some(item => item.revenue > 0)">
                                <div class="chart-scroll-container">
                                    <div class="chart-bars-wrapper">
                                        <div class="chart-y-axis">
                                            <div class="y-axis-labels">
                                                <span>{{ formatPeso(maxRevenue) }}</span>
                                                <span>{{ formatPeso(maxRevenue * 0.75) }}</span>
                                                <span>{{ formatPeso(maxRevenue * 0.5) }}</span>
                                                <span>{{ formatPeso(maxRevenue * 0.25) }}</span>
                                                <span>₱0</span>
                                            </div>
                                        </div>
                                        <div class="chart-grid-container">
                                            <div class="chart-grid-lines">
                                                <div class="grid-line" style="top: 0%"></div>
                                                <div class="grid-line" style="top: 25%"></div>
                                                <div class="grid-line" style="top: 50%"></div>
                                                <div class="grid-line" style="top: 75%"></div>
                                                <div class="grid-line" style="top: 100%"></div>
                                            </div>
                                            <div class="chart-bars-container">
                                                <div
                                                    v-for="item in weeklySales"
                                                    :key="item.day"
                                                    class="chart-bar-item"
                                                    :style="{ width: weeklySales.length > 20 ? '35px' : (weeklySales.length > 14 ? '45px' : '55px') }"
                                                >
                                                    <div class="bar-wrapper">
                                                        <div
                                                            class="bar"
                                                            :class="item.revenue > 0 ? 'bar-active' : 'bar-inactive'"
                                                            :style="{ height: `${(item.revenue / maxRevenue) * 100}%`, minHeight: item.revenue > 0 ? '4px' : '0' }"
                                                        >
                                                            <div v-if="item.revenue > 0" class="bar-tooltip">{{ formatPeso(item.revenue) }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="bar-label">
                                                        <p class="bar-day" :title="item.day">{{ item.day.length > 4 ? item.day.slice(0, 3) : item.day }}</p>
                                                        <p class="bar-orders">{{ item.orders }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="chart-summary">
                                    <div class="summary-item">
                                        <p class="summary-label">Highest day</p>
                                        <p class="summary-value">{{ bestDay.day !== '—' ? bestDay.day.slice(0, 6) : 'N/A' }}</p>
                                        <p class="summary-revenue">{{ bestDay.day !== '—' ? formatPeso(bestDay.revenue) : '₱0' }}</p>
                                    </div>
                                    <div class="summary-item">
                                        <p class="summary-label">Avg daily revenue</p>
                                        <p class="summary-value">{{ formatPeso(totalRevenue / Math.max(1, weeklySales.length)) }}</p>
                                    </div>
                                    <div class="summary-item">
                                        <p class="summary-label">Total orders</p>
                                        <p class="summary-value">{{ weeklySales.reduce((sum, day) => sum + day.orders, 0) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="no-data-state">
                                <div class="no-data-content">
                                    <div class="no-data-icon"><BarChart3 class="no-data-icon-size" /></div>
                                    <p class="no-data-title">No revenue data available</p>
                                    <p class="no-data-description">Complete some orders to see your revenue trends</p>
                                </div>
                            </div>
                        </section>

                        <!-- Historical Trends -->
                        <section v-if="historicalTrends.length > 0" class="trends-section">
                            <div class="section-header">
                                <div class="section-badge"><TrendingUp class="badge-icon" /> Historical trends</div>
                                <h2 class="section-title">Revenue comparison</h2>
                                <p class="section-description">Compare revenue across different time periods.</p>
                            </div>
                            <div class="trends-list">
                                <div v-for="trend in historicalTrends" :key="trend.period" class="trend-item">
                                    <span class="trend-period">{{ trend.period }}</span>
                                    <div class="trend-bar-container">
                                        <div class="trend-bar-bg">
                                            <div class="trend-bar-fill" :style="{ width: `${(trend.revenue / maxHistoricalRevenue) * 100}%` }"></div>
                                        </div>
                                    </div>
                                    <span class="trend-value">{{ formatPeso(trend.revenue) }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Top Products -->
                        <section class="products-section">
                            <div class="section-header">
                                <div class="section-badge"><Package class="badge-icon" /> Product performance</div>
                                <h2 class="section-title">Top-selling products</h2>
                                <p class="section-description">Review which items are driving the most orders and revenue.</p>
                            </div>

                            <div class="products-desktop-table">
                                <div class="products-table-header">
                                    <span>Product</span>
                                    <span>Orders</span>
                                    <span>Revenue</span>
                                    <span>Growth</span>
                                </div>
                                <div class="products-table-body">
                                    <div v-for="product in topProducts" :key="product.name" class="products-table-row">
                                        <p class="product-name">{{ product.name }}</p>
                                        <p class="product-orders">{{ product.orders }}</p>
                                        <p class="product-revenue">{{ formatPeso(product.revenue) }}</p>
                                        <span class="product-growth">{{ product.growth }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="products-mobile-grid">
                                <div v-for="product in topProducts" :key="product.name" class="product-card">
                                    <div class="product-card-header">
                                        <div>
                                            <p class="product-card-name">{{ product.name }}</p>
                                            <p class="product-card-orders">{{ product.orders }} orders</p>
                                        </div>
                                        <span class="product-growth">{{ product.growth }}</span>
                                    </div>
                                    <div class="product-card-revenue">
                                        <p class="revenue-label">Revenue</p>
                                        <p class="revenue-value">{{ formatPeso(product.revenue) }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="analytics-sidebar">

                        <!-- Category Mix -->
                        <section class="category-section">
                            <div class="category-header">
                                <ShoppingBag class="category-icon" />
                                <h3 class="category-title">Category mix</h3>
                            </div>
                            <div class="category-list">
                                <div v-for="item in categoryMix" :key="item.label" class="category-item">
                                    <div class="category-label">
                                        <p class="category-name">{{ item.label }}</p>
                                        <p class="category-percentage">{{ item.value }}%</p>
                                    </div>
                                    <div class="category-bar-bg">
                                        <div class="category-bar-fill" :style="{ width: `${item.value}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Peak Windows -->
                        <section class="peak-section">
                            <div class="peak-header">
                                <Clock3 class="peak-icon" />
                                <h3 class="peak-title">Peak pick-up windows</h3>
                            </div>
                            <div class="peak-list">
                                <div v-for="slot in peakWindows" :key="slot.label" class="peak-item">
                                    <div class="peak-info">
                                        <p class="peak-label">{{ slot.label }}</p>
                                        <div class="peak-bar-bg">
                                            <div class="peak-bar-fill" :style="{ width: `${(slot.orders / maxPeakOrders) * 100}%` }"></div>
                                        </div>
                                    </div>
                                    <p class="peak-orders">{{ slot.orders }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Monthly Revenue -->
                        <section v-if="monthlyRevenue.length > 0" class="monthly-section">
                            <div class="monthly-header">
                                <Calendar class="monthly-icon" />
                                <h3 class="monthly-title">Monthly revenue</h3>
                            </div>
                            <div class="monthly-list">
                                <div v-for="month in monthlyRevenue" :key="month.month" class="monthly-item">
                                    <span class="monthly-label">{{ month.month }}</span>
                                    <div class="monthly-bar-bg">
                                        <div class="monthly-bar-fill" :style="{ width: `${(month.revenue / maxMonthlyRevenue) * 100}%` }"></div>
                                    </div>
                                    <span class="monthly-value">{{ formatPeso(month.revenue) }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Daily Breakdown -->
                        <section v-if="dailyRevenue.length > 0" class="daily-section">
                            <div class="daily-header">
                                <ReceiptText class="daily-icon" />
                                <h3 class="daily-title">Daily breakdown</h3>
                            </div>
                            <div class="daily-list">
                                <div v-for="day in dailyRevenue" :key="day.date" class="daily-item">
                                    <span class="daily-date">{{ formatDate(day.date) }}</span>
                                    <div class="daily-bar-bg">
                                        <div class="daily-bar-fill" :style="{ width: `${day.percentage}%` }"></div>
                                    </div>
                                    <span class="daily-value">{{ formatPeso(day.revenue) }}</span>
                                </div>
                            </div>
                        </section>

                        <!-- Quick Insights -->
                        <section class="insights-section">
                            <div class="insights-header">
                                <TrendingUp class="insights-icon" />
                                <h3 class="insights-title">Quick insights</h3>
                            </div>
                            <div class="insights-list">
                                <div v-for="insight in recentInsights" :key="insight" class="insight-item">
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

<style scoped>
/* ── Container ─────────────────────────────────────────────── */
.analytics-container {
    margin-left: auto;
    margin-right: auto;
    width: 100%;
    max-width: 80rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
}
@media (min-width: 640px)  { .analytics-container { gap: 1.5rem; padding: 1.5rem; } }
@media (min-width: 1024px) { .analytics-container { padding: 1.5rem 2rem; } }

/* ── Hero ──────────────────────────────────────────────────── */
.analytics-hero-section {
    overflow: hidden;
    border-radius: 30px;
    background-color: var(--card);
    border: 1px solid var(--border);
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
.hero-gradient-bg {
    background: linear-gradient(135deg, #1B4A3D 0%, #2C725E 100%);
    padding: 1.75rem 1.25rem;
}
@media (min-width: 640px) { .hero-gradient-bg { padding: 2rem 1.75rem; } }

.hero-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
@media (min-width: 1024px) {
    .hero-content { flex-direction: row; align-items: flex-end; justify-content: space-between; }
}

.hero-text { max-width: 36rem; }

.hero-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    border-radius: 9999px;
    background-color: rgba(255,255,255,0.12);
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.22em;
    color: #F7E8C6; margin-bottom: 0.75rem;
}
.badge-icon { height: 0.875rem; width: 0.875rem; }

.hero-title {
    font-size: 1.5rem; font-weight: 600;
    letter-spacing: -0.025em; color: white;
}
@media (min-width: 640px)  { .hero-title { font-size: 1.875rem; } }
@media (min-width: 1024px) { .hero-title { font-size: 2.25rem; } }

.hero-description {
    margin-top: 0.75rem; max-width: 36rem;
    font-size: 0.875rem; line-height: 1.75rem; color: #ECFDF5 !important;
}

.hero-stats {
    display: grid; gap: 0.75rem;
    grid-template-columns: repeat(3, 1fr);
}
@media (min-width: 1024px) { .hero-stats { min-width: 430px; } }

.stat-card {
    border-radius: 1rem; padding: 1rem;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.15);
}
.stat-label {
    font-size: 0.6875rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.22em; color: #ECFDF5 !important;
}
.stat-value { margin-top: 0.5rem; font-size: 1.25rem; font-weight: 600; color: white; }

/* ── Filter Bar ────────────────────────────────────────────── */
.filter-section {
    border-radius: 16px;
    border: 1px solid var(--border);
    background-color: var(--card);
    padding: 1rem 1.25rem;
}

.filter-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
}

.filter-pills {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-pill {
    padding: 0.4rem 1rem;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 9999px;
    border: 1px solid var(--border);
    background: var(--background);
    color: var(--muted-foreground);
    transition: background 0.15s, color 0.15s, border-color 0.15s;
    cursor: pointer;
    white-space: nowrap;
}
.filter-pill:hover {
    background: var(--secondary);
    color: var(--foreground);
}
.filter-pill--active {
    background: #1B4D3E;
    color: #ffffff;
    border-color: #1B4D3E;
    font-weight: 600;
}
:global(.dark) .filter-pill--active {
    background: #245C4A;
    border-color: #245C4A;
    color: #ffffff;
}

/* Custom range inputs */
.custom-range {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.date-input {
    padding: 0.35rem 0.65rem;
    font-size: 0.8rem;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--background);
    color: var(--foreground);
    outline: none;
}
.date-input:focus { border-color: #1B4D3E; }
:global(.dark) .date-input { background: var(--input); color: var(--foreground); border-color: var(--border); }

.date-sep { font-size: 0.85rem; color: var(--muted-foreground); }

.apply-btn {
    padding: 0.4rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 9999px;
    background: #1B4D3E;
    color: #ffffff;
    border: none;
    cursor: pointer;
    transition: background 0.15s;
}
.apply-btn:hover { background: #163d32; }

/* Transition */
.fade-slide-enter-active, .fade-slide-leave-active { transition: opacity 0.2s, transform 0.2s; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-4px); }

.range-label {
    margin-top: 0.65rem;
    font-size: 0.75rem;
    color: var(--muted-foreground);
}
.range-value { font-weight: 600; color: #1B4D3E; }
:global(.dark) .range-value { color: #6ee7b7; }

/* ── Stats Grid ────────────────────────────────────────────── */
.stats-grid {
    display: grid; gap: 1rem;
    grid-template-columns: 1fr;
}
@media (min-width: 768px)  { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }

.stats-card {
    border-radius: 26px;
    border: 1px solid var(--border);
    background-color: var(--card);
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
.stats-card-header { display: flex; align-items: flex-start; justify-content: space-between; }

.stats-card-label {
    font-size: 0.875rem; font-weight: 500;
    color: var(--muted-foreground);
}
.stats-card-value {
    margin-top: 0.5rem;
    font-size: 1.5rem; font-weight: 600;
    color: var(--foreground);
}
.stats-card-icon { border-radius: 1rem; padding: 0.75rem; }

.stats-card-icon-green { background-color: #EEF6F2; color: #245C4A; }
:global(.dark) .stats-card-icon-green { background-color: rgba(36,92,74,0.25); color: #6ee7b7; }

.stats-card-icon-orange { background-color: #FFF5E8; color: #A56A16; }
:global(.dark) .stats-card-icon-orange { background-color: rgba(165,106,22,0.2); color: #fbbf24; }

.stats-card-icon-blue { background-color: #EEF2FF; color: #4253B5; }
:global(.dark) .stats-card-icon-blue { background-color: rgba(66,83,181,0.2); color: #93c5fd; }

.icon-size { height: 1.25rem; width: 1.25rem; }
.stats-card-description { margin-top: 0.75rem; font-size: 0.875rem; color: var(--muted-foreground); }

/* ── Analytics Grid ────────────────────────────────────────── */
.analytics-grid { display: grid; gap: 1.5rem; }
@media (min-width: 1280px) {
    .analytics-grid { grid-template-columns: minmax(0, 1.65fr) 360px; }
}
.analytics-main    { display: grid; gap: 1.5rem; }
.analytics-sidebar { display: grid; gap: 1.5rem; }

/* ── Shared Section Card ───────────────────────────────────── */
.chart-section,
.trends-section,
.products-section,
.category-section,
.peak-section,
.monthly-section,
.daily-section,
.insights-section {
    border-radius: 28px;
    border: 1px solid var(--border);
    background-color: var(--card);
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    overflow: hidden;
}
@media (min-width: 640px) {
    .chart-section, .trends-section, .products-section { padding: 1.5rem; }
}

/* ── Section Header ────────────────────────────────────────── */
.section-header { margin-bottom: 1.25rem; }

.section-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    border-radius: 9999px;
    background-color: #EDF6F1;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem; font-weight: 600;
    color: #245C4A; margin-bottom: 0.5rem;
}
:global(.dark) .section-badge { background-color: rgba(36,92,74,0.25); color: #6ee7b7; }

.section-title-wrapper {
    display: flex; flex-direction: column;
    justify-content: space-between; gap: 0.5rem;
}
@media (min-width: 640px) { .section-title-wrapper { flex-direction: row; align-items: center; } }

.section-title { font-size: 1rem; font-weight: 600; color: var(--foreground); }
@media (min-width: 640px) { .section-title { font-size: 1.125rem; } }

.section-description { margin-top: 0.25rem; font-size: 0.75rem; color: var(--muted-foreground); }
@media (min-width: 640px) { .section-description { font-size: 0.875rem; } }

.scroll-indicator {
    font-size: 0.75rem; color: var(--muted-foreground);
    display: flex; align-items: center; gap: 0.25rem;
    background-color: var(--secondary);
    padding: 0.25rem 0.5rem; border-radius: 9999px; white-space: nowrap;
}

/* ── Chart ─────────────────────────────────────────────────── */
.chart-scroll-container { width: 100%; overflow-x: auto; overflow-y: visible; }
.chart-scroll-container::-webkit-scrollbar { height: 6px; }
.chart-scroll-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
.chart-scroll-container::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }

.chart-bars-wrapper {
    display: inline-flex; align-items: flex-end; justify-content: flex-start;
    gap: 0.25rem; height: 16rem; position: relative; min-width: 100%;
}

.chart-y-axis { position: relative; width: 2.5rem; flex-shrink: 0; }
.y-axis-labels {
    position: absolute; top: 0; bottom: 0; width: 2rem;
    text-align: right;
    display: flex; flex-direction: column; justify-content: space-between;
    font-size: 0.625rem; font-weight: 500;
    color: var(--muted-foreground); height: 200px;
}

.chart-grid-container { position: relative; flex: 1; min-width: 200px; }
.chart-grid-lines {
    position: absolute; left: 0; right: 0; top: 0; bottom: 0;
    pointer-events: none; height: 200px;
}
.grid-line {
    border-top: 1px solid var(--border);
    position: absolute; width: 100%;
}

.chart-bars-container {
    display: flex; align-items: flex-end; justify-content: flex-start;
    gap: 0.25rem; height: 200px; position: relative; z-index: 10;
}

.chart-bar-item { flex-shrink: 0; display: flex; flex-direction: column; align-items: center; }
.bar-wrapper { width: 100%; position: relative; cursor: pointer; height: 180px; }

.bar {
    position: absolute; bottom: 0; width: 100%;
    border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem;
    transition: all 0.3s;
}
.bar-active { background: linear-gradient(to top, #1B4D3E, #2C7A5E); }
:global(.dark) .bar-active { background: linear-gradient(to top, #d97706, #f59e0b); }
.bar-inactive { background-color: var(--secondary); }

.bar-tooltip {
    position: absolute; top: -1.75rem; left: 50%; transform: translateX(-50%);
    background-color: #111827; color: white;
    font-size: 0.625rem; font-weight: 500;
    border-radius: 0.25rem; padding: 0.125rem 0.375rem;
    white-space: nowrap; opacity: 0;
    transition: opacity 0.15s; pointer-events: none; z-index: 20;
}
.bar-wrapper:hover .bar-tooltip { opacity: 1; }

.bar-label { margin-top: 0.5rem; text-align: center; width: 100%; }
.bar-day {
    font-size: 0.5625rem; font-weight: 500;
    color: var(--muted-foreground);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 45px;
}
.bar-orders { font-size: 0.5rem; color: var(--muted-foreground); margin-top: 0.125rem; }

.chart-summary {
    margin-top: 1.5rem; padding-top: 1rem;
    border-top: 1px solid var(--border);
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;
}
.summary-item {
    text-align: center; padding: 0.5rem;
    border-radius: 0.5rem; transition: background-color 0.15s;
}
.summary-item:hover { background-color: var(--secondary); }
.summary-label { font-size: 0.75rem; color: var(--muted-foreground); }
.summary-value { font-size: 0.875rem; font-weight: 600; color: var(--foreground); }
.summary-revenue { font-size: 0.75rem; font-weight: 500; color: #245C4A; }
:global(.dark) .summary-revenue { color: #6ee7b7; }

/* ── No Data ───────────────────────────────────────────────── */
.no-data-state { padding: 2rem 0; }
.no-data-content {
    height: 16rem; width: 100%; border-radius: 0.75rem;
    background: var(--secondary);
    border: 1px dashed var(--border);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.no-data-icon {
    width: 4rem; height: 4rem; border-radius: 9999px;
    background-color: var(--background);
    display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;
}
.no-data-icon-size { width: 2rem; height: 2rem; color: var(--muted-foreground); }
.no-data-title { font-size: 0.875rem; font-weight: 500; color: var(--foreground); }
.no-data-description { font-size: 0.75rem; color: var(--muted-foreground); margin-top: 0.25rem; }

/* ── Trends ────────────────────────────────────────────────── */
.trends-list { margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem; }
.trend-item { display: flex; align-items: center; justify-content: space-between; }
.trend-period { font-size: 0.75rem; color: var(--muted-foreground); width: 8rem; }
.trend-bar-container { flex: 1; margin: 0 1rem; }
.trend-bar-bg { height: 0.5rem; border-radius: 9999px; background-color: var(--secondary); }
.trend-bar-fill { height: 0.5rem; border-radius: 9999px; background-color: #1B4D3E; }
:global(.dark) .trend-bar-fill { background-color: #10b981; }
.trend-value { font-size: 0.75rem; font-weight: 600; color: var(--foreground); }

/* ── Products ──────────────────────────────────────────────── */
.products-desktop-table {
    display: none; overflow: hidden;
    border-radius: 1rem; border: 1px solid var(--border);
}
@media (min-width: 1024px) { .products-desktop-table { display: block; } }

.products-table-header {
    display: grid; grid-template-columns: 1.6fr 0.8fr 1fr 0.7fr;
    background-color: var(--secondary);
    padding: 0.75rem 1.25rem;
    font-size: 0.75rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.18em;
    color: var(--muted-foreground);
}
.products-table-body { display: flex; flex-direction: column; }
.products-table-row {
    display: grid; grid-template-columns: 1.6fr 0.8fr 1fr 0.7fr;
    align-items: center; padding: 1rem 1.25rem;
    border-top: 1px solid var(--border);
}
.product-name { font-weight: 600; color: var(--foreground); }
.product-orders { font-size: 0.875rem; color: var(--muted-foreground); }
.product-revenue { font-weight: 600; color: var(--foreground); }
.product-growth {
    display: inline-flex; width: fit-content;
    border-radius: 9999px; background-color: #EDF6F1;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem; font-weight: 600;
    color: #245C4A;
}
:global(.dark) .product-growth { background-color: rgba(36,92,74,0.25); color: #6ee7b7; }

.products-mobile-grid { display: grid; gap: 1rem; }
@media (min-width: 1024px) { .products-mobile-grid { display: none; } }

.product-card {
    border-radius: 1rem; border: 1px solid var(--border);
    background-color: var(--card); padding: 1rem;
}
.product-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
.product-card-name { font-weight: 600; color: var(--foreground); }
.product-card-orders { margin-top: 0.25rem; font-size: 0.875rem; color: var(--muted-foreground); }
.product-card-revenue {
    margin-top: 1rem; border-radius: 0.75rem;
    background-color: var(--secondary); padding: 0.75rem;
}
.revenue-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.16em; color: var(--muted-foreground); }
.revenue-value { margin-top: 0.25rem; font-weight: 500; color: var(--foreground); }

/* ── Sidebar Sections ──────────────────────────────────────── */
/* Shared header row */
.category-header, .peak-header, .monthly-header, .daily-header, .insights-header {
    display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;
}
.category-icon, .peak-icon, .monthly-icon, .daily-icon, .insights-icon {
    height: 1rem; width: 1rem; color: #245C4A; flex-shrink: 0;
}
@media (min-width: 640px) {
    .category-icon, .peak-icon, .monthly-icon, .daily-icon, .insights-icon { height: 1.25rem; width: 1.25rem; }
}
:global(.dark) .category-icon,
:global(.dark) .peak-icon,
:global(.dark) .monthly-icon,
:global(.dark) .daily-icon,
:global(.dark) .insights-icon { color: #6ee7b7; }

.category-title, .peak-title, .monthly-title, .daily-title, .insights-title {
    font-size: 0.875rem; font-weight: 600; color: var(--foreground);
}
@media (min-width: 640px) {
    .category-title, .peak-title, .monthly-title, .daily-title, .insights-title { font-size: 1rem; }
}

/* Category */
.category-list { display: flex; flex-direction: column; gap: 1rem; }
.category-item { display: flex; flex-direction: column; gap: 0.5rem; }
.category-label { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
.category-name { font-size: 0.875rem; font-weight: 500; color: var(--muted-foreground); }
.category-percentage { font-size: 0.875rem; font-weight: 600; color: var(--foreground); }
.category-bar-bg { height: 0.5rem; border-radius: 9999px; background-color: var(--secondary); }
.category-bar-fill { height: 0.5rem; border-radius: 9999px; background-color: #245C4A; }
:global(.dark) .category-bar-fill { background-color: #10b981; }

/* Peak */
.peak-list { display: flex; flex-direction: column; gap: 1rem; }
.peak-item { display: grid; grid-template-columns: minmax(0,1fr) 56px; align-items: center; gap: 0.75rem; }
.peak-info { display: flex; flex-direction: column; gap: 0.5rem; }
.peak-label { font-size: 0.875rem; font-weight: 500; color: var(--muted-foreground); }
.peak-bar-bg { height: 0.5rem; border-radius: 9999px; background-color: var(--secondary); }
.peak-bar-fill { height: 0.5rem; border-radius: 9999px; background-color: #245C4A; }
:global(.dark) .peak-bar-fill { background-color: #10b981; }
.peak-orders { text-align: right; font-size: 0.875rem; font-weight: 600; color: var(--foreground); }

/* Monthly */
.monthly-list { display: flex; flex-direction: column; gap: 1rem; }
.monthly-item { display: grid; grid-template-columns: 44px minmax(0,1fr) 80px; align-items: center; gap: 0.75rem; }
.monthly-label { font-size: 0.875rem; font-weight: 500; color: var(--muted-foreground); }
.monthly-bar-bg { height: 0.75rem; border-radius: 9999px; background-color: var(--secondary); }
.monthly-bar-fill { height: 0.75rem; border-radius: 9999px; background-color: #245C4A; }
:global(.dark) .monthly-bar-fill { background-color: #10b981; }
.monthly-value { text-align: right; font-size: 0.875rem; font-weight: 600; color: var(--foreground); }

/* Daily */
.daily-list { display: flex; flex-direction: column; gap: 0.75rem; max-height: 20rem; overflow-y: auto; }
.daily-item { display: flex; align-items: center; justify-content: space-between; font-size: 0.875rem; }
.daily-date { color: var(--muted-foreground); }
.daily-bar-bg { flex: 1; margin: 0 1rem; height: 0.5rem; border-radius: 9999px; background-color: var(--secondary); }
.daily-bar-fill { height: 0.5rem; border-radius: 9999px; background-color: #245C4A; }
:global(.dark) .daily-bar-fill { background-color: #10b981; }
.daily-value { font-weight: 600; color: var(--foreground); }

/* Insights */
.insights-list { display: flex; flex-direction: column; gap: 0.75rem; }
.insight-item {
    border-radius: 1rem; border: 1px dashed var(--border);
    background-color: var(--secondary);
    padding: 1rem;
    font-size: 0.875rem; line-height: 1.5rem;
    color: var(--foreground);
}
</style>