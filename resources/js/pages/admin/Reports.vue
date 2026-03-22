<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
import { useSidebar } from '@/composables/useSidebar';
import {
    DocumentChartBarIcon,
    BuildingStorefrontIcon,
    UsersIcon,
    Squares2X2Icon,
    ChartBarIcon,
    CheckCircleIcon,
    ClockIcon,
    ArrowTrendingUpIcon,
    ShieldCheckIcon,
    StarIcon,
} from '@heroicons/vue/24/outline';

/* ------------------------------------------------------------------ */
/*  Types                                                               */
/* ------------------------------------------------------------------ */
interface MonthPoint  { month: string; count: number }
interface TopVendor   { id: string; name: string; domain: string | null; created_at: string; days_active: number }
interface FunnelStep  { label: string; value: number }
interface RecentCustomer { id: number; name: string; email: string; verified: boolean; created_at: string }
interface CategoryRow { name: string; count: number; pct: number; color: string }

interface Props {
    overview?: {
        totalVendors: number; activeVendors: number; pendingVendors: number
        totalCustomers: number; approvalRate: number
        newVendorsThisMonth: number; newCustomersThisMonth: number
        healthScore: number
    }
    vendorPerformance?: {
        topVendors: TopVendor[]
        monthlyRegistrations: MonthPoint[]
        approvalFunnel: FunnelStep[]
    }
    customerActivity?: {
        totalCustomers: number
        monthlySignups: MonthPoint[]
        verified: number; unverified: number; verificationRate: number
        recentSignups: RecentCustomer[]
    }
    categoryBreakdown?: {
        breakdown: CategoryRow[]
        totalUnique: number
    }
}

const props = defineProps<Props>()

/* ------------------------------------------------------------------ */
/*  Safe accessors                                                      */
/* ------------------------------------------------------------------ */
const ov  = computed(() => props.overview          ?? { totalVendors:0, activeVendors:0, pendingVendors:0, totalCustomers:0, approvalRate:0, newVendorsThisMonth:0, newCustomersThisMonth:0, healthScore:0 })
const vp  = computed(() => props.vendorPerformance ?? { topVendors:[], monthlyRegistrations:[], approvalFunnel:[] })
const ca  = computed(() => props.customerActivity  ?? { totalCustomers:0, monthlySignups:[], verified:0, unverified:0, verificationRate:0, recentSignups:[] })
const cb  = computed(() => props.categoryBreakdown ?? { breakdown:[], totalUnique:0 })

/* ------------------------------------------------------------------ */
/*  Sidebar                                                             */
/* ------------------------------------------------------------------ */
const { isCollapsed } = useSidebar()
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}))

/* ------------------------------------------------------------------ */
/*  Active tab                                                          */
/* ------------------------------------------------------------------ */
type Tab = 'overview' | 'vendors' | 'customers' | 'categories'
const activeTab = ref<Tab>('overview')

const tabs: { key: Tab; label: string; icon: any }[] = [
    { key: 'overview',   label: 'Platform Overview', icon: ChartBarIcon },
    { key: 'vendors',    label: 'Vendor Performance', icon: BuildingStorefrontIcon },
    { key: 'customers',  label: 'Customer Activity',  icon: UsersIcon },
    { key: 'categories', label: 'Categories',         icon: Squares2X2Icon },
]

/* ------------------------------------------------------------------ */
/*  SVG line/area chart helper                                          */
/* ------------------------------------------------------------------ */
const CW = 520; const CH = 140
const PT = 12;  const PR = 12; const PB = 24; const PL = 28

function chartPoints(data: MonthPoint[]) {
    const max    = Math.max(...data.map(d => d.count), 1)
    const innerW = CW - PL - PR
    const innerH = CH - PT - PB
    return data.map((d, i) => ({
        x: PL + (i / (data.length - 1 || 1)) * innerW,
        y: PT + innerH - (d.count / max) * innerH,
        count: d.count,
        month: d.month,
    }))
}

