<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
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
    operating_hours: tenantInfo.value?.operating_hours ?? {
        monday: { is_open: true, open_time: '08:00', close_time: '18:00' },
        tuesday: { is_open: true, open_time: '08:00', close_time: '18:00' },
        wednesday: { is_open: true, open_time: '08:00', close_time: '18:00' },
        thursday: { is_open: true, open_time: '08:00', close_time: '18:00' },
        friday: { is_open: true, open_time: '08:00', close_time: '18:00' },
        saturday: { is_open: true, open_time: '09:00', close_time: '15:00' },
        sunday: { is_open: false, open_time: '09:00', close_time: '15:00' },
    },
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

const hours = ref([
    { key: 'monday', day: 'Monday', ...(tenantInfo.value?.operating_hours?.monday ?? { is_open: true, open_time: '08:00', close_time: '18:00' }) },
    { key: 'tuesday', day: 'Tuesday', ...(tenantInfo.value?.operating_hours?.tuesday ?? { is_open: true, open_time: '08:00', close_time: '18:00' }) },
    { key: 'wednesday', day: 'Wednesday', ...(tenantInfo.value?.operating_hours?.wednesday ?? { is_open: true, open_time: '08:00', close_time: '18:00' }) },
    { key: 'thursday', day: 'Thursday', ...(tenantInfo.value?.operating_hours?.thursday ?? { is_open: true, open_time: '08:00', close_time: '18:00' }) },
    { key: 'friday', day: 'Friday', ...(tenantInfo.value?.operating_hours?.friday ?? { is_open: true, open_time: '08:00', close_time: '18:00' }) },
    { key: 'saturday', day: 'Saturday', ...(tenantInfo.value?.operating_hours?.saturday ?? { is_open: true, open_time: '09:00', close_time: '15:00' }) },
    { key: 'sunday', day: 'Sunday', ...(tenantInfo.value?.operating_hours?.sunday ?? { is_open: false, open_time: '09:00', close_time: '15:00' }) },
].map((day) => ({
    ...day,
    enabled: day.is_open,
    open: day.open_time,
    close: day.close_time,
})));

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
    const formattedHours = hours.value.reduce((acc, day) => {
        acc[day.key] = {
            is_open: day.enabled,
            open_time: day.open,
            close_time: day.close,
        };
        return acc;
    }, {} as Record<string, { is_open: boolean; open_time: string; close_time: string }>);

    storeForm.transform((data) => ({
        store_name: data.store_name,
        email: data.email,
        description: data.description,
        address: data.address,
        phone: data.phone,
        operating_hours: formattedHours,
        tagline: data.tagline,
        pickup_notes: data.pickup_notes,
        website: data.website,
        pickup_lead_time: data.pickup_lead_time,
        order_notice: data.order_notice,
    })).put('/vendor/store-settings', {
        onSuccess: () => {
            saveLabel.value = 'Changes saved';
        },
        onError: () => {
            saveLabel.value = 'Could not save changes';
        },
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
                <section class="overflow-hidden rounded-[30px] border border-[#DCE8E1] bg-white shadow-sm">
    <div class="bg-[linear-gradient(135deg,#1B4A3D_0%,#2C725E_100%)] px-5 py-7 sm:px-7 sm:py-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#F7E8C6]">
                    <Sparkles class="h-3.5 w-3.5" />
                    Store settings
                </div>

                <h1 class="text-2xl font-semibold tracking-tight !text-white sm:text-3xl lg:text-4xl">
                    {{ storeName }}
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-7 text-white sm:text-base">
                    Keep your storefront polished, informative, and easy to manage across desktop and mobile.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[430px]">
                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">Lead time</p>
                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ storeForm.pickup_lead_time }}</p>
                </div>

                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">Order notice</p>
                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ storeForm.order_notice }}</p>
                </div>

                <div class="rounded-2xl px-4 py-4 backdrop-blur-sm" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.15)">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color:#ffffff">Setup</p>
                    <p class="mt-2 text-xl font-semibold" style="color:#ffffff">{{ setupProgress }}% complete</p>
                </div>
            </div>
        </div>
    </div>
