<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import AdminNav from '@/components/navigation/AdminNav.vue';
import { useSidebar } from '@/composables/useSidebar';
import ConfirmationModal from '@/components/ui/modal/ConfirmationModal.vue';
import {
    BuildingStorefrontIcon,
    CheckCircleIcon,
    ClockIcon,
    UsersIcon,
    ChartBarIcon,
    ArrowRightIcon,
    UserPlusIcon,
    ArrowTrendingUpIcon,
    ArrowUpIcon,
} from '@heroicons/vue/24/outline';

/* ------------------------------------------------------------------ */
/*  Props                                                               */
/* ------------------------------------------------------------------ */
interface GrowthPoint { month: string; count: number }
interface VendorItem {
    id: string; name: string; email: string;
    is_approved: boolean; domain: string | null; created_at: string
}
interface CustomerItem {
    id: string; name: string; email: string; created_at: string
}
interface PendingVendor {
    id: string; name: string; email: string;
    domain: string | null; created_at: string
}

interface Props {
    vendorStats: { pending: number; active: number; total: number };
    customerCount: number;
    vendorGrowth: GrowthPoint[];
    customerGrowth: GrowthPoint[];
    recentVendors: VendorItem[];
    recentCustomers: CustomerItem[];
    pendingVendors: PendingVendor[];
    healthScore: number;
    healthComponents: {
        activeVendorRatio: number;
        emailVerificationRate: number;
        newCustomersScore: number;
        newVendorsScore: number;
    };
}

const props = defineProps<Props>();

/* ------------------------------------------------------------------ */
/*  Sidebar                                                             */
/* ------------------------------------------------------------------ */
const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

const showApproveModal = ref(false);
const vendorToApprove = ref<PendingVendor | null>(null);
const approvingId = ref<string | null>(null);
const approveProcessing = ref(false);

function openApproveModal(id: string) {
    const vendor = props.pendingVendors.find((v) => v.id === id);
    if (!vendor) {
        return;
    }

    vendorToApprove.value = vendor;
    showApproveModal.value = true;
}

function confirmApproveVendor() {
    if (!vendorToApprove.value) {
        return;
    }

    approvingId.value = vendorToApprove.value.id;
    approveProcessing.value = true;

    router.post(`/admin/vendors/${vendorToApprove.value.id}/approve`, {}, {
        onFinish: () => {
            approvingId.value = null;
            approveProcessing.value = false;
            showApproveModal.value = false;
            vendorToApprove.value = null;
        },
    });
}

/* ------------------------------------------------------------------ */
/*  SVG Line/Area chart — combined vendor + customer trend             */
/* ------------------------------------------------------------------ */
const CHART_W = 560;
const CHART_H = 160;
const PAD_TOP = 16; const PAD_RIGHT = 16; const PAD_BOTTOM = 28; const PAD_LEFT = 32;

function toPoints(data: GrowthPoint[]) {
    const counts = data.map(d => d.count);
    const max    = Math.max(...counts, 1);
    const innerW = CHART_W - PAD_LEFT - PAD_RIGHT;
    const innerH = CHART_H - PAD_TOP  - PAD_BOTTOM;
    return data.map((d, i) => ({
        x: PAD_LEFT + (i / (data.length - 1 || 1)) * innerW,
        y: PAD_TOP  + innerH - (d.count / max) * innerH,
        count: d.count,
        month: d.month,
    }));
}

function polyline(pts: { x: number; y: number }[]) {
    return pts.map(p => `${p.x},${p.y}`).join(' ');
}

function areaPath(pts: { x: number; y: number }[]) {
    if (!pts.length) return '';
    const bottom = CHART_H - PAD_BOTTOM;
    const first  = pts[0];
    const last   = pts[pts.length - 1];
    return `M${first.x},${bottom} ` +
           pts.map(p => `L${p.x},${p.y}`).join(' ') +
           ` L${last.x},${bottom} Z`;
}

const vendorPts   = computed(() => toPoints(props.vendorGrowth));
const customerPts = computed(() => toPoints(props.customerGrowth));

const xLabels = computed(() =>
    props.vendorGrowth.map((d, i) => ({
        x: vendorPts.value[i]?.x ?? 0,
        label: d.month.split(' ')[0],
    }))
);

/* grid line Y positions */
const gridLines = computed(() => {
    const innerH = CHART_H - PAD_TOP - PAD_BOTTOM;
    return [0, 1, 2, 3].map(n => PAD_TOP + (innerH / 3) * n);
});

