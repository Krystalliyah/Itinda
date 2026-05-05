<script setup lang="ts">
import {
    BuildingStorefrontIcon,
    CalendarIcon,
    CircleStackIcon,
    EnvelopeIcon,
    GlobeAltIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Domain {
    id: number;
    domain: string;
    tenant_id: string;
    created_at: string;
}

interface Tenant {
    id: string;
    name: string;
    email: string;
    is_approved: boolean;
    created_at: string;
    domains: Domain[];
}

defineProps<{
    open: boolean;
    tenant: Tenant | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const formatDate = (value?: string | null) => {
    if (!value) return '—';

    try {
        return new Intl.DateTimeFormat('en-PH', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(value));
    } catch {
        return value;
    }
};

const primaryDomain = (tenant: Tenant | null) => {
    return tenant?.domains?.[0]?.domain ?? 'No domain assigned';
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-2xl border-0 p-0 overflow-hidden">
            <div v-if="tenant" class="store-details-modal">
                <div class="modal-hero">
                    <div class="hero-left">
                        <div class="hero-icon">
                            <BuildingStorefrontIcon />
                        </div>

                        <div>
                            <DialogHeader class="space-y-1 text-left">
                                <DialogTitle class="hero-title">
                                    {{ tenant.name }}
                                </DialogTitle>
                                <DialogDescription class="hero-subtitle">
                                    Review the basic store and vendor information before taking action.
                                </DialogDescription>
                            </DialogHeader>
                        </div>
                    </div>

                    <div
                        class="status-badge"
                        :class="tenant.is_approved ? 'approved' : 'pending'"
                    >
                        <ShieldCheckIcon class="status-icon" />
                        {{ tenant.is_approved ? 'Approved' : 'Pending Approval' }}
                    </div>
                </div>

                <div class="modal-body">
                    <div class="details-grid">
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <EnvelopeIcon class="detail-icon" />
                                <p class="detail-label">Vendor Email</p>
                            </div>
                            <p class="detail-value break-all">{{ tenant.email }}</p>
                        </div>

                        <div class="detail-card">
                            <div class="detail-card-header">
                                <GlobeAltIcon class="detail-icon" />
                                <p class="detail-label">Primary Domain</p>
                            </div>
                            <a
                                v-if="tenant.domains?.length"
                                :href="`http://${tenant.domains[0].domain}`"
                                target="_blank"
                                class="detail-link"
                            >
                                {{ tenant.domains[0].domain }}
                            </a>
                            <p v-else class="detail-value">No domain assigned</p>
                        </div>

                        <div class="detail-card">
                            <div class="detail-card-header">
                                <CircleStackIcon class="detail-icon" />
                                <p class="detail-label">Database Name</p>
                            </div>
                            <p class="detail-value">tenant{{ tenant.id }}</p>
                        </div>

                        <div class="detail-card">
                            <div class="detail-card-header">
                                <CalendarIcon class="detail-icon" />
                                <p class="detail-label">Created At</p>
                            </div>
                            <p class="detail-value">{{ formatDate(tenant.created_at) }}</p>
                        </div>
                    </div>

                    <div class="info-panel">
                        <p class="info-title">Store Summary</p>
                        <div class="summary-list">
                            <div class="summary-row">
                                <span class="summary-key">Store Name</span>
                                <span class="summary-value">{{ tenant.name }}</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-key">Approval Status</span>
                                <span class="summary-value">
                                    {{ tenant.is_approved ? 'Approved and active' : 'Awaiting admin approval' }}
                                </span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-key">Tenant ID</span>
                                <span class="summary-value">{{ tenant.id }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <Button
                            variant="outline"
                            class="min-w-[120px]"
                            @click="emit('update:open', false)"
                        >
                            Close
                        </Button>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.store-details-modal {
    background: var(--card);
}

.modal-hero {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem 1.5rem 1.25rem;
    background:
        radial-gradient(circle at top left, var(--accent), transparent 35%),
        linear-gradient(180deg, var(--secondary) 0%, var(--card) 100%);
    border-bottom: 1px solid var(--border);
}

.hero-left {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    min-width: 0;
}

.hero-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 16px;
    background: var(--secondary);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid var(--border);
}

.hero-icon svg {
    width: 1.5rem;
    height: 1.5rem;
}

.hero-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--foreground);
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 0.92rem;
    color: var(--muted-foreground);
    max-width: 42rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    border-radius: 999px;
    padding: 0.45rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
}

.status-badge.approved {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.status-badge.pending {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.status-icon {
    width: 1rem;
    height: 1rem;
}

.modal-body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.detail-card {
    border: 1px solid var(--border);
    background: var(--secondary);
    border-radius: 14px;
    padding: 1rem;
}

.detail-card-header {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    margin-bottom: 0.6rem;
}

.detail-icon {
    width: 1rem;
    height: 1rem;
    color: var(--muted-foreground);
    flex-shrink: 0;
}

.detail-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted-foreground);
    margin: 0;
}

.detail-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--foreground);
    margin: 0;
    line-height: 1.45;
}

.detail-link {
    color: var(--primary);
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    word-break: break-all;
}

.detail-link:hover {
    text-decoration: underline;
}

.info-panel {
    border: 1px solid var(--border);
    background: var(--card);
    border-radius: 14px;
    padding: 1rem;
}

.info-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--foreground);
    margin: 0 0 0.85rem 0;
}

.summary-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border);
}

.summary-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.summary-key {
    font-size: 0.85rem;
    color: var(--muted-foreground);
    font-weight: 500;
}

.summary-value {
    font-size: 0.9rem;
    color: var(--foreground);
    font-weight: 600;
    text-align: right;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 0.25rem;
}

@media (max-width: 640px) {
    .modal-hero {
        flex-direction: column;
        align-items: stretch;
    }

    .status-badge {
        align-self: flex-start;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .summary-row {
        flex-direction: column;
        gap: 0.25rem;
    }

    .summary-value {
        text-align: left;
    }
}
</style>