</section>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_360px]">
                    <div class="grid gap-6">
                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-4 sm:p-6 shadow-sm">
                            <div class="mb-5 flex flex-col gap-3">
                                <div>
                                    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#EDF6F1] px-3 py-1 text-xs font-semibold text-[#245C4A]">
                                        <Store class="h-3.5 w-3.5" />
                                        Storefront
                                    </div>
                                    <h2 class="text-base sm:text-lg font-semibold text-[#183D34]">Basic information</h2>
                                    <p class="mt-1 text-xs sm:text-sm text-[#6E817A]">
                                        These details help customers quickly understand your store.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-[#245C4A] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1D4B3C]"
                                    @click="saveChanges"
                                >
                                    <Save class="h-4 w-4" />
                                    Save changes
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Store name</span>
                                    <input
                                        v-model="storeForm.store_name"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Store slug</span>
                                    <div class="flex overflow-hidden rounded-xl border border-[#D7E3DC] bg-[#F3F7F5]">
                                        <span class="flex items-center border-r border-[#E5EEEA] bg-[#EAEFEC] px-3 text-xs text-[#70867D] shrink-0">
                                            {{ storeDomain ? storeDomain.replace(storeForm.store_slug, '') : 'your-domain/' }}
                                        </span>
                                        <input
                                            v-model="storeForm.store_slug"
                                            type="text"
                                            readonly
                                            title="Store slug is set at provisioning and cannot be changed here."
                                            class="h-11 min-w-0 flex-1 bg-transparent px-4 text-sm text-[#70867D] outline-none cursor-not-allowed select-all"
                                        />
                                    </div>
                                    <p class="mt-1 text-xs text-[#8A9C95]">Slug is fixed after store creation.</p>
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Short tagline</span>
                                    <input
                                        v-model="storeForm.tagline"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 flex items-center gap-2 text-sm font-medium text-[#183D34]">
                                        <Mail class="h-4 w-4 text-[#245C4A]" />
                                        Contact email
                                    </span>
                                    <input
                                        v-model="storeForm.email"
                                        type="email"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 flex items-center gap-2 text-sm font-medium text-[#183D34]">
                                        <Phone class="h-4 w-4 text-[#245C4A]" />
                                        Contact number
                                    </span>
                                    <input
                                        v-model="storeForm.phone"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 flex items-center gap-2 text-sm font-medium text-[#183D34]">
                                        <MapPin class="h-4 w-4 text-[#245C4A]" />
                                        Pick-up address
                                    </span>
                                    <input
                                        v-model="storeForm.address"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">About your store</span>
                                    <textarea
                                        v-model="storeForm.description"
                                        rows="4"
                                        class="w-full rounded-2xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 py-3 text-sm leading-6 text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    ></textarea>
                                </label>
                            </div>
                        </section>

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-4 sm:p-6 shadow-sm">
                            <div class="mb-5">
                                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#FFF7EA] px-3 py-1 text-xs font-semibold text-[#A56D17]">
                                    <Clock3 class="h-3.5 w-3.5" />
                                    Pick-up operations
                                </div>
                                <h2 class="text-base sm:text-lg font-semibold text-[#183D34]">Schedule and order settings</h2>
                                <p class="mt-1 text-xs sm:text-sm text-[#6E817A]">
                                    Set the hours and notes customers see when ordering from your store.
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Estimated lead time</span>
                                    <input
                                        v-model="storeForm.pickup_lead_time"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Customer order notice</span>
                                    <input
                                        v-model="storeForm.order_notice"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Pick-up instructions</span>
                                    <textarea
                                        v-model="storeForm.pickup_notes"
                                        rows="3"
                                        class="w-full rounded-2xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 py-3 text-sm leading-6 text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    ></textarea>
                                </label>
                            </div>

                            <div class="mt-5 grid gap-3">
                                <div
                                    v-for="day in hours"
                                    :key="day.day"
                                    class="rounded-2xl border border-[#E4ECE8] bg-[#FBFCFC] p-3 sm:p-4"
                                >
                                    <!-- Day label + toggle row (always visible) -->
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <div>
                                            <p class="text-sm font-semibold text-[#183D34]">{{ day.day }}</p>
                                            <p class="text-xs text-[#74867F]">{{ day.enabled ? 'Open for pick-up' : 'Closed' }}</p>
                                        </div>
                                        <label class="inline-flex items-center gap-2">
                                            <span class="text-xs font-medium text-[#61766F]">Open</span>
                                            <input
                                                v-model="day.enabled"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]"
                                            />
                                        </label>
                                    </div>
                                    <!-- Time pickers row - always flex-row, gap, wraps if needed -->
                                    <div class="flex items-center gap-2">
                                        <input
                                            v-model="day.open"
                                            type="time"
                                            :disabled="!day.enabled"
                                            class="flex-1 min-w-0 h-10 rounded-xl border border-[#D7E3DC] bg-white px-2 text-sm text-[#1E4138] outline-none disabled:cursor-not-allowed disabled:bg-[#EFF4F1] disabled:text-[#90A39B]"
                                        />
                                        <span class="text-xs text-[#74867F] shrink-0">to</span>
                                        <input
                                            v-model="day.close"
                                            type="time"
                                            :disabled="!day.enabled"
                                            class="flex-1 min-w-0 h-10 rounded-xl border border-[#D7E3DC] bg-white px-2 text-sm text-[#1E4138] outline-none disabled:cursor-not-allowed disabled:bg-[#EFF4F1] disabled:text-[#90A39B]"
                                        />
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="grid gap-6 order-first xl:order-last">
                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-4 sm:p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <CheckCircle2 class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A]" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34]">Store readiness</h3>
                            </div>

                            <div class="mb-4 flex items-center justify-between">
                                <p class="text-sm text-[#70827B]">Profile completion</p>
                                <span class="rounded-full bg-[#EDF6F1] px-3 py-1 text-sm font-semibold text-[#245C4A]">
                                    {{ setupProgress }}%
                                </span>
                            </div>

                            <div class="mb-5 h-2 rounded-full bg-[#ECF2EF]">
                                <div class="h-2 rounded-full bg-[#245C4A]" :style="{ width: `${setupProgress}%` }"></div>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="item in completionItems"
                                    :key="item.label"
                                    class="flex items-center justify-between rounded-2xl border border-[#E6EEEA] px-4 py-3"
                                >
                                    <span class="text-sm font-medium text-[#183D34]">{{ item.label }}</span>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="item.done ? 'bg-[#EDF7F2] text-[#1D6B55]' : 'bg-[#FFF5E7] text-[#A16B19]'"
                                    >
                                        {{ item.done ? 'Done' : 'Pending' }}
                                    </span>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-4 sm:p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <Settings2 class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A]" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34]">Store preferences</h3>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-start gap-3 rounded-2xl border border-[#E5EEEA] bg-[#FBFCFC] p-4">
                                    <input v-model="toggles.acceptPreOrders" type="checkbox" class="mt-1 h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]" />
                                    <span>
                                        <span class="block font-semibold text-[#183D34]">Accept pre-orders</span>
                                        <span class="mt-1 block text-sm leading-6 text-[#70827B]">Let customers place orders ahead of busy hours.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-[#E5EEEA] bg-[#FBFCFC] p-4">
                                    <input v-model="toggles.showInventoryCount" type="checkbox" class="mt-1 h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]" />
                                    <span>
                                        <span class="block font-semibold text-[#183D34]">Show item availability</span>
                                        <span class="mt-1 block text-sm leading-6 text-[#70827B]">Display stock visibility to help customers decide faster.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-[#E5EEEA] bg-[#FBFCFC] p-4">
                                    <input v-model="toggles.showPickupInstructions" type="checkbox" class="mt-1 h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]" />
                                    <span>
                                        <span class="block font-semibold text-[#183D34]">Show pick-up instructions</span>
                                        <span class="mt-1 block text-sm leading-6 text-[#70827B]">Surface order claim reminders during checkout.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-[#E5EEEA] bg-[#FBFCFC] p-4">
                                    <input v-model="toggles.autoAcceptOrders" type="checkbox" class="mt-1 h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]" />
                                    <span>
                                        <span class="block font-semibold text-[#183D34]">Auto-accept orders</span>
                                        <span class="mt-1 block text-sm leading-6 text-[#70827B]">Instantly confirm orders without manual approval.</span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-4 sm:p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <Bell class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A]" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34]">Notifications</h3>
                            </div>

                            <div class="space-y-3">
                                <label class="flex items-start gap-3 rounded-2xl border border-[#E5EEEA] bg-[#FBFCFC] p-4">
                                    <input v-model="toggles.receiveSmsAlerts" type="checkbox" class="mt-1 h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]" />
                                    <span>
                                        <span class="block font-semibold text-[#183D34]">SMS alerts</span>
                                        <span class="mt-1 block text-sm leading-6 text-[#70827B]">Receive immediate updates for new or urgent orders.</span>
                                    </span>
                                </label>

                                <label class="flex items-start gap-3 rounded-2xl border border-[#E5EEEA] bg-[#FBFCFC] p-4">
                                    <input v-model="toggles.receiveEmailAlerts" type="checkbox" class="mt-1 h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]" />
                                    <span>
                                        <span class="block font-semibold text-[#183D34]">Email digests</span>
                                        <span class="mt-1 block text-sm leading-6 text-[#70827B]">Get daily summaries of orders, performance, and activity.</span>
                                    </span>
                                </label>
                            </div>
                        </section>

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-[#F7FAF8] p-4 sm:p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <ShieldCheck class="h-4 w-4 sm:h-5 sm:w-5 text-[#245C4A]" />
                                <h3 class="text-sm sm:text-base font-semibold text-[#183D34]">Public preview</h3>
                            </div>

                            <div class="space-y-3 text-sm leading-6 text-[#6C8079]">
                                <p>
                                    Your store currently shows a
                                    <span class="font-semibold text-[#183D34]">{{ storeForm.pickup_lead_time }}</span>
                                    estimated lead time and a customer notice of
                                    <span class="font-semibold text-[#183D34]">{{ storeForm.order_notice }}</span>.
                                </p>

                                <div class="rounded-2xl border border-dashed border-[#CBD9D2] bg-white p-4">
                                    <div class="flex items-start gap-3">
                                        <ImageIcon class="mt-0.5 h-5 w-5 text-[#245C4A]" />
                                        <div>
                                            <p class="font-semibold text-[#183D34]">Branding reminder</p>
                                            <p class="mt-1 text-sm text-[#70827B]">
                                                Add a cover photo and profile logo to make your storefront feel more complete.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-[#8A9C95]">
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