/* ------------------------------------------------------------------ */
/*  Approval Rate donut                                                 */
/* ------------------------------------------------------------------ */
const DONUT_R      = 44;
const DONUT_CX     = 56;
const DONUT_CY     = 56;
const DONUT_STROKE = 14;
const CIRC = 2 * Math.PI * DONUT_R;

const approvalRate = computed(() => {
    const total = props.vendorStats.total;
    if (!total) return 0;
    return Math.round((props.vendorStats.active / total) * 100);
});

const donutDash = computed(() => {
    const filled = (approvalRate.value / 100) * CIRC;
    return `${filled} ${CIRC - filled}`;
});

/* ------------------------------------------------------------------ */
/*  Platform health score — computed server-side via PlatformHealthService
/*  Formula: (activeVendorRatio×40) + (emailVerificationRate×30)
/*           + (newCustomersScore×20) + (newVendorsScore×10)          */
/* ------------------------------------------------------------------ */
const healthLabel = computed(() => {
    if (props.healthScore >= 80) return { text: 'Excellent',       color: '#10b981' };
    if (props.healthScore >= 60) return { text: 'Good',            color: '#3b82f6' };
    if (props.healthScore >= 40) return { text: 'Fair',            color: '#f59e0b' };
    return                              { text: 'Needs Attention', color: '#ef4444' };
});

/* ------------------------------------------------------------------ */
/*  MoM growth delta                                                    */
/* ------------------------------------------------------------------ */
function growthDelta(data: GrowthPoint[]) {
    if (data.length < 2) return null;
    const last = data[data.length - 1].count;
    const prev = data[data.length - 2].count;
    if (!prev) return null;
    return Math.round(((last - prev) / prev) * 100);
}

const vendorDelta   = computed(() => growthDelta(props.vendorGrowth));
const customerDelta = computed(() => growthDelta(props.customerGrowth));

/* ------------------------------------------------------------------ */
/*  Helpers                                                             */
/* ------------------------------------------------------------------ */
function formatDate(dt: string | null) {
    if (!dt) return '—';
    return new Date(dt).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}