function polyline(pts: { x: number; y: number }[]) {
    return pts.map(p => `${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ')
}

function areaPath(pts: { x: number; y: number }[]) {
    if (!pts.length) return ''
    const b = CH - PB
    return `M${pts[0].x},${b} ` + pts.map(p => `L${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ') + ` L${pts[pts.length-1].x},${b} Z`
}

const vendorPts   = computed(() => chartPoints(vp.value.monthlyRegistrations))
const customerPts = computed(() => chartPoints(ca.value.monthlySignups))

const xLabels = (pts: { x: number; month: string }[]) =>
    pts.map(p => ({ x: p.x, label: p.month.split(' ')[0] }))

/* ------------------------------------------------------------------ */
/*  Health colour                                                       */
/* ------------------------------------------------------------------ */
const healthColor = computed(() => {
    const s = ov.value.healthScore
    if (s >= 80) return '#10b981'
    if (s >= 60) return '#3b82f6'
    if (s >= 40) return '#f59e0b'
    return '#ef4444'
})
const healthLabel = computed(() => {
    const s = ov.value.healthScore
    if (s >= 80) return 'Excellent'
    if (s >= 60) return 'Good'
    if (s >= 40) return 'Fair'
    return 'Needs Attention'
})

/* ------------------------------------------------------------------ */
/*  Donut helper                                                        */
/* ------------------------------------------------------------------ */
const DONUT_R = 42; const CIRC = 2 * Math.PI * DONUT_R
const approvalDash = computed(() => {
    const f = (ov.value.approvalRate / 100) * CIRC
    return `${f.toFixed(1)} ${(CIRC - f).toFixed(1)}`
})
const verifyDash = computed(() => {
    const f = (ca.value.verificationRate / 100) * CIRC
    return `${f.toFixed(1)} ${(CIRC - f).toFixed(1)}`
})

/* ------------------------------------------------------------------ */
/*  Funnel bar widths                                                   */
/* ------------------------------------------------------------------ */
const funnelMax = computed(() => Math.max(...vp.value.approvalFunnel.map(f => f.value), 1))

/* ------------------------------------------------------------------ */
/*  Helpers                                                             */
/* ------------------------------------------------------------------ */
function initials(name: string) {
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
}
function formatDate(d: string | null) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<template>
    <Head title="Platform Reports" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="admin"><AdminNav /></Sidebar>

        <main :class="contentClass">
            <div class="page-container">

                <!-- ── Page Header ── -->
                <div class="page-header">
                    <div class="page-header-icon">
                        <DocumentChartBarIcon class="header-icon" />
                    </div>
                    <div class="page-header-text">
                        <h1>Platform Reports</h1>
                        <p>Analytics, performance metrics and platform health at a glance</p>
                    </div>
                </div>

                <!-- ── Tab Bar ── -->
                <div class="tab-bar">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        class="tab-btn"
                        :class="{ active: activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        <component :is="tab.icon" class="tab-icon" />
                        {{ tab.label }}
                    </button>
                </div>

                <!-- ══════════════════════════════════════════════════
                     TAB: PLATFORM OVERVIEW
                ══════════════════════════════════════════════════ -->
                <template v-if="activeTab === 'overview'">

                    <!-- KPI strip -->
                    <div class="kpi-grid">
                        <div class="kpi-card">
                            <div class="kpi-icon-wrap" style="background:#6366f11a">
                                <BuildingStorefrontIcon class="kpi-icon" style="color:#6366f1" />
                            </div>
                            <div>
                                <p class="kpi-label">Total Vendors</p>
                                <p class="kpi-val">{{ ov.totalVendors }}</p>
                                <p class="kpi-sub">+{{ ov.newVendorsThisMonth }} this month</p>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon-wrap" style="background:#10b9811a">
                                <CheckCircleIcon class="kpi-icon" style="color:#10b981" />
                            </div>
                            <div>
                                <p class="kpi-label">Active Vendors</p>
                                <p class="kpi-val">{{ ov.activeVendors }}</p>
                                <p class="kpi-sub">{{ ov.approvalRate }}% approval rate</p>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon-wrap" style="background:#3b82f61a">
                                <UsersIcon class="kpi-icon" style="color:#3b82f6" />
                            </div>
                            <div>
                                <p class="kpi-label">Total Customers</p>
                                <p class="kpi-val">{{ ov.totalCustomers }}</p>
                                <p class="kpi-sub">+{{ ov.newCustomersThisMonth }} this month</p>
                            </div>
                        </div>
                        <div class="kpi-card">
                            <div class="kpi-icon-wrap" style="background:#f59e0b1a">
                                <ClockIcon class="kpi-icon" style="color:#f59e0b" />
                            </div>
                            <div>
                                <p class="kpi-label">Pending Approvals</p>
                                <p class="kpi-val">{{ ov.pendingVendors }}</p>
                                <p class="kpi-sub">awaiting review</p>
                            </div>
                        </div>
                    </div>

                    <!-- Health + Approval rate -->
                    <div class="two-col-grid">

                        <!-- Health score card -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <ShieldCheckIcon class="card-title-icon" style="color:#6366f1" />
                                    <div>
                                        <h3 class="card-title">Platform Health Score</h3>
                                        <p class="card-subtitle">Composite metric across vendor & customer data</p>
                                    </div>
                                </div>
                            </div>
                            <div class="health-body">
                                <div class="health-score-row">
                                    <span class="health-num" :style="{ color: healthColor }">{{ ov.healthScore }}</span>
                                    <span class="health-den">/100</span>
                                    <span class="health-badge" :style="{ background: healthColor + '1a', color: healthColor }">{{ healthLabel }}</span>
                                </div>
                                <div class="health-track">
                                    <div class="health-fill" :style="{ width: ov.healthScore + '%', background: healthColor }"></div>
                                </div>
                                <div class="health-rows">
                                    <div class="hrow">
                                        <span class="hrow-label">Active vendor ratio</span>
                                        <span class="hrow-val">{{ ov.approvalRate }}%</span>
                                    </div>
                                    <div class="hrow">
                                        <span class="hrow-label">Pending queue</span>
                                        <span class="hrow-val" :style="{ color: ov.pendingVendors > 0 ? '#f59e0b' : '#10b981' }">
                                            {{ ov.pendingVendors }} vendor{{ ov.pendingVendors !== 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    <div class="hrow">
                                        <span class="hrow-label">New vendors this month</span>
                                        <span class="hrow-val">{{ ov.newVendorsThisMonth }}</span>
                                    </div>
                                    <div class="hrow">
                                        <span class="hrow-label">New customers this month</span>
                                        <span class="hrow-val">{{ ov.newCustomersThisMonth }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Approval rate donut -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <CheckCircleIcon class="card-title-icon" style="color:#10b981" />
                                    <div>
                                        <h3 class="card-title">Vendor Approval Rate</h3>
                                        <p class="card-subtitle">Active vs total registered vendors</p>
                                    </div>
                                </div>
                            </div>
                            <div class="donut-body">
                                <svg width="120" height="120" viewBox="0 0 112 112">
                                    <circle cx="56" cy="56" :r="DONUT_R" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                                    <circle cx="56" cy="56" :r="DONUT_R" fill="none" stroke="#10b981" stroke-width="14"
                                        stroke-linecap="round"
                                        :stroke-dasharray="approvalDash"
                                        :stroke-dashoffset="CIRC * 0.25"
                                    />
                                    <text x="56" y="51" text-anchor="middle" font-size="17" font-weight="700" fill="#0f172a" font-family="inherit">{{ ov.approvalRate }}%</text>
                                    <text x="56" y="65" text-anchor="middle" font-size="9" fill="#94a3b8" font-family="inherit">approved</text>
                                </svg>
                                <div class="donut-legend">
                                    <div class="dl-row">
                                        <span class="dl-dot" style="background:#10b981"></span>
                                        <span class="dl-label">Active</span>
                                        <span class="dl-val">{{ ov.activeVendors }}</span>
                                    </div>
                                    <div class="dl-row">
                                        <span class="dl-dot" style="background:#fef3c7;border:1.5px solid #f59e0b"></span>
                                        <span class="dl-label">Pending</span>
                                        <span class="dl-val">{{ ov.pendingVendors }}</span>
                                    </div>
                                    <div class="dl-row">
                                        <span class="dl-dot" style="background:#e2e8f0"></span>
                                        <span class="dl-label">Total</span>
                                        <span class="dl-val">{{ ov.totalVendors }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>

                <!-- ══════════════════════════════════════════════════
                     TAB: VENDOR PERFORMANCE
                ══════════════════════════════════════════════════ -->
                <template v-if="activeTab === 'vendors'">
                    <div class="two-col-grid">

                        <!-- Monthly registrations chart -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <ArrowTrendingUpIcon class="card-title-icon" style="color:#6366f1" />
                                    <div>
                                        <h3 class="card-title">Vendor Registrations</h3>
                                        <p class="card-subtitle">Monthly new vendor signups — last 6 months</p>
                                    </div>
                                </div>
                            </div>
                            <div class="chart-wrap">
                                <svg :viewBox="`0 0 ${CW} ${CH}`" class="trend-svg" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="gv" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#6366f1" stop-opacity="0.2"/>
                                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <line v-for="n in 4" :key="n"
                                        :x1="PL" :x2="CW-PR"
                                        :y1="PT + ((CH-PT-PB)/3)*(n-1)"
                                        :y2="PT + ((CH-PT-PB)/3)*(n-1)"
                                        stroke="#f1f5f9" stroke-width="1"
                                    />
                                    <path :d="areaPath(vendorPts)" fill="url(#gv)" />
                                    <polyline :points="polyline(vendorPts)" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                                    <circle v-for="p in vendorPts" :key="p.x" :cx="p.x" :cy="p.y" r="4" fill="white" stroke="#6366f1" stroke-width="2"/>
                                    <text v-for="l in xLabels(vendorPts)" :key="l.label" :x="l.x" :y="CH-4" text-anchor="middle" font-size="10" fill="#94a3b8" font-family="inherit">{{ l.label }}</text>
                                </svg>
                            </div>
                            <!-- counts row -->
                            <div class="chart-counts">
                                <div v-for="p in vendorPts" :key="p.month" class="cc-item">
                                    <span class="cc-val">{{ p.count }}</span>
                                    <span class="cc-label">{{ p.month.split(' ')[0] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Approval funnel -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <ChartBarIcon class="card-title-icon" style="color:#10b981" />
                                    <div>
                                        <h3 class="card-title">Approval Funnel</h3>
                                        <p class="card-subtitle">Vendor registration to approval breakdown</p>
                                    </div>
                                </div>
                            </div>
                            <div class="funnel-body">
                                <div v-for="(step, i) in vp.approvalFunnel" :key="step.label" class="funnel-row">
                                    <span class="funnel-label">{{ step.label }}</span>
                                    <div class="funnel-track">
                                        <div
                                            class="funnel-fill"
                                            :style="{
                                                width: ((step.value / funnelMax) * 100) + '%',
                                                background: i === 0 ? '#6366f1' : i === 1 ? '#10b981' : '#f59e0b'
                                            }"
                                        ></div>
                                    </div>
                                    <span class="funnel-val">{{ step.value }}</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Top vendors table -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <StarIcon class="card-title-icon" style="color:#f59e0b" />
                                <div>
                                    <h3 class="card-title">Top Active Vendors</h3>
                                    <p class="card-subtitle">Longest-running approved vendors on the platform</p>
                                </div>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Vendor</th>
                                        <th>Domain</th>
                                        <th>Joined</th>
                                        <th>Days Active</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(v, i) in vp.topVendors" :key="v.id">
                                        <td class="td-rank">{{ i + 1 }}</td>
                                        <td>
                                            <div class="td-name-cell">
                                                <div class="avatar avatar-indigo">{{ initials(v.name) }}</div>
                                                <span class="td-name">{{ v.name }}</span>
                                            </div>
                                        </td>
                                        <td class="td-mono">{{ v.domain ?? '—' }}</td>
                                        <td>{{ formatDate(v.created_at) }}</td>
                                        <td>
                                            <span class="days-badge">{{ v.days_active }}d</span>
                                        </td>
                                    </tr>
                                    <tr v-if="vp.topVendors.length === 0">
                                        <td colspan="5" class="td-empty">No active vendors yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- ══════════════════════════════════════════════════
                     TAB: CUSTOMER ACTIVITY
                ══════════════════════════════════════════════════ -->
                <template v-if="activeTab === 'customers'">
                    <div class="two-col-grid">

                        <!-- Monthly signups chart -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <ArrowTrendingUpIcon class="card-title-icon" style="color:#3b82f6" />
                                    <div>
                                        <h3 class="card-title">Customer Signups</h3>
                                        <p class="card-subtitle">Monthly new customers — last 6 months</p>
                                    </div>
                                </div>
                            </div>
                            <div class="chart-wrap">
                                <svg :viewBox="`0 0 ${CW} ${CH}`" class="trend-svg" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="gc" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.2"/>
                                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <line v-for="n in 4" :key="n"
                                        :x1="PL" :x2="CW-PR"
                                        :y1="PT + ((CH-PT-PB)/3)*(n-1)"
                                        :y2="PT + ((CH-PT-PB)/3)*(n-1)"
                                        stroke="#f1f5f9" stroke-width="1"
                                    />
                                    <path :d="areaPath(customerPts)" fill="url(#gc)" />
                                    <polyline :points="polyline(customerPts)" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                                    <circle v-for="p in customerPts" :key="p.x" :cx="p.x" :cy="p.y" r="4" fill="white" stroke="#3b82f6" stroke-width="2"/>
                                    <text v-for="l in xLabels(customerPts)" :key="l.label" :x="l.x" :y="CH-4" text-anchor="middle" font-size="10" fill="#94a3b8" font-family="inherit">{{ l.label }}</text>
                                </svg>
                            </div>
                            <div class="chart-counts">
                                <div v-for="p in customerPts" :key="p.month" class="cc-item">
                                    <span class="cc-val">{{ p.count }}</span>
                                    <span class="cc-label">{{ p.month.split(' ')[0] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Verification donut -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title-group">
                                    <ShieldCheckIcon class="card-title-icon" style="color:#3b82f6" />
                                    <div>
                                        <h3 class="card-title">Email Verification</h3>
                                        <p class="card-subtitle">Verified vs unverified customers</p>
                                    </div>
                                </div>
                            </div>
                            <div class="donut-body">
                                <svg width="120" height="120" viewBox="0 0 112 112">
                                    <circle cx="56" cy="56" :r="DONUT_R" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                                    <circle cx="56" cy="56" :r="DONUT_R" fill="none" stroke="#3b82f6" stroke-width="14"
                                        stroke-linecap="round"
                                        :stroke-dasharray="verifyDash"
                                        :stroke-dashoffset="CIRC * 0.25"
                                    />
                                    <text x="56" y="51" text-anchor="middle" font-size="17" font-weight="700" fill="#0f172a" font-family="inherit">{{ ca.verificationRate }}%</text>
                                    <text x="56" y="65" text-anchor="middle" font-size="9" fill="#94a3b8" font-family="inherit">verified</text>
                                </svg>
                                <div class="donut-legend">
                                    <div class="dl-row">
                                        <span class="dl-dot" style="background:#3b82f6"></span>
                                        <span class="dl-label">Verified</span>
                                        <span class="dl-val">{{ ca.verified }}</span>
                                    </div>
                                    <div class="dl-row">
                                        <span class="dl-dot" style="background:#e2e8f0"></span>
                                        <span class="dl-label">Unverified</span>
                                        <span class="dl-val">{{ ca.unverified }}</span>
                                    </div>
                                    <div class="dl-row">
                                        <span class="dl-dot" style="background:#0f172a"></span>
                                        <span class="dl-label">Total</span>
                                        <span class="dl-val">{{ ca.totalCustomers }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent signups table -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <UsersIcon class="card-title-icon" style="color:#3b82f6" />
                                <div>
                                    <h3 class="card-title">Recent Customer Signups</h3>
                                    <p class="card-subtitle">Latest 6 registered customers</p>
                                </div>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Verified</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="c in ca.recentSignups" :key="c.id">
                                        <td>
                                            <div class="td-name-cell">
                                                <div class="avatar avatar-blue">{{ initials(c.name) }}</div>
                                                <span class="td-name">{{ c.name }}</span>
                                            </div>
                                        </td>
                                        <td class="td-mono">{{ c.email }}</td>
                                        <td>
                                            <span class="status-pill" :class="c.verified ? 'pill-active' : 'pill-pending'">
                                                {{ c.verified ? 'Verified' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(c.created_at) }}</td>
                                    </tr>
                                    <tr v-if="ca.recentSignups.length === 0">
                                        <td colspan="4" class="td-empty">No customers yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- ══════════════════════════════════════════════════
                     TAB: CATEGORY BREAKDOWN
                ══════════════════════════════════════════════════ -->
                <template v-if="activeTab === 'categories'">

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <Squares2X2Icon class="card-title-icon" style="color:#6366f1" />
                                <div>
                                    <h3 class="card-title">Category Distribution</h3>
                                    <p class="card-subtitle">How often each category appears across all tenant stores — {{ cb.totalUnique }} unique categories total</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="cb.breakdown.length === 0" class="empty-state">
                            <Squares2X2Icon class="empty-icon" />
                            <p>No category data yet. Approve vendors and run the category seeder.</p>
                        </div>

                        <div v-else class="cat-breakdown-body">
                            <!-- Horizontal bar chart -->
                            <div class="cat-bars">
                                <div
                                    v-for="cat in cb.breakdown"
                                    :key="cat.name"
                                    class="cat-bar-row"
                                >
                                    <span class="cat-bar-label">{{ cat.name }}</span>
                                    <div class="cat-bar-track">
                                        <div
                                            class="cat-bar-fill"
                                            :style="{ width: cat.pct + '%', background: cat.color }"
                                        ></div>
                                    </div>
                                    <span class="cat-bar-pct">{{ cat.count }} store{{ cat.count !== 1 ? 's' : '' }}</span>
                                </div>
                            </div>

                            <!-- Legend chips -->
                            <div class="cat-legend">
                                <div
                                    v-for="cat in cb.breakdown"
                                    :key="cat.name"
                                    class="cat-chip"
                                    :style="{ background: cat.color + '18', border: `1px solid ${cat.color}40`, color: cat.color }"
                                >
                                    <span class="cat-chip-dot" :style="{ background: cat.color }"></span>
                                    {{ cat.name }}
                                    <span class="cat-chip-pct">{{ cat.pct }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </main>
    </div>
</template>

<style scoped>
.page-container {
    padding: 2rem 2.5rem;
    background: #f1f5f9;
    min-height: 100vh;
    display: flex; flex-direction: column; gap: 1.5rem;
}

/* ── Header ── */
.page-header { display: flex; align-items: center; gap: 1rem; }
.page-header-icon {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    border-radius: 12px; padding: 0.75rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.header-icon { width: 28px; height: 28px; color: white; }
.page-header-text h1 { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 0 0 0.2rem; }
.page-header-text p  { color: #64748b; margin: 0; font-size: 0.9rem; }

/* ── Tab bar ── */
.tab-bar {
    display: flex; gap: 0.35rem;
    background: white; border: 1px solid #e2e8f0;
    border-radius: 12px; padding: 0.35rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    overflow-x: auto;
}
.tab-btn {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.5rem 1rem;
    border: none; border-radius: 9px;
    font-size: 0.83rem; font-weight: 600; color: #64748b;
    background: transparent; cursor: pointer;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.tab-btn:hover  { background: #f8fafc; color: #0f172a; }
.tab-btn.active { background: #0f172a; color: white; }
.tab-icon { width: 15px; height: 15px; }

/* ── Cards ── */
.card {
    background: white; border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    overflow: hidden; display: flex; flex-direction: column;
}
.card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.1rem 1.4rem 0.9rem;
    border-bottom: 1px solid #f1f5f9; gap: 0.75rem;
}
.card-title-group { display: flex; align-items: center; gap: 0.55rem; }
.card-title-icon  { width: 18px; height: 18px; flex-shrink: 0; }
.card-title    { font-size: 0.92rem; font-weight: 600; color: #0f172a; margin: 0; line-height: 1.2; }
.card-subtitle { font-size: 0.72rem; color: #94a3b8; margin: 0.1rem 0 0; }

/* ── KPI grid ── */
.kpi-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem;
}
.kpi-card {
    background: white; border-radius: 14px; padding: 1.25rem;
    border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    display: flex; align-items: center; gap: 1rem;
    transition: transform 0.15s, box-shadow 0.15s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.07); }
.kpi-icon-wrap {
    width: 46px; height: 46px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.kpi-icon  { width: 22px; height: 22px; }
.kpi-label { font-size: 0.72rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.2rem; }
.kpi-val   { font-size: 1.8rem; font-weight: 700; color: #0f172a; margin: 0; line-height: 1; }
.kpi-sub   { font-size: 0.72rem; color: #94a3b8; margin: 0.2rem 0 0; }

/* ── Two-col layout ── */
.two-col-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }

/* ── Health ── */
.health-body  { padding: 1.1rem 1.4rem 1.25rem; display: flex; flex-direction: column; gap: 0.85rem; }
.health-score-row { display: flex; align-items: baseline; gap: 0.3rem; }
.health-num   { font-size: 2.2rem; font-weight: 800; line-height: 1; }
.health-den   { font-size: 0.88rem; color: #94a3b8; }
.health-badge { margin-left: auto; font-size: 0.7rem; font-weight: 600; padding: 0.18rem 0.55rem; border-radius: 99px; }
.health-track { height: 6px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.health-fill  { height: 100%; border-radius: 99px; transition: width 0.6s cubic-bezier(.4,0,.2,1); }
.health-rows  { display: flex; flex-direction: column; gap: 0.4rem; }
.hrow { display: flex; justify-content: space-between; }
.hrow-label { font-size: 0.74rem; color: #64748b; }
.hrow-val   { font-size: 0.77rem; font-weight: 600; color: #0f172a; }

/* ── Donut ── */
.donut-body   { display: flex; align-items: center; gap: 1.5rem; padding: 1.1rem 1.4rem 1.25rem; }
.donut-legend { display: flex; flex-direction: column; gap: 0.55rem; }
.dl-row   { display: flex; align-items: center; gap: 0.5rem; }
.dl-dot   { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.dl-label { font-size: 0.78rem; color: #64748b; flex: 1; }
.dl-val   { font-size: 0.85rem; font-weight: 700; color: #0f172a; }

/* ── Chart ── */
.chart-wrap { padding: 1rem 1.4rem 0; }
.trend-svg  { width: 100%; height: 140px; display: block; overflow: visible; }
.chart-counts {
    display: flex; padding: 0.5rem 1.4rem 1rem;
    border-top: 1px solid #f8fafc;
}
.cc-item  { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.1rem; }
.cc-val   { font-size: 0.82rem; font-weight: 700; color: #0f172a; }
.cc-label { font-size: 0.67rem; color: #94a3b8; }

/* ── Funnel ── */
.funnel-body { padding: 1.25rem 1.4rem; display: flex; flex-direction: column; gap: 1rem; }
.funnel-row  { display: flex; align-items: center; gap: 0.75rem; }
.funnel-label { font-size: 0.8rem; font-weight: 600; color: #475569; width: 90px; flex-shrink: 0; }
.funnel-track { flex: 1; height: 10px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.funnel-fill  { height: 100%; border-radius: 99px; transition: width 0.6s ease; }
.funnel-val   { font-size: 0.82rem; font-weight: 700; color: #0f172a; width: 28px; text-align: right; flex-shrink: 0; }

/* ── Table ── */
.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
.data-table thead tr { border-bottom: 1px solid #f1f5f9; }
.data-table th { padding: 0.7rem 1.4rem; text-align: left; font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
.data-table td { padding: 0.75rem 1.4rem; border-bottom: 1px solid #f8fafc; color: #334155; vertical-align: middle; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover td { background: #fafbff; }
.td-rank { font-weight: 700; color: #94a3b8; width: 32px; }
.td-mono { font-family: ui-monospace, monospace; font-size: 0.78rem; color: #64748b; }
.td-empty { text-align: center; color: #94a3b8; padding: 2rem !important; }
.td-name-cell { display: flex; align-items: center; gap: 0.6rem; }
.td-name { font-weight: 600; color: #0f172a; }
.days-badge { background: #f1f5f9; color: #475569; font-size: 0.72rem; font-weight: 600; padding: 0.18rem 0.55rem; border-radius: 99px; }

/* ── Avatars ── */
.avatar { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.67rem; font-weight: 700; flex-shrink: 0; }
.avatar-indigo { background: #ede9fe; color: #5b21b6; }
.avatar-blue   { background: #dbeafe; color: #1e40af; }

/* Status pills */
.status-pill { font-size: 0.67rem; font-weight: 600; padding: 0.18rem 0.5rem; border-radius: 99px; }
.pill-active  { background: #d1fae5; color: #065f46; }
.pill-pending { background: #fef3c7; color: #92400e; }

/* ── Category breakdown ── */
.cat-breakdown-body { padding: 1.25rem 1.4rem; display: flex; flex-direction: column; gap: 1.5rem; }
.cat-bars { display: flex; flex-direction: column; gap: 0.75rem; }
.cat-bar-row { display: flex; align-items: center; gap: 0.75rem; }
.cat-bar-label { font-size: 0.8rem; font-weight: 600; color: #475569; width: 160px; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cat-bar-track { flex: 1; height: 10px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.cat-bar-fill  { height: 100%; border-radius: 99px; transition: width 0.6s ease; }
.cat-bar-pct   { font-size: 0.78rem; font-weight: 600; color: #64748b; width: 64px; text-align: right; flex-shrink: 0; }

.cat-legend { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.cat-chip {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.75rem; border-radius: 99px;
    font-size: 0.77rem; font-weight: 600;
}
.cat-chip-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.cat-chip-pct { opacity: 0.7; font-weight: 500; }

/* Empty */
.empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem; padding: 4rem 1rem; }
.empty-icon  { width: 40px; height: 40px; color: #e2e8f0; }
.empty-state p { font-size: 0.88rem; color: #94a3b8; margin: 0; text-align: center; }

/* ══════════════════
   RESPONSIVE
══════════════════ */
@media (max-width: 1024px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .page-container { padding: 1rem; gap: 1.25rem; }
    .page-header-text h1 { font-size: 1.4rem; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .two-col-grid { grid-template-columns: 1fr; }
    .tab-btn span, .tab-btn .tab-icon + * { }
    .cat-bar-label { width: 110px; }
    .data-table th, .data-table td { padding: 0.65rem 1rem; }
}

@media (max-width: 480px) {
    .kpi-grid { grid-template-columns: 1fr 1fr; gap: 0.6rem; }
    .kpi-card { padding: 1rem; gap: 0.75rem; }
    .kpi-val  { font-size: 1.5rem; }
    .tab-btn  { padding: 0.45rem 0.7rem; font-size: 0.78rem; }
    .cat-bar-label { width: 90px; font-size: 0.73rem; }
}
</style>