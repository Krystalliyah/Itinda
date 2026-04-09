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

const isDailyView = computed(() => {
    if (!props.activeFrom && !props.activeTo && props.activePreset) {
        const shortPresets = ['today', 'yesterday', 'this_week', 'last_week', 'last_7'];
        return shortPresets.includes(props.activePreset);
    }
    return weeklySales.value.length <= 14;
});

const chartTitle = computed(() =>
    isDailyView.value ? 'Daily revenue trend' : 'Weekly revenue trend'
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
            <div class="analytics-container">
                <!-- Header Section -->
                <section class="analytics-hero-section">
                    <div class="hero-gradient-bg">
                        <div class="hero-content">
                            <div class="hero-text">
                                <div class="hero-badge">
                                    <Sparkles class="badge-icon" />
                                    Sales analytics
                                </div>

                                <h1 class="hero-title">
                                    Track revenue, orders, and top-performing products
                                </h1>

                                <p class="hero-description">
                                    Get a clear view of store performance, customer order patterns, and your strongest-selling items.
                                </p>
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

                <!-- Date Range Filter Bar -->
                <section class="filter-section">
                    <div class="filter-container">
                        <div class="filter-buttons">
                            <button
                                v-for="preset in datePresets"
                                :key="preset.key"
                                @click="applyPreset(preset.key)"
                                class="filter-btn"
                                :class="{ 'filter-btn-active': selectedPreset === preset.key }"
                            >
                                {{ preset.label }}
                            </button>
                        </div>
                        
                        <div class="filter-actions">
                            <div v-if="selectedPreset === 'custom'" class="custom-date-inputs">
                                <input
                                    type="date"
                                    v-model="customFrom"
                                    class="date-input"
                                />
                                <span class="date-separator">to</span>
                                <input
                                    type="date"
                                    v-model="customTo"
                                    class="date-input"
                                />
                            </div>
                            <button @click="applyFilter" class="apply-btn">
                                Apply
                            </button>
                            <button @click="resetFilter" class="reset-btn">
                                Reset
                            </button>
                        </div>
                    </div>
                    
                    <div class="range-label">
                        Showing data for: <span class="range-value">{{ rangeLabel }}</span>
                    </div>
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
                        <!-- Weekly Revenue Trend -->
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

                            <!-- Check if all revenue is zero -->
                            <div v-if="weeklySales.length > 0 && weeklySales.every(item => item.revenue === 0)" class="no-data-state">
                                <div class="no-data-content">
                                    <div class="no-data-icon">
                                        <BarChart3 class="no-data-icon-size" />
                                    </div>
                                    <p class="no-data-title">No sales for this period</p>
                                    <p class="no-data-description">Try selecting a different date range</p>
                                </div>
                            </div>

                            <!-- Professional Histogram with Horizontal Scroll -->
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
                                                    v-for="(item, index) in weeklySales"
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
                                                            <div v-if="item.revenue > 0" class="bar-tooltip">
                                                                {{ formatPeso(item.revenue) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bar-label">
                                                        <p class="bar-day" :title="item.day">
                                                            {{ item.day.length > 4 ? item.day.slice(0, 3) : item.day }}
                                                        </p>
                                                        <p class="bar-orders">
                                                            {{ item.orders }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="chart-summary">
                                    <div class="summary-item">
                                        <p class="summary-label">Highest Day</p>
                                        <p class="summary-value">{{ bestDay.day !== '—' ? bestDay.day.slice(0, 6) : 'N/A' }}</p>
                                        <p class="summary-revenue">{{ bestDay.day !== '—' ? formatPeso(bestDay.revenue) : '₱0' }}</p>
                                    </div>
                                    <div class="summary-item">
                                        <p class="summary-label">Avg Daily Revenue</p>
                                        <p class="summary-value">{{ formatPeso(totalRevenue / Math.max(1, weeklySales.length)) }}</p>
                                    </div>
                                    <div class="summary-item">
                                        <p class="summary-label">Total Orders</p>
                                        <p class="summary-value">{{ weeklySales.reduce((sum, day) => sum + day.orders, 0) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="no-data-state">
                                <div class="no-data-content">
                                    <div class="no-data-icon">
                                        <BarChart3 class="no-data-icon-size" />
                                    </div>
                                    <p class="no-data-title">No revenue data available</p>
                                    <p class="no-data-description">Complete some orders to see your revenue trends</p>
                                </div>
                            </div>
                        </section>

                        <!-- Historical Trends Section -->
                        <section v-if="historicalTrends.length > 0" class="trends-section">
                            <div class="section-header">
                                <div class="section-badge">
                                    <TrendingUp class="badge-icon" />
                                    Historical trends
                                </div>
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

                        <!-- Top Products Section -->
                        <section class="products-section">
                            <div class="section-header">
                                <div class="section-badge">
                                    <Package class="badge-icon" />
                                    Product performance
                                </div>
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

                        <!-- Peak Pick-up Windows -->
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

                        <!-- Monthly Revenue Chart -->
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

                        <!-- Daily Revenue Breakdown Table -->
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
/* Container Styles */
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

@media (min-width: 640px) {
    .analytics-container {
        gap: 1.5rem;
        padding: 1.5rem 1.5rem;
    }
}

@media (min-width: 1024px) {
    .analytics-container {
        padding: 1.5rem 2rem;
    }
}

/* Hero Section */
.analytics-hero-section {
    overflow: hidden;
    border-radius: 30px;
    border: 1px solid #DCE8E1;
    background-color: white;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

:global(.dark) .analytics-hero-section {
    border-color: #374151;
    background-color: #1e293b;
}

.hero-gradient-bg {
    background: linear-gradient(135deg, #1B4A3D 0%, #2C725E 100%);
    padding: 1.75rem 1.25rem;
}

@media (min-width: 640px) {
    .hero-gradient-bg {
        padding: 2rem 1.75rem;
    }
}

.hero-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (min-width: 1024px) {
    .hero-content {
        flex-direction: row;
        align-items: flex-end;
        justify-content: space-between;
    }
}

.hero-text {
    max-width: 36rem;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 9999px;
    background-color: rgba(255, 255, 255, 0.12);
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.22em;
    color: #F7E8C6;
    margin-bottom: 0.75rem;
}

.badge-icon {
    height: 0.875rem;
    width: 0.875rem;
}

.hero-title {
    font-size: 1.5rem;
    font-weight: 600;
    letter-spacing: -0.025em;
    color: white;
}

@media (min-width: 640px) {
    .hero-title {
        font-size: 1.875rem;
    }
}

@media (min-width: 1024px) {
    .hero-title {
        font-size: 2.25rem;
    }
}

.hero-description {
    margin-top: 0.75rem;
    max-width: 36rem;
    font-size: 0.875rem;
    line-height: 1.75rem;
    color: white;
}

@media (min-width: 640px) {
    .hero-description {
        font-size: 1rem;
    }
}

.hero-stats {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(3, 1fr);
}

@media (min-width: 1024px) {
    .hero-stats {
        min-width: 430px;
    }
}

.stat-card {
    border-radius: 1rem;
    padding: 1rem;
    backdrop-filter: blur(4px);
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.stat-label {
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.22em;
    color: white;
}

.stat-value {
    margin-top: 0.5rem;
    font-size: 1.25rem;
    font-weight: 600;
    color: white;
}

/* Filter Section */
.filter-section {
    border-radius: 0.75rem;
    border: 1px solid #DCE8E1;
    background-color: white;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    padding: 1rem;
}

:global(.dark) .filter-section {
    border-color: #374151;
    background-color: #1e293b;
}

.filter-container {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 1rem;
}

@media (min-width: 640px) {
    .filter-container {
        flex-direction: row;
        align-items: center;
    }
}

.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 0.5rem;
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
    background-color: #f3f4f6;
    color: #374151;
}

.filter-btn:hover {
    background-color: #e5e7eb;
}

.filter-btn-active {
    background-color: #059669;
    color: white;
}

:global(.dark) .filter-btn {
    background-color: #334155;
    color: #cbd5e1;
}

:global(.dark) .filter-btn:hover {
    background-color: #475569;
}

.filter-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.custom-date-inputs {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.date-input {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

:global(.dark) .date-input {
    border-color: #4b5563;
    background-color: #1e293b;
    color: #f1f5f9;
}

.date-separator {
    font-size: 0.75rem;
    color: #6b7280;
}

.apply-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 0.5rem;
    background-color: #059669;
    color: white;
}

.apply-btn:hover {
    background-color: #047857;
}

.reset-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
}

.reset-btn:hover {
    background-color: #f9fafb;
}

:global(.dark) .reset-btn {
    border-color: #4b5563;
    color: #cbd5e1;
}

:global(.dark) .reset-btn:hover {
    background-color: #334155;
}

.range-label {
    margin-top: 0.75rem;
    font-size: 0.75rem;
    color: #6b7280;
}

:global(.dark) .range-label {
    color: #64748b;
}

.range-value {
    font-weight: 600;
    color: #059669;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: 1fr;
}

@media (min-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1280px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stats-card {
    border-radius: 26px;
    border: 1px solid #DCE8E1;
    background-color: white;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

:global(.dark) .stats-card {
    border-color: #374151;
    background-color: #1e293b;
}

.stats-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}

.stats-card-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #73867F;
}

:global(.dark) .stats-card-label {
    color: #cbd5e1;
}

.stats-card-value {
    margin-top: 0.5rem;
    font-size: 1.5rem;
    font-weight: 600;
    color: #183D34;
}

:global(.dark) .stats-card-value {
    color: #f1f5f9;
}

.stats-card-icon {
    border-radius: 1rem;
    padding: 0.75rem;
}

.stats-card-icon-green {
    background-color: #EEF6F2;
    color: #245C4A;
}

:global(.dark) .stats-card-icon-green {
    background-color: rgba(245, 158, 11, 0.15);
    color: #fde68a;
}

.stats-card-icon-orange {
    background-color: #FFF5E8;
    color: #A56A16;
}

:global(.dark) .stats-card-icon-orange {
    background-color: rgba(245, 158, 11, 0.15);
    color: #fde68a;
}

.stats-card-icon-blue {
    background-color: #EEF2FF;
    color: #4253B5;
}

:global(.dark) .stats-card-icon-blue {
    background-color: rgba(245, 158, 11, 0.15);
    color: #fde68a;
}

.icon-size {
    height: 1.25rem;
    width: 1.25rem;
}

.stats-card-description {
    margin-top: 0.75rem;
    font-size: 0.875rem;
    color: #6C817A;
}

:global(.dark) .stats-card-description {
    color: #cbd5e1;
}

/* Analytics Grid */
.analytics-grid {
    display: grid;
    gap: 1.5rem;
}

@media (min-width: 1280px) {
    .analytics-grid {
        grid-template-columns: minmax(0, 1.65fr) 360px;
    }
}

.analytics-main {
    display: grid;
    gap: 1.5rem;
}

.analytics-sidebar {
    display: grid;
    gap: 1.5rem;
}

/* Chart Section */
.chart-section {
    border-radius: 28px;
    border: 1px solid #DCE8E1;
    background-color: white;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    overflow: hidden;
}

@media (min-width: 640px) {
    .chart-section {
        padding: 1.5rem;
    }
}

:global(.dark) .chart-section {
    border-color: #374151;
    background-color: #1e293b;
}

.section-header {
    margin-bottom: 1.25rem;
}

.section-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 9999px;
    background-color: #EDF6F1;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #245C4A;
    margin-bottom: 0.5rem;
}

:global(.dark) .section-badge {
    background-color: rgba(245, 158, 11, 0.15);
    color: #fde68a;
}

.section-title-wrapper {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 0.5rem;
}

@media (min-width: 640px) {
    .section-title-wrapper {
        flex-direction: row;
        align-items: center;
    }
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #183D34;
}

@media (min-width: 640px) {
    .section-title {
        font-size: 1.125rem;
    }
}

:global(.dark) .section-title {
    color: #f1f5f9;
}

.section-description {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: #6E817A;
}

@media (min-width: 640px) {
    .section-description {
        font-size: 0.875rem;
    }
}

:global(.dark) .section-description {
    color: #cbd5e1;
}

.scroll-indicator {
    font-size: 0.75rem;
    color: #80928B;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    background-color: #f9fafb;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    white-space: nowrap;
}

:global(.dark) .scroll-indicator {
    color: #94a3b8;
    background-color: rgba(51, 65, 85, 0.5);
}

/* Chart Styles */
.chart-scroll-container {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
}

.chart-scroll-container::-webkit-scrollbar {
    height: 6px;
}

.chart-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.chart-scroll-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.chart-bars-wrapper {
    display: inline-flex;
    align-items: flex-end;
    justify-content: flex-start;
    gap: 0.25rem;
    height: 16rem;
    position: relative;
    min-width: 100%;
}

.chart-y-axis {
    position: relative;
    width: 2.5rem;
    flex-shrink: 0;
}

.y-axis-labels {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 2rem;
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    font-size: 0.625rem;
    font-weight: 500;
    color: #80928B;
    height: 200px;
}

:global(.dark) .y-axis-labels {
    color: #94a3b8;
}

.chart-grid-container {
    position: relative;
    flex: 1;
    min-width: 200px;
}

.chart-grid-lines {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    pointer-events: none;
    height: 200px;
}

.grid-line {
    border-top: 1px solid #E5EEEA;
    position: absolute;
    width: 100%;
}

:global(.dark) .grid-line {
    border-color: #334155;
}

.chart-bars-container {
    display: flex;
    align-items: flex-end;
    justify-content: flex-start;
    gap: 0.25rem;
    height: 12rem;
    position: relative;
    z-index: 10;
    height: 200px;
}

.chart-bar-item {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.bar-wrapper {
    width: 100%;
    position: relative;
    cursor: pointer;
    height: 180px;
}

.bar {
    position: absolute;
    bottom: 0;
    width: 100%;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    transition-property: all;
    transition-duration: 300ms;
}

.bar-active {
    background: linear-gradient(to top, #245C4A, #3D7A5C);
}

.bar-inactive {
    background-color: #e5e7eb;
}

:global(.dark) .bar-inactive {
    background-color: #334155;
}

:global(.dark) .bar-active {
    background: linear-gradient(to top, #d97706, #f59e0b);
}

.bar:hover .bar-active {
    opacity: 0.8;
}

.bar-tooltip {
    position: absolute;
    top: -1.75rem;
    left: 50%;
    transform: translateX(-50%);
    background-color: #111827;
    color: white;
    font-size: 0.625rem;
    font-weight: 500;
    border-radius: 0.25rem;
    padding: 0.125rem 0.375rem;
    white-space: nowrap;
    opacity: 0;
    transition-property: opacity;
    transition-duration: 150ms;
    pointer-events: none;
    z-index: 20;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
}

.bar-wrapper:hover .bar-tooltip {
    opacity: 1;
}

.bar-label {
    margin-top: 0.5rem;
    text-align: center;
    width: 100%;
}

.bar-day {
    font-size: 0.5625rem;
    font-weight: 500;
    color: #5F756D;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 45px;
}

:global(.dark) .bar-day {
    color: #94a3b8;
}

.bar-orders {
    font-size: 0.5rem;
    color: #80928B;
    margin-top: 0.125rem;
}

:global(.dark) .bar-orders {
    color: #64748b;
}

/* Chart Summary */
.chart-summary {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #E5EEEA;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

:global(.dark) .chart-summary {
    border-color: #334155;
}

.summary-item {
    text-align: center;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition-property: background-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.summary-item:hover {
    background-color: #f9fafb;
}

:global(.dark) .summary-item:hover {
    background-color: #334155;
}

.summary-label {
    font-size: 0.75rem;
    color: #80928B;
}

:global(.dark) .summary-label {
    color: #94a3b8;
}

.summary-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

:global(.dark) .summary-value {
    color: #f1f5f9;
}

.summary-revenue {
    font-size: 0.75rem;
    font-weight: 500;
    color: #245C4A;
}

:global(.dark) .summary-revenue {
    color: #fbbf24;
}

/* No Data State */
.no-data-state {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

.no-data-content {
    position: relative;
    height: 16rem;
    width: 100%;
    border-radius: 0.75rem;
    background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
    border: 1px dashed #d1d5db;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

:global(.dark) .no-data-content {
    background: linear-gradient(to bottom, #1e293b, #0f172a);
    border-color: #334155;
}

.no-data-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 9999px;
    background-color: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

:global(.dark) .no-data-icon {
    background-color: #334155;
}

.no-data-icon-size {
    width: 2rem;
    height: 2rem;
    color: #9ca3af;
}

:global(.dark) .no-data-icon-size {
    color: #64748b;
}

.no-data-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
}

:global(.dark) .no-data-title {
    color: #9ca3af;
}

.no-data-description {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.25rem;
}

:global(.dark) .no-data-description {
    color: #64748b;
}

/* Trends Section */
.trends-section {
    border-radius: 28px;
    border: 1px solid #DCE8E1;
    background-color: white;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

@media (min-width: 640px) {
    .trends-section {
        padding: 1.5rem;
    }
}

:global(.dark) .trends-section {
    border-color: #374151;
    background-color: #1e293b;
}

.trends-list {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.trend-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.trend-period {
    font-size: 0.75rem;
    color: #6b7280;
    width: 8rem;
}

.trend-bar-container {
    flex: 1;
    margin-left: 1rem;
    margin-right: 1rem;
}

.trend-bar-bg {
    height: 0.5rem;
    border-radius: 9999px;
    background-color: #f3f4f6;
}

:global(.dark) .trend-bar-bg {
    background-color: #334155;
}

.trend-bar-fill {
    height: 0.5rem;
    border-radius: 9999px;
    background-color: #10b981;
}

.trend-value {
    font-size: 0.75rem;
    font-weight: 600;
}

/* Products Section */
.products-section {
    border-radius: 28px;
    border: 1px solid #DCE8E1;
    background-color: white;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

@media (min-width: 640px) {
    .products-section {
        padding: 1.5rem;
    }
}

:global(.dark) .products-section {
    border-color: #374151;
    background-color: #1e293b;
}

.products-desktop-table {
    display: none;
    overflow: hidden;
    border-radius: 1rem;
    border: 1px solid #E4ECE8;
}

@media (min-width: 1024px) {
    .products-desktop-table {
        display: block;
    }
}

:global(.dark) .products-desktop-table {
    border-color: #374151;
    background-color: #0f172a;
}

.products-table-header {
    display: grid;
    grid-template-columns: 1.6fr 0.8fr 1fr 0.7fr;
    background-color: #F7FAF8;
    padding: 0.75rem 1.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: #789087;
}

:global(.dark) .products-table-header {
    background-color: #1e293b;
    color: #cbd5e1;
}

.products-table-body {
    display: flex;
    flex-direction: column;
}

.products-table-row {
    display: grid;
    grid-template-columns: 1.6fr 0.8fr 1fr 0.7fr;
    align-items: center;
    padding: 1rem 1.25rem;
    border-top: 1px solid #E7EFEB;
}

:global(.dark) .products-table-row {
    border-color: #334155;
}

.product-name {
    font-weight: 600;
    color: #183D34;
}

:global(.dark) .product-name {
    color: #f1f5f9;
}

.product-orders {
    font-size: 0.875rem;
    color: #5F756D;
}

:global(.dark) .product-orders {
    color: #cbd5e1;
}

.product-revenue {
    font-weight: 600;
    color: #183D34;
}

:global(.dark) .product-revenue {
    color: #f1f5f9;
}

.product-growth {
    display: inline-flex;
    width: fit-content;
    border-radius: 9999px;
    background-color: #EAF7F0;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #1D6A4F;
}

:global(.dark) .product-growth {
    background-color: rgba(16, 185, 129, 0.1);
    color: #6ee7b7;
}

.products-mobile-grid {
    display: grid;
    gap: 1rem;
}

@media (min-width: 1024px) {
    .products-mobile-grid {
        display: none;
    }
}

.product-card {
    border-radius: 1rem;
    border: 1px solid #E4ECE8;
    background-color: #FBFCFC;
    padding: 1rem;
}

:global(.dark) .product-card {
    border-color: #374151;
    background-color: #0f172a;
}

.product-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.product-card-name {
    font-weight: 600;
    color: #183D34;
}

:global(.dark) .product-card-name {
    color: #f1f5f9;
}

.product-card-orders {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #70827B;
}

:global(.dark) .product-card-orders {
    color: #cbd5e1;
}

.product-card-revenue {
    margin-top: 1rem;
    border-radius: 0.75rem;
    background-color: white;
    padding: 0.75rem;
}

:global(.dark) .product-card-revenue {
    background-color: #0f172a;
}

.revenue-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: #7A8E86;
}

:global(.dark) .revenue-label {
    color: #94a3b8;
}

.revenue-value {
    margin-top: 0.25rem;
    font-weight: 500;
    color: #183D34;
}

:global(.dark) .revenue-value {
    color: #f1f5f9;
}

/* Category Section */
.category-section {
    border-radius: 28px;
    border: 1px solid #DCE8E1;
    background-color: white;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

:global(.dark) .category-section {
    border-color: #374151;
    background-color: #1e293b;
}

.category-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.category-icon {
    height: 1rem;
    width: 1rem;
    color: #245C4A;
}

@media (min-width: 640px) {
    .category-icon {
        height: 1.25rem;
        width: 1.25rem;
    }
}

:global(.dark) .category-icon {
    color: #fde68a;
}

.category-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

@media (min-width: 640px) {
    .category-title {
        font-size: 1rem;
    }
}

:global(.dark) .category-title {
    color: #f1f5f9;
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.category-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.category-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.category-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #355B50;
}

:global(.dark) .category-name {
    color: #cbd5e1;
}

.category-percentage {
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

:global(.dark) .category-percentage {
    color: #f1f5f9;
}

.category-bar-bg {
    height: 0.5rem;
    border-radius: 9999px;
    background-color: #EBF1EE;
}

:global(.dark) .category-bar-bg {
    background-color: #334155;
}

.category-bar-fill {
    height: 0.5rem;
    border-radius: 9999px;
    background-color: #245C4A;
}

:global(.dark) .category-bar-fill {
    background-color: #fcd34d;
}

/* Peak Section */
.peak-section {
    border-radius: 28px;
    border: 1px solid #DCE8E1;
    background-color: white;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

:global(.dark) .peak-section {
    border-color: #374151;
    background-color: #1e293b;
}

.peak-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.peak-icon {
    height: 1rem;
    width: 1rem;
    color: #245C4A;
}

@media (min-width: 640px) {
    .peak-icon {
        height: 1.25rem;
        width: 1.25rem;
    }
}

:global(.dark) .peak-icon {
    color: #fde68a;
}

.peak-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

@media (min-width: 640px) {
    .peak-title {
        font-size: 1rem;
    }
}

:global(.dark) .peak-title {
    color: #f1f5f9;
}

.peak-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.peak-item {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 56px;
    align-items: center;
    gap: 0.75rem;
}

.peak-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.peak-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #355B50;
}

:global(.dark) .peak-label {
    color: #cbd5e1;
}

.peak-bar-bg {
    height: 0.5rem;
    border-radius: 9999px;
    background-color: #ECF2EF;
}

:global(.dark) .peak-bar-bg {
    background-color: #334155;
}

.peak-bar-fill {
    height: 0.5rem;
    border-radius: 9999px;
    background-color: #245C4A;
}

:global(.dark) .peak-bar-fill {
    background-color: #fcd34d;
}

.peak-orders {
    text-align: right;
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

:global(.dark) .peak-orders {
    color: #f1f5f9;
}

/* Monthly Section */
.monthly-section {
    border-radius: 28px;
    border: 1px solid #DCE8E1;
    background-color: white;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

:global(.dark) .monthly-section {
    border-color: #374151;
    background-color: #1e293b;
}

.monthly-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.monthly-icon {
    height: 1rem;
    width: 1rem;
    color: #245C4A;
}

@media (min-width: 640px) {
    .monthly-icon {
        height: 1.25rem;
        width: 1.25rem;
    }
}

:global(.dark) .monthly-icon {
    color: #fde68a;
}

.monthly-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

@media (min-width: 640px) {
    .monthly-title {
        font-size: 1rem;
    }
}

:global(.dark) .monthly-title {
    color: #f1f5f9;
}

.monthly-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.monthly-item {
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr) 80px;
    align-items: center;
    gap: 0.75rem;
}

.monthly-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #5F756D;
}

:global(.dark) .monthly-label {
    color: #cbd5e1;
}

.monthly-bar-bg {
    height: 0.75rem;
    border-radius: 9999px;
    background-color: #ECF2EF;
}

:global(.dark) .monthly-bar-bg {
    background-color: #334155;
}

.monthly-bar-fill {
    height: 0.75rem;
    border-radius: 9999px;
    background-color: #245C4A;
}

:global(.dark) .monthly-bar-fill {
    background-color: #fcd34d;
}

.monthly-value {
    text-align: right;
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

:global(.dark) .monthly-value {
    color: #f1f5f9;
}

/* Daily Section */
.daily-section {
    border-radius: 28px;
    border: 1px solid #DCE8E1;
    background-color: white;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

:global(.dark) .daily-section {
    border-color: #374151;
    background-color: #1e293b;
}

.daily-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.daily-icon {
    height: 1rem;
    width: 1rem;
    color: #245C4A;
}

@media (min-width: 640px) {
    .daily-icon {
        height: 1.25rem;
        width: 1.25rem;
    }
}

:global(.dark) .daily-icon {
    color: #fde68a;
}

.daily-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

@media (min-width: 640px) {
    .daily-title {
        font-size: 1rem;
    }
}

:global(.dark) .daily-title {
    color: #f1f5f9;
}

.daily-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 20rem;
    overflow-y: auto;
}

.daily-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.875rem;
}

.daily-date {
    color: #6b7280;
}

:global(.dark) .daily-date {
    color: #94a3b8;
}

.daily-bar-bg {
    flex: 1;
    margin-left: 1rem;
    margin-right: 1rem;
    height: 0.5rem;
    border-radius: 9999px;
    background-color: #f3f4f6;
}

:global(.dark) .daily-bar-bg {
    background-color: #334155;
}

.daily-bar-fill {
    height: 0.5rem;
    border-radius: 9999px;
    background-color: #10b981;
}

.daily-value {
    font-weight: 600;
}

/* Insights Section */
.insights-section {
    border-radius: 28px;
    border: 1px solid #DCE8E1;
    background-color: #F7FAF8;
    padding: 1.25rem;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

:global(.dark) .insights-section {
    border-color: #374151;
    background-color: #0f172a;
}

.insights-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.insights-icon {
    height: 1rem;
    width: 1rem;
    color: #245C4A;
}

@media (min-width: 640px) {
    .insights-icon {
        height: 1.25rem;
        width: 1.25rem;
    }
}

:global(.dark) .insights-icon {
    color: #fde68a;
}

.insights-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #183D34;
}

@media (min-width: 640px) {
    .insights-title {
        font-size: 1rem;
    }
}

:global(.dark) .insights-title {
    color: #f1f5f9;
}

.insights-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.insight-item {
    border-radius: 1rem;
    border: 1px dashed #CBD9D2;
    background-color: white;
    padding: 1rem;
    font-size: 0.875rem;
    line-height: 1.5rem;
    color: #5F756D;
}

:global(.dark) .insight-item {
    border-color: #334155;
    background-color: #1e293b;
    color: #cbd5e1;
}
</style>