function initials(name: string) {
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="admin">
            <AdminNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="page-container">

                <!-- ── Page Header ── -->
                <div class="page-header">
                    <div class="page-header-icon">
                        <ChartBarIcon class="header-icon" />
                    </div>
                    <div>
                        <h1>Admin Dashboard</h1>
                        <p>Platform overview and management</p>
                    </div>
                </div>

                <!-- ── Stat Cards with action buttons ── -->
                <div class="stats-grid">

                    <div class="stat-card stat-total">
                        <div class="stat-icon-wrapper">
                            <BuildingStorefrontIcon class="stat-icon" />
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Total Vendors</p>
                            <h2 class="stat-value">{{ vendorStats.total }}</h2>
                        </div>
                        <a href="/admin/vendors" class="stat-action stat-action-green">
                            Manage <ArrowRightIcon class="stat-action-icon" />
                        </a>
                    </div>

                    <div class="stat-card stat-active">
                        <div class="stat-icon-wrapper">
                            <CheckCircleIcon class="stat-icon" />
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Active Vendors</p>
                            <h2 class="stat-value">{{ vendorStats.active }}</h2>
                        </div>
                        <a href="/admin/vendors" class="stat-action stat-action-green">
                            View <ArrowRightIcon class="stat-action-icon" />
                        </a>
                    </div>

                    <div class="stat-card stat-pending">
                        <div class="stat-icon-wrapper">
                            <ClockIcon class="stat-icon" />
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Pending Approval</p>
                            <h2 class="stat-value">{{ vendorStats.pending }}</h2>
                        </div>
                        <a href="/admin/vendors?filter=pending" class="stat-action stat-action-amber">
                            Review <ArrowRightIcon class="stat-action-icon" />
                        </a>
                    </div>

                    <div class="stat-card stat-customers">
                        <div class="stat-icon-wrapper">
                            <UsersIcon class="stat-icon" />
                        </div>
                        <div class="stat-content">
                            <p class="stat-label">Total Customers</p>
                            <h2 class="stat-value">{{ customerCount }}</h2>
                        </div>
                        <a href="/admin/customers" class="stat-action stat-action-blue">
                            View <ArrowRightIcon class="stat-action-icon" />
                        </a>
                    </div>

                </div>

                <!-- ── Analytics Row ── -->
                <div class="analytics-grid">

                    <!-- Combined SVG trend chart (spans both rows) -->
                    <div class="card chart-card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <ArrowTrendingUpIcon class="card-title-icon" style="color:#6366f1" />
                                <div>
                                    <h3 class="card-title">Platform Growth</h3>
                                    <p class="card-subtitle">Vendors vs Customers — last 6 months</p>
                                </div>
                            </div>
                            <div class="chart-legend">
                                <span class="legend-dot" style="background:#10b981"></span>
                                <span class="legend-label">Vendors</span>
                                <span class="legend-dot" style="background:#3b82f6; margin-left:0.5rem"></span>
                                <span class="legend-label">Customers</span>
                            </div>
                        </div>

                        <div class="chart-wrap">
                            <svg :viewBox="`0 0 ${CHART_W} ${CHART_H}`" class="trend-svg" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="gVendor" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%"   stop-color="#10b981" stop-opacity="0.2"/>
                                        <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
                                    </linearGradient>
                                    <linearGradient id="gCustomer" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%"   stop-color="#3b82f6" stop-opacity="0.15"/>
                                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>

                                <!-- Grid lines -->
                                <line
                                    v-for="(y, i) in gridLines" :key="i"
                                    :x1="PAD_LEFT" :x2="CHART_W - PAD_RIGHT"
                                    :y1="y" :y2="y"
                                    stroke="var(--border)" stroke-width="1"
                                />

                                <!-- Area fills -->
                                <path :d="areaPath(customerPts)" fill="url(#gCustomer)" />
                                <path :d="areaPath(vendorPts)"   fill="url(#gVendor)" />

                                <!-- Lines – customer first so it stays on top -->
                                <polyline :points="polyline(customerPts)" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>
                                <polyline :points="polyline(vendorPts)"   fill="none" stroke="#10b981" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>

                                <!-- Dots -->
                                <circle v-for="p in vendorPts"   :key="`v${p.x}`" :cx="p.x" :cy="p.y" r="4" fill="var(--card)" stroke="#10b981" stroke-width="2"/>
                                <circle v-for="p in customerPts" :key="`c${p.x}`" :cx="p.x" :cy="p.y" r="4" fill="var(--card)" stroke="#3b82f6" stroke-width="2"/>

                                <!-- X-axis labels -->
                                <text
                                    v-for="l in xLabels" :key="l.label"
                                    :x="l.x" :y="CHART_H - 4"
                                    text-anchor="middle" font-size="10" fill="var(--muted-foreground)" font-family="inherit"
                                >{{ l.label }}</text>
                            </svg>
                        </div>

                        <!-- MoM stat footer -->
                        <div class="chart-footer">
                            <div class="delta-item">
                                <span class="delta-label">Vendor MoM</span>
                                <span
                                    v-if="vendorDelta !== null"
                                    class="delta-value"
                                    :class="vendorDelta >= 0 ? 'delta-up' : 'delta-down'"
                                >
                                    <ArrowUpIcon v-if="vendorDelta >= 0" class="delta-arrow" />
                                    {{ Math.abs(vendorDelta) }}%
                                </span>
                                <span v-else class="delta-value delta-neutral">—</span>
                            </div>
                            <div class="delta-divider"></div>
                            <div class="delta-item">
                                <span class="delta-label">Customer MoM</span>
                                <span
                                    v-if="customerDelta !== null"
                                    class="delta-value"
                                    :class="customerDelta >= 0 ? 'delta-up' : 'delta-down'"
                                >
                                    <ArrowUpIcon v-if="customerDelta >= 0" class="delta-arrow" />
                                    {{ Math.abs(customerDelta) }}%
                                </span>
                                <span v-else class="delta-value delta-neutral">—</span>
                            </div>
                            <div class="delta-divider"></div>
                            <div class="delta-item">
                                <span class="delta-label">This month vendors</span>
                                <span class="delta-value delta-neutral">{{ vendorGrowth.at(-1)?.count ?? 0 }}</span>
                            </div>
                            <div class="delta-divider"></div>
                            <div class="delta-item">
                                <span class="delta-label">This month customers</span>
                                <span class="delta-value delta-neutral">{{ customerGrowth.at(-1)?.count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Rate Donut (first row) -->
                    <div class="card donut-card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <CheckCircleIcon class="card-title-icon" style="color:#10b981" />
                                <div>
                                    <h3 class="card-title">Approval Rate</h3>
                                    <p class="card-subtitle">Active vs total vendors</p>
                                </div>
                            </div>
                        </div>
                        <div class="donut-body">
                            <svg width="112" height="112" viewBox="0 0 112 112">
                                <circle :cx="DONUT_CX" :cy="DONUT_CY" :r="DONUT_R"
                                    fill="none" stroke="var(--border)" :stroke-width="DONUT_STROKE" />
                                <circle :cx="DONUT_CX" :cy="DONUT_CY" :r="DONUT_R"
                                    fill="none" stroke="#10b981" :stroke-width="DONUT_STROKE"
                                    stroke-linecap="round"
                                    :stroke-dasharray="donutDash"
                                    :stroke-dashoffset="CIRC * 0.25"
                                    style="transition: stroke-dasharray 0.6s ease"
                                />
                                <text x="56" y="52" text-anchor="middle" font-size="18" font-weight="700" fill="var(--foreground)" font-family="inherit">{{ approvalRate }}%</text>
                                <text x="56" y="66" text-anchor="middle" font-size="9"  fill="var(--muted-foreground)"   font-family="inherit">approved</text>
                            </svg>
                            <div class="donut-legend">
                                <div class="donut-stat">
                                    <span class="ds-dot" style="background:#10b981"></span>
                                    <span class="ds-label">Active</span>
                                    <span class="ds-val">{{ vendorStats.active }}</span>
                                </div>
                                <div class="donut-stat">
                                    <span class="ds-dot ds-dot-outline"></span>
                                    <span class="ds-label">Pending</span>
                                    <span class="ds-val">{{ vendorStats.pending }}</span>
                                </div>
                                <div class="donut-stat">
                                    <span class="ds-dot" style="background:var(--border)"></span>
                                    <span class="ds-label">Total</span>
                                    <span class="ds-val">{{ vendorStats.total }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Health (second row) -->
                    <div class="card health-card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <ChartBarIcon class="card-title-icon" style="color:#6366f1" />
                                <div>
                                    <h3 class="card-title">Platform Health</h3>
                                    <p class="card-subtitle">Overall score</p>
                                </div>
                            </div>
                            <!-- Tooltip explaining the score -->
                            <span class="info-tooltip" title="Active vendor ratio (40%) + Email verification rate (30%) + New customers this month (20%) + New vendors this month (10%)">ⓘ</span>
                        </div>
                        <div class="health-body">
                            <div class="health-score-row">
                                <span class="health-score-num" :style="{ color: healthLabel.color }">{{ props.healthScore }}</span>
                                <span class="health-score-den">/100</span>
                                <span class="health-badge" :style="{ background: healthLabel.color + '25', color: healthLabel.color }">
                                    {{ healthLabel.text }}
                                </span>
                            </div>
                            <div class="health-bar-track" style="background: var(--accent)">
                                <div class="health-bar-fill" :style="{ width: props.healthScore + '%', background: healthLabel.color }"></div>
                            </div>
                            <div class="health-metrics">
                                <div class="hm-row">
                                    <span class="hm-label">Active vendor ratio</span>
                                    <span class="hm-val">{{ healthComponents.activeVendorRatio }}%</span>
                                </div>
                                <div class="hm-row">
                                    <span class="hm-label">Email verification rate</span>
                                    <span class="hm-val">{{ healthComponents.emailVerificationRate }}%</span>
                                </div>
                                <div class="hm-row">
                                    <span class="hm-label">New customers this month</span>
                                    <span class="hm-val">{{ healthComponents.newCustomersScore }}%</span>
                                </div>
                                <div class="hm-row">
                                    <span class="hm-label">New vendors this month</span>
                                    <span class="hm-val">{{ healthComponents.newVendorsScore }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ── Bottom row: lists ── -->
                <div class="three-col-grid">

                    <!-- Pending Vendor Approvals -->
                    <div class="card pending-card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <ClockIcon class="card-title-icon" style="color:#f59e0b" />
                                <h3 class="card-title">Pending Approvals</h3>
                            </div>
                            <span v-if="vendorStats.pending > 0" class="badge badge-amber">{{ vendorStats.pending }}</span>
                        </div>

                        <div v-if="pendingVendors.length === 0" class="empty-state">
                            <CheckCircleIcon class="empty-icon" />
                            <p>All vendors approved!</p>
                        </div>
                        <ul v-else class="item-list">
                            <li v-for="v in pendingVendors" :key="v.id" class="item-row">
                                <div class="avatar avatar-amber">{{ initials(v.name) }}</div>
                                <div class="item-info">
                                    <p class="item-name">{{ v.name }}</p>
                                    <p class="item-meta">{{ v.email }}</p>
                                    <p class="item-meta item-date">{{ formatDate(v.created_at) }}</p>
                                </div>
                                <button class="approve-btn" :disabled="approvingId === v.id" @click="openApproveModal(v.id)">
                                    <span v-if="approvingId === v.id">…</span>
                                    <span v-else>Approve</span>
                                </button>
                            </li>
                        </ul>

                        <a href="/admin/vendors" class="card-footer-link">
                            View all vendors <ArrowRightIcon class="link-icon" />
                        </a>
                    </div>

                    <ConfirmationModal
                        v-model:open="showApproveModal"
                        title="Confirm Vendor Approval"
                        :description="vendorToApprove
                            ? `Approve ${vendorToApprove.name}? This will create their tenant database and enable vendor access.`
                            : 'Approve this vendor? This will create their tenant database and enable vendor access.'
                        "
                        confirm-text="Approve Vendor"
                        cancel-text="Cancel"
                        variant="destructive"
                        :loading="approveProcessing"
                        @confirm="confirmApproveVendor"
                    />

                    <!-- Recent Vendor Registrations -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <BuildingStorefrontIcon class="card-title-icon" style="color:#10b981" />
                                <h3 class="card-title">Recent Vendors</h3>
                            </div>
                        </div>

                        <div v-if="recentVendors.length === 0" class="empty-state">
                            <BuildingStorefrontIcon class="empty-icon" />
                            <p>No vendors yet</p>
                        </div>
                        <ul v-else class="item-list">
                            <li v-for="v in recentVendors" :key="v.id" class="item-row">
                                <div class="avatar" :class="v.is_approved ? 'avatar-green' : 'avatar-amber'">{{ initials(v.name) }}</div>
                                <div class="item-info">
                                    <p class="item-name">{{ v.name }}</p>
                                    <p class="item-meta">{{ v.domain ?? v.email }}</p>
                                    <p class="item-meta item-date">{{ formatDate(v.created_at) }}</p>
                                </div>
                                <span class="status-pill" :class="v.is_approved ? 'pill-active' : 'pill-pending'">
                                    {{ v.is_approved ? 'Active' : 'Pending' }}
                                </span>
                            </li>
                        </ul>

                        <a href="/admin/vendors" class="card-footer-link">
                            All vendors <ArrowRightIcon class="link-icon" />
                        </a>
                    </div>

                    <!-- Recent Customer Signups -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <UserPlusIcon class="card-title-icon" style="color:#3b82f6" />
                                <h3 class="card-title">Recent Customers</h3>
                            </div>
                        </div>

                        <div v-if="recentCustomers.length === 0" class="empty-state">
                            <UsersIcon class="empty-icon" />
                            <p>No customers yet</p>
                        </div>
                        <ul v-else class="item-list">
                            <li v-for="c in recentCustomers" :key="c.id" class="item-row">
                                <div class="avatar avatar-blue">{{ initials(c.name) }}</div>
                                <div class="item-info">
                                    <p class="item-name">{{ c.name }}</p>
                                    <p class="item-meta">{{ c.email }}</p>
                                    <p class="item-meta item-date">{{ formatDate(c.created_at) }}</p>
                                </div>
                            </li>
                        </ul>

                        <a href="/admin/customers" class="card-footer-link">
                            All customers <ArrowRightIcon class="link-icon" />
                        </a>
                    </div>

                </div>

            </div>
        </main>
    </div>
</template>

<style scoped>
/* ── Base ── */
.page-container {
    padding: 2rem 2.5rem;
    background: var(--background);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

/* ── Page Header ── */
.page-header { display: flex; align-items: center; gap: 1rem; }
.page-header-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 12px; padding: 0.75rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.header-icon { width: 28px; height: 28px; color: white; }
.page-header h1 { font-size: 1.75rem; font-weight: 700; color: var(--brand-green-dark); margin: 0 0 0.2rem; }
.page-header p  { color: var(--brand-muted); margin: 0; font-size: 0.9rem; }

/* ── Stat Cards ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}
.stat-card {
    background: var(--card);
    border-radius: 14px;
    padding: 1.4rem 1.4rem 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid var(--border);
    display: grid;
    grid-template-rows: auto auto;
    grid-template-columns: auto 1fr;
    grid-template-areas: "icon content" "action action";
    gap: 0 0.9rem;
    align-items: center;
    transition: transform 0.18s, box-shadow 0.18s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }

.stat-icon-wrapper {
    grid-area: icon;
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.stat-icon { width: 24px; height: 24px; color: white; }
.stat-total    .stat-icon-wrapper { background: linear-gradient(135deg,#134e3a,#2d8c65); }
.stat-active   .stat-icon-wrapper { background: linear-gradient(135deg,#10b981,#34d399); }
.stat-pending  .stat-icon-wrapper { background: linear-gradient(135deg,#f59e0b,#fbbf24); }
.stat-customers .stat-icon-wrapper { background: linear-gradient(135deg,#3b82f6,#60a5fa); }

.stat-content { grid-area: content; }
.stat-label {
    font-size: 0.73rem; font-weight: 600; color: var(--muted-foreground);
    text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.25rem;
}
.stat-value { font-size: 1.9rem; font-weight: 700; color: var(--foreground); margin: 0; line-height: 1; }

.stat-action {
    grid-area: action;
    display: inline-flex; align-items: center; gap: 0.3rem;
    margin-top: 0.65rem;
    padding: 0.38rem 0.75rem;
    border-radius: 8px;
    font-size: 0.77rem; font-weight: 600;
    text-decoration: none;
    border: 1px solid;
    transition: background 0.15s;
    width: fit-content;
}
.stat-action-green { background: color-mix(in srgb, #10b981 10%, transparent); color:#10b981; border-color: color-mix(in srgb, #10b981 20%, transparent); }
.stat-action-green:hover { background: color-mix(in srgb, #10b981 18%, transparent); }
.stat-action-amber { background: color-mix(in srgb, #f59e0b 10%, transparent); color:#f59e0b; border-color: color-mix(in srgb, #f59e0b 20%, transparent); }
.stat-action-amber:hover { background: color-mix(in srgb, #f59e0b 18%, transparent); }
.stat-action-blue  { background: color-mix(in srgb, #3b82f6 10%, transparent); color:#3b82f6; border-color: color-mix(in srgb, #3b82f6 20%, transparent); }
.stat-action-blue:hover  { background: color-mix(in srgb, #3b82f6 18%, transparent); }
.stat-action-icon { width: 13px; height: 13px; }

/* ── Cards base ── */
.card {
    background: var(--card);
    border-radius: 14px;
    border: 1px solid var(--border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.1rem 1.4rem 0.9rem;
    border-bottom: 1px solid var(--border);
    gap: 0.75rem; flex-wrap: wrap;
}
.card-title-group { display: flex; align-items: center; gap: 0.55rem; }
.card-title-icon  { width: 18px; height: 18px; flex-shrink: 0; }
.card-title    { font-size: 0.92rem; font-weight: 600; color: var(--foreground); margin: 0; line-height: 1.2; }
.card-subtitle { font-size: 0.72rem; color: var(--muted-foreground); margin: 0.1rem 0 0; }

.badge { font-size: 0.68rem; font-weight: 600; padding: 0.18rem 0.55rem; border-radius: 99px; }
.badge-green { background:#d1fae5; color:#065f46; }
.badge-blue  { background:#dbeafe; color:#1e40af; }
.badge-amber { background:#fef3c7; color:#92400e; }

/* ── Analytics Grid ── */
.analytics-grid {
    display: grid;
    grid-template-columns: 1fr 300px;   /* left flexible, right fixed width */
    grid-template-rows: auto auto;       /* two rows, height based on content */
    gap: 1.25rem;
    align-items: stretch;                /* make cards fill row height */
}

.chart-card {
    grid-row: span 2;                    /* occupy both rows */
    display: flex;
    flex-direction: column;
    height: 100%;                         /* fill the grid area */
}

/* Make header and footer stay fixed, chart area expand */
.chart-card .card-header {
    flex-shrink: 0;
}

.chart-wrap {
    flex: 1 1 auto;                       /* take remaining space */
    display: flex;
    align-items: center;                  /* vertically center SVG */
    justify-content: center;
    padding: 1rem 1.4rem 0;               /* original padding */
}

.trend-svg {
    width: 100%;
    height: 160px;                         /* fixed height – no distortion */
    display: block;
    overflow: visible;
}

.chart-footer {
    flex-shrink: 0;                        /* stay at bottom */
}

/* Right column cards */
.donut-card,
.health-card {
    height: 100%;                          /* fill row height */
    display: flex;
    flex-direction: column;
}

/* Chart legend */
.chart-legend { display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0; flex-wrap: wrap; }
.legend-dot   { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-label { font-size: 0.75rem; color: var(--muted-foreground); font-weight: 500; }

/* Footer deltas */
.chart-footer {
    display: flex; align-items: center;
    padding: 0.85rem 1.4rem;
    border-top: 1px solid var(--border);
    flex-wrap: wrap; gap: 0.5rem 0;
}
.delta-item    { display: flex; flex-direction: column; gap: 0.1rem; padding: 0 1rem; flex: 1; min-width: 90px; }
.delta-item:first-child { padding-left: 0; }
.delta-divider { width: 1px; height: 28px; background: #e2e8f0; flex-shrink: 0; }
.delta-label   { font-size: 0.67rem; color: var(--muted-foreground); font-weight: 500; white-space: nowrap; }
.delta-value   { font-size: 0.88rem; font-weight: 700; display: flex; align-items: center; gap: 0.15rem; }
.delta-up      { color: #10b981; }
.delta-down    { color: #ef4444; }
.delta-neutral { color: var(--foreground); }
.delta-arrow   { width: 12px; height: 12px; }

/* Donut */
.donut-body {
    display: flex; align-items: center; gap: 1.25rem;
    padding: 1rem 1.4rem 1.2rem;
    flex: 1;   /* take remaining space if needed */
}
.donut-legend { display: flex; flex-direction: column; gap: 0.5rem; }
.donut-stat   { display: flex; align-items: center; gap: 0.5rem; }
.ds-dot       { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.ds-dot-outline { background: #fef3c7; border: 1.5px solid #f59e0b; }
.ds-label     { font-size: 0.77rem; color: var(--muted-foreground); flex: 1; }
.ds-val       { font-size: 0.85rem; font-weight: 700; color: var(--foreground); }

/* Tooltip icon */
.info-tooltip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--secondary);
    color: var(--muted-foreground);
    font-size: 12px;
    font-weight: 600;
    cursor: help;
    flex-shrink: 0;
}

/* Health */
.health-body { padding: 1rem 1.4rem 1.2rem; display: flex; flex-direction: column; gap: 0.8rem; flex: 1; }
.health-score-row { display: flex; align-items: baseline; gap: 0.3rem; }
.health-score-num { font-size: 2.1rem; font-weight: 800; line-height: 1; }
.health-score-den { font-size: 0.88rem; color: var(--muted-foreground); font-weight: 500; }
.health-badge { margin-left: auto; font-size: 0.7rem; font-weight: 600; padding: 0.18rem 0.55rem; border-radius: 99px; }
.health-bar-track { height: 6px; background: var(--accent); border-radius: 99px; overflow: hidden; }
.health-bar-fill  { height: 100%; border-radius: 99px; transition: width 0.6s cubic-bezier(.4,0,.2,1); }
.health-metrics   { display: flex; flex-direction: column; gap: 0.4rem; }
.hm-row   { display: flex; justify-content: space-between; align-items: center; }
.hm-label { font-size: 0.74rem; color: var(--muted-foreground); }
.hm-val   { font-size: 0.77rem; font-weight: 600; color: var(--foreground); }

/* ── Three-col bottom ── */
.three-col-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

/* Item lists */
.item-list { list-style: none; margin: 0; padding: 0; flex: 1; overflow-y: auto; max-height: 300px; }
.item-row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.8rem 1.4rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.13s;
}
.item-row:last-child { border-bottom: none; }
.item-row:hover { background: var(--accent); }

.avatar {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.68rem; font-weight: 700; flex-shrink: 0;
}
.avatar-green { background:#d1fae5; color:#065f46; }
.avatar-amber { background:#fef3c7; color:#92400e; }
.avatar-blue  { background:#dbeafe; color:#1e40af; }

.item-info  { flex: 1; min-width: 0; }
.item-name  { font-size: 0.83rem; font-weight: 600; color: var(--foreground); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-meta  { font-size: 0.73rem; color: var(--muted-foreground); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-date  { color: var(--muted-foreground); font-size: 0.68rem; opacity: 0.8; }

.status-pill { font-size: 0.67rem; font-weight: 600; padding: 0.18rem 0.5rem; border-radius: 99px; flex-shrink: 0; }
.pill-active  { background:#d1fae5; color:#065f46; }
.pill-pending { background:#fef3c7; color:#92400e; }

.approve-btn {
    flex-shrink: 0; padding: 0.28rem 0.7rem;
    background: #10b981; color: white;
    font-size: 0.73rem; font-weight: 600;
    border: none; border-radius: 7px; cursor: pointer;
    transition: background 0.15s;
}
.approve-btn:hover:not(:disabled) { background: #059669; }
.approve-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.card-footer-link {
    display: flex; align-items: center; gap: 0.3rem;
    padding: 0.75rem 1.4rem;
    font-size: 0.78rem; font-weight: 600; color: var(--muted-foreground);
    text-decoration: none; border-top: 1px solid var(--border);
    transition: color 0.15s; margin-top: auto;
}
.card-footer-link:hover { color: #10b981; }
.link-icon { width: 13px; height: 13px; }

.empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 0.5rem; padding: 2.5rem 1rem;
}
.empty-icon { width: 34px; height: 34px; color: var(--border); }
.empty-state p { font-size: 0.83rem; margin: 0; color: #94a3b8; }

.pending-card { border-top: 3px solid #f59e0b; }

/* ====================================================
   RESPONSIVE
   ==================================================== */
@media (max-width: 1200px) {
    .analytics-grid { grid-template-columns: 1fr; }   /* stack on smaller screens */
    .chart-card { grid-row: span 1; }                 /* reset row-span */
    .donut-card, .health-card { height: auto; }
}

@media (max-width: 1024px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .three-col-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .page-container { padding: 1rem; gap: 1.25rem; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
    .page-header h1 { font-size: 1.4rem; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .stat-value { font-size: 1.55rem; }
    .analytics-grid { grid-template-columns: 1fr; }
    .delta-item { padding: 0 0.6rem; min-width: 70px; }
    .three-col-grid { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 0.6rem; }
    .stat-card { padding: 1rem; }
    .stat-icon-wrapper { width: 40px; height: 40px; }
    .stat-icon { width: 20px; height: 20px; }
    .stat-value { font-size: 1.35rem; }
    .stat-action { font-size: 0.72rem; padding: 0.32rem 0.6rem; }
    .delta-divider { display: none; }
    .delta-item { padding: 0; min-width: 45%; }
    .chart-footer { gap: 0.5rem; }
}

/* ── Dark mode ── */
:global(.dark) .page-header h1  { color: var(--foreground); }
:global(.dark) .page-header p   { color: var(--muted-foreground); }

:global(.dark) .stat-card {
    background: var(--card);
    border-color: var(--border);
}
:global(.dark) .stat-label  { color: var(--muted-foreground); }
:global(.dark) .stat-value  { color: var(--foreground); }

:global(.dark) .stat-action-green { background: rgba(6,95,70,0.2);   color: #6ee7b7; border-color: rgba(6,95,70,0.4); }
:global(.dark) .stat-action-amber { background: rgba(120,53,15,0.2); color: #fde68a; border-color: rgba(120,53,15,0.4); }
:global(.dark) .stat-action-blue  { background: rgba(30,58,138,0.2); color: #93c5fd; border-color: rgba(30,58,138,0.4); }

:global(.dark) .card {
    background: var(--card);
    border-color: var(--border);
}
:global(.dark) .card-header  { border-bottom-color: var(--border); }
:global(.dark) .card-title   { color: var(--foreground); }
:global(.dark) .card-subtitle { color: var(--muted-foreground); }

:global(.dark) .chart-legend .legend-label { color: var(--muted-foreground); }
:global(.dark) .trend-svg line { stroke: var(--border); }
:global(.dark) .trend-svg circle[fill="white"] { fill: var(--card); }
:global(.dark) .trend-svg text { fill: var(--muted-foreground); }

:global(.dark) .chart-footer  { border-top-color: var(--border); background: var(--card); }
:global(.dark) .delta-label   { color: var(--muted-foreground); }
:global(.dark) .delta-neutral { color: var(--foreground); }
:global(.dark) .delta-divider { background: var(--border); }

:global(.dark) .donut-body svg { color: var(--foreground); }
:global(.dark) .ds-label { color: var(--muted-foreground); }
:global(.dark) .ds-val   { color: var(--foreground); }
:global(.dark) .ds-dot[style*="#e2e8f0"] { background: var(--border) !important; }

:global(.dark) .health-bar-track { background: var(--accent); }
:global(.dark) .health-score-den { color: var(--muted-foreground); }
:global(.dark) .hm-label { color: var(--muted-foreground); }
:global(.dark) .hm-val   { color: var(--foreground); }
:global(.dark) .info-tooltip { color: var(--muted-foreground); }

:global(.dark) .item-row { border-bottom-color: var(--border); }
:global(.dark) .item-row:hover { background: var(--accent); }
:global(.dark) .item-name { color: var(--foreground); }
:global(.dark) .item-meta { color: var(--muted-foreground); }

:global(.dark) .approve-btn {
    background: rgba(6,95,70,0.2);
    color: #6ee7b7;
    border-color: rgba(6,95,70,0.4);
}
:global(.dark) .approve-btn:hover {
    background: rgba(6,95,70,0.35);
}

:global(.dark) .badge-green { background: rgba(16, 185, 129, 0.2); color: #34d399; }
:global(.dark) .badge-blue  { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
:global(.dark) .badge-amber { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

:global(.dark) .avatar-green { background: rgba(16, 185, 129, 0.2); color: #34d399; }
:global(.dark) .avatar-amber { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
:global(.dark) .avatar-blue  { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }

:global(.dark) .pill-active  { background: rgba(16, 185, 129, 0.2); color: #34d399; }
:global(.dark) .pill-pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

:global(.dark) .card-footer-link { color: var(--muted-foreground); border-top-color: var(--border); }
:global(.dark) .card-footer-link:hover { color: var(--foreground); }

:global(.dark) .empty-state p { color: var(--muted-foreground); }
:global(.dark) .empty-icon { color: var(--border); }
</style>