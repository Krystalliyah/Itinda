<script setup lang="ts">
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Bell,
    CheckCircle2,
    Clock3,
    Image as ImageIcon,
    Mail,
    MapPin,
    Phone,
    Save,
    Settings2,
    ShieldCheck,
    Sparkles,
    Store,
} from 'lucide-vue-next';

import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();
const page = usePage();

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

const tenantInfo = computed(() => (page.props as any).tenantInfo ?? (page.props as any).store ?? {});
const storeName = computed(() => tenantInfo.value?.name ?? '');

const saveLabel = ref('Changes not yet published');

const ORDERED_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as const;
type DayKey = typeof ORDERED_DAYS[number];

const DAY_LABELS: Record<DayKey, string> = {
    monday: 'Monday',
    tuesday: 'Tuesday',
    wednesday: 'Wednesday',
    thursday: 'Thursday',
    friday: 'Friday',
    saturday: 'Saturday',
    sunday: 'Sunday',
};

const DEFAULT_HOURS: Record<DayKey, { is_open: boolean; open_time: string; close_time: string }> = {
    monday: { is_open: true, open_time: '09:00', close_time: '17:00' },
    tuesday: { is_open: true, open_time: '09:00', close_time: '17:00' },
    wednesday: { is_open: true, open_time: '09:00', close_time: '17:00' },
    thursday: { is_open: true, open_time: '09:00', close_time: '17:00' },
    friday: { is_open: true, open_time: '09:00', close_time: '17:00' },
    saturday: { is_open: true, open_time: '10:00', close_time: '15:00' },
    sunday: { is_open: false, open_time: '10:00', close_time: '15:00' },
};

const storeForm = useForm({
    store_name: tenantInfo.value?.name ?? '',
    store_slug: tenantInfo.value?.id ?? '',
    tagline: tenantInfo.value?.data?.tagline ?? '',
    email: tenantInfo.value?.email ?? '',
    phone: tenantInfo.value?.phone ?? '',
    address: tenantInfo.value?.address ?? '',
    pickup_notes: tenantInfo.value?.data?.pickup_notes ?? '',
    description: tenantInfo.value?.description ?? '',
    website: tenantInfo.value?.data?.website ?? '',
    pickup_lead_time: tenantInfo.value?.data?.pickup_lead_time ?? '',
    order_notice: tenantInfo.value?.data?.order_notice ?? '',
    operating_hours: (tenantInfo.value?.operating_hours ?? DEFAULT_HOURS) as Record<DayKey, { is_open: boolean; open_time: string; close_time: string }>,
});

const storeDomain = computed(() => tenantInfo.value?.domain ?? null);

const toggles = ref({
    acceptPreOrders: true,
    showInventoryCount: true,
    showPickupInstructions: true,
    autoAcceptOrders: false,
    receiveSmsAlerts: true,
    receiveEmailAlerts: true,
});

const completionItems = computed(() => [
    { label: 'Store profile', done: true },
    { label: 'Contact details', done: true },
    { label: 'Pick-up schedule', done: true },
    { label: 'Notifications', done: true },
    { label: 'Brand assets', done: false },
]);

const completedCount = computed(() => completionItems.value.filter((item) => item.done).length);
const setupProgress = computed(() => Math.round((completedCount.value / completionItems.value.length) * 100));

const saveChanges = () => {
    storeForm.put('/vendor/store-settings', {
        onSuccess: () => {
            saveLabel.value = 'Changes saved';
        },
        onError: () => {
            saveLabel.value = 'Could not save changes';
        },
    });
};

const saveOperatingHours = () => {
    router.put('/vendor/store-settings', {
        operating_hours: storeForm.operating_hours,
    });
};
</script>

<template>
    <Head title="Store Settings" />

    <div class="dashboard-wrapper">
        <Header />

        <Sidebar role="vendor">
            <VendorNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8 py-6">
                <!-- Hero Section -->
                <section class="overflow-hidden rounded-[30px] border border-[#DCE8E1] bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <div class="bg-[linear-gradient(135deg,#1B4A3D_0%,#2C725E_100%)] dark:bg-[linear-gradient(135deg,#0f172a_0%,#1e293b_100%)] px-5 py-7 sm:px-7 sm:py-8">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                            <div class="max-w-2xl">
                                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#F7E8C6]">
                                    <Sparkles class="h-3.5 w-3.5" />
                                    Store settings
                                </div>

                                <h1 class="text-2xl font-semibold tracking-tight !text-white sm:text-3xl lg:text-4xl">
                                    {{ storeName || 'Complete Your Store Setup' }}
                                </h1>

                                <p class="mt-3 max-w-xl text-sm leading-7 text-white sm:text-base opacity-90">
                                    {{ storeName ? 'Keep your storefront polished, informative, and easy to manage.' : 'Fill out the information below to get your store ready for customers.' }}
                                </p>
                            </div>

                            <div class="rounded-2xl px-6 py-4 backdrop-blur-sm text-center bg-white/10 border border-white/10">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-white">Setup Progress</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ setupProgress }}% complete</p>
                                <div class="mt-2 w-32 h-1.5 rounded-full bg-white/20">
                                    <div class="h-1.5 rounded-full bg-[#F7E8C6]" :style="{ width: `${setupProgress}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_360px]">
                    <!-- Main Content area -->
                    <div class="grid gap-6">
                        <!-- Basic Information -->
                        <section class="rounded-[28px] border border-border bg-card p-4 sm:p-6 shadow-sm">
                            <div class="mb-5 flex flex-col gap-3">
                                <div>
                                    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-secondary px-3 py-1 text-xs font-semibold text-foreground">
                                        <Store class="h-3.5 w-3.5" />
                                        Storefront
                                    </div>
                                    <h2 class="text-base sm:text-lg font-semibold text-foreground">Basic information</h2>
                                    <p class="mt-1 text-xs sm:text-sm text-muted-foreground">
                                        These details help customers quickly understand your store.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-brand-green px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 dark:bg-emerald-600"
                                    @click="saveChanges"
                                >
                                    <Save class="h-4 w-4" />
                                    Save changes
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-foreground">Store name</span>
                                    <input
                                        v-model="storeForm.store_name"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-foreground">Store slug</span>
                                    <div class="flex overflow-hidden rounded-xl border border-border bg-secondary/50">
                                        <span class="flex items-center border-r border-border bg-secondary px-3 text-xs text-muted-foreground shrink-0">
                                            {{ storeDomain ? storeDomain.replace(storeForm.store_slug, '') : 'your-domain/' }}
                                        </span>
                                        <input
                                            v-model="storeForm.store_slug"
                                            type="text"
                                            readonly
                                            class="h-11 min-w-0 flex-1 bg-transparent px-4 text-sm text-muted-foreground outline-none cursor-not-allowed select-all"
                                        />
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground opacity-70">Slug is fixed after store creation.</p>
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-foreground">Short tagline</span>
                                    <input
                                        v-model="storeForm.tagline"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 flex items-center gap-2 text-sm font-medium text-foreground">
                                        <Mail class="h-4 w-4 text-brand-green dark:text-emerald-500" />
                                        Contact email
                                    </span>
                                    <input
                                        v-model="storeForm.email"
                                        type="email"
                                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 flex items-center gap-2 text-sm font-medium text-foreground">
                                        <Phone class="h-4 w-4 text-brand-green dark:text-emerald-500" />
                                        Contact number
                                    </span>
                                    <input
                                        v-model="storeForm.phone"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    />
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 flex items-center gap-2 text-sm font-medium text-foreground">
                                        <MapPin class="h-4 w-4 text-brand-green dark:text-emerald-500" />
                                        Pick-up address
                                    </span>
                                    <input
                                        v-model="storeForm.address"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    />
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-foreground">About your store</span>
                                    <textarea
                                        v-model="storeForm.description"
                                        rows="4"
                                        class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm leading-6 text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    ></textarea>
                                </label>
                            </div>
                        </section>

                        <!-- Pick-up Operations (Operating Hours) -->
                        <section class="rounded-[28px] border border-border bg-card p-4 sm:p-6 shadow-sm">
                            <div class="mb-5">
                                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-600 dark:text-amber-400">
                                    <Clock3 class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                    Pick-up operations
                                </div>
                                <h2 class="text-base sm:text-lg font-semibold text-foreground">Schedule and order settings</h2>
                                <p class="mt-1 text-xs sm:text-sm text-muted-foreground">
                                    Set the hours and notes customers see when ordering from your store.
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-foreground">Estimated lead time</span>
                                    <input
                                        v-model="storeForm.pickup_lead_time"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-foreground">Customer order notice</span>
                                    <input
                                        v-model="storeForm.order_notice"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    />
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-foreground">Pick-up instructions</span>
                                    <textarea
                                        v-model="storeForm.pickup_notes"
                                        rows="3"
                                        class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm leading-6 text-foreground outline-none transition focus:border-brand-green focus:ring-2 focus:ring-brand-green/10"
                                    ></textarea>
                                </label>
                            </div>

                            <div class="mt-6 mb-5 flex flex-col gap-3">
                                <h3 class="text-sm font-semibold text-foreground">Operating hours</h3>
                                <p class="text-xs text-muted-foreground">
                                    Define when your store is open for pick-ups each day of the week.
                                </p>
                            </div>

                            <div class="mt-5 grid gap-3">
                                <div
                                    v-for="dayKey in ORDERED_DAYS"
                                    :key="dayKey"
                                    class="rounded-2xl border border-border bg-secondary/30 p-3 sm:p-4 dark:bg-slate-900/40"
                                >
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <div>
                                            <p class="text-sm font-semibold text-foreground">{{ DAY_LABELS[dayKey] }}</p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ storeForm.operating_hours[dayKey]?.is_open ? 'Open for pick-up' : 'Closed' }}
                                            </p>
                                        </div>
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <span class="text-xs font-medium text-muted-foreground">Open</span>
                                            <input
                                                v-model="storeForm.operating_hours[dayKey].is_open"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-border bg-background text-brand-green focus:ring-brand-green/20"
                                            />
                                        </label>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input
                                            v-model="storeForm.operating_hours[dayKey].open_time"
                                            type="time"
                                            :disabled="!storeForm.operating_hours[dayKey]?.is_open"
                                            class="flex-1 min-w-0 h-10 rounded-xl border border-border bg-background px-2 text-sm text-foreground outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        />
                                        <span class="text-xs text-muted-foreground shrink-0">to</span>
                                        <input
                                            v-model="storeForm.operating_hours[dayKey].close_time"
                                            type="time"
                                            :disabled="!storeForm.operating_hours[dayKey]?.is_open"
                                            class="flex-1 min-w-0 h-10 rounded-xl border border-border bg-background px-2 text-sm text-foreground outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        />
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-6 w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-brand-green px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 dark:bg-emerald-600"
                                @click="saveOperatingHours"
                            >
                                <Save class="h-4 w-4" />
                                Save operating hours
                            </button>
                        </section>
                    </div>

                    <!-- Sidebar area (Preferences, Notifications, Public Preview) -->
                    <aside class="grid gap-6 order-first xl:order-last">
                        <!-- Store Readiness -->
                        <section class="rounded-[28px] border border-border bg-card p-4 sm:p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 sm:h-5 sm:w-5 text-brand-green dark:text-emerald-500" />
                                <h3 class="text-sm sm:text-base font-semibold text-foreground">Store readiness</h3>
                            </div>

                            <div class="mb-4 flex items-center justify-between">
                                <p class="text-sm text-muted-foreground">Profile completion</p>
                                <span class="rounded-full bg-secondary px-3 py-1 text-sm font-semibold text-foreground">
                                    {{ setupProgress }}%
                                </span>
                            </div>

                            <div class="mb-5 h-2 rounded-full bg-secondary">
                                <div class="h-2 rounded-full bg-brand-green" :style="{ width: `${setupProgress}%` }"></div>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="item in completionItems"
                                    :key="item.label"
                                    class="flex items-center justify-between rounded-2xl border border-border px-4 py-3 bg-background/40"
                                >
                                    <span class="text-sm font-medium text-foreground">{{ item.label }}</span>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="item.done ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'"
                                    >
                                        {{ item.done ? 'Done' : 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        </section>

                        <!-- Store Preferences -->
                        <section class="rounded-[28px] border border-border bg-card p-4 sm:p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <Settings2 class="h-4 w-4 sm:h-5 sm:w-5 text-brand-green dark:text-emerald-500" />
                                <h3 class="text-sm sm:text-base font-semibold text-foreground">Store preferences</h3>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-start gap-3 rounded-2xl border border-border bg-secondary/30 p-4 cursor-pointer hover:bg-secondary/50 transition-colors">
                                    <input v-model="toggles.acceptPreOrders" type="checkbox" class="mt-1 h-4 w-4 rounded border-border bg-background text-brand-green focus:ring-brand-green/20" />
                                    <span>
                                        <span class="block font-semibold text-foreground">Accept pre-orders</span>
                                        <span class="mt-1 block text-sm leading-6 text-muted-foreground">Let customers place orders ahead of busy hours.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-border bg-secondary/30 p-4 cursor-pointer hover:bg-secondary/50 transition-colors">
                                    <input v-model="toggles.showInventoryCount" type="checkbox" class="mt-1 h-4 w-4 rounded border-border bg-background text-brand-green focus:ring-brand-green/20" />
                                    <span>
                                        <span class="block font-semibold text-foreground">Show item availability</span>
                                        <span class="mt-1 block text-sm leading-6 text-muted-foreground">Display stock visibility to help customers decide faster.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-border bg-secondary/30 p-4 cursor-pointer hover:bg-secondary/50 transition-colors">
                                    <input v-model="toggles.showPickupInstructions" type="checkbox" class="mt-1 h-4 w-4 rounded border-border bg-background text-brand-green focus:ring-brand-green/20" />
                                    <span>
                                        <span class="block font-semibold text-foreground">Show pick-up instructions</span>
                                        <span class="mt-1 block text-sm leading-6 text-muted-foreground">Surface order claim reminders during checkout.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-border bg-secondary/30 p-4 cursor-pointer hover:bg-secondary/50 transition-colors">
                                    <input v-model="toggles.autoAcceptOrders" type="checkbox" class="mt-1 h-4 w-4 rounded border-border bg-background text-brand-green focus:ring-brand-green/20" />
                                    <span>
                                        <span class="block font-semibold text-foreground">Auto-accept orders</span>
                                        <span class="mt-1 block text-sm leading-6 text-muted-foreground">Instantly confirm orders without manual approval.</span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <!-- Notifications -->
                        <section class="rounded-[28px] border border-border bg-card p-4 sm:p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <Bell class="h-4 w-4 sm:h-5 sm:w-5 text-brand-green dark:text-emerald-500" />
                                <h3 class="text-sm sm:text-base font-semibold text-foreground">Notifications</h3>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-start gap-3 rounded-2xl border border-border bg-secondary/30 p-4 cursor-pointer hover:bg-secondary/50 transition-colors">
                                    <input v-model="toggles.receiveSmsAlerts" type="checkbox" class="mt-1 h-4 w-4 rounded border-border bg-background text-brand-green focus:ring-brand-green/20" />
                                    <span>
                                        <span class="block font-semibold text-foreground">SMS alerts</span>
                                        <span class="mt-1 block text-sm leading-6 text-muted-foreground">Receive immediate updates for new or urgent orders.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-border bg-secondary/30 p-4 cursor-pointer hover:bg-secondary/50 transition-colors">
                                    <input v-model="toggles.receiveEmailAlerts" type="checkbox" class="mt-1 h-4 w-4 rounded border-border bg-background text-brand-green focus:ring-brand-green/20" />
                                    <span>
                                        <span class="block font-semibold text-foreground">Email digests</span>
                                        <span class="mt-1 block text-sm leading-6 text-muted-foreground">Get daily summaries of orders, performance, and activity.</span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <!-- Public Preview -->
                        <section class="rounded-[28px] border border-border bg-secondary/10 p-4 sm:p-5 shadow-sm dark:bg-slate-900/40">
                            <div class="mb-4 flex items-center gap-2">
                                <ShieldCheck class="h-4 w-4 sm:h-5 sm:w-5 text-brand-green dark:text-emerald-500" />
                                <h3 class="text-sm sm:text-base font-semibold text-foreground">Public preview</h3>
                            </div>

                            <div class="space-y-3 text-sm leading-6 text-muted-foreground">
                                <p>
                                    Your store currently shows a
                                    <span class="font-semibold text-foreground">{{ storeForm.pickup_lead_time }}</span>
                                    estimated lead time and a customer notice of
                                    <span class="font-semibold text-foreground">{{ storeForm.order_notice }}</span>.
                                </p>

                                <div class="rounded-2xl border border-dashed border-border bg-card p-4">
                                    <div class="flex items-start gap-3">
                                        <ImageIcon class="mt-0.5 h-5 w-5 text-brand-green dark:text-emerald-500" />
                                        <div>
                                            <p class="font-semibold text-foreground">Branding reminder</p>
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                Add a cover photo and profile logo to make your storefront feel more complete.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-muted-foreground opacity-60">
                                    {{ saveLabel }}
                                </p>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</template>