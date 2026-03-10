<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
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
const storeName = computed(() => tenantInfo.value?.name ?? 'iTinda Vendor Store');

const saveLabel = ref('Changes not yet published');

const storeForm = ref({
    storeName: storeName.value,
    storeSlug: tenantInfo.value?.slug ?? 'itinda-vendor-store',
    tagline: 'Fresh, student-friendly favorites ready for quick campus pick-up.',
    email: tenantInfo.value?.email ?? 'vendor@itinda.test',
    phone: tenantInfo.value?.phone ?? '+63 912 345 6789',
    pickupAddress: tenantInfo.value?.address ?? 'Main Campus Arcade, Ground Floor',
    pickupNotes: 'Please present your order receipt upon pick-up. Orders are typically ready within 20–30 minutes.',
    about: 'We serve affordable meals, drinks, and daily essentials tailored for fast and convenient campus pick-up.',
    website: 'itinda.app/store/vendor-store',
    pickupLeadTime: '20–30 mins',
    orderNotice: '30 mins',
});

const toggles = ref({
    acceptPreOrders: true,
    showInventoryCount: true,
    showPickupInstructions: true,
    autoAcceptOrders: false,
    receiveSmsAlerts: true,
    receiveEmailAlerts: true,
});

const hours = ref([
    { day: 'Monday', open: '08:00', close: '18:00', enabled: true },
    { day: 'Tuesday', open: '08:00', close: '18:00', enabled: true },
    { day: 'Wednesday', open: '08:00', close: '18:00', enabled: true },
    { day: 'Thursday', open: '08:00', close: '18:00', enabled: true },
    { day: 'Friday', open: '08:00', close: '18:00', enabled: true },
    { day: 'Saturday', open: '09:00', close: '15:00', enabled: true },
    { day: 'Sunday', open: '09:00', close: '15:00', enabled: false },
]);

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
    saveLabel.value = 'Changes saved locally';
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
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <section class="overflow-hidden rounded-[30px] border border-[#DCE8E1] bg-white shadow-sm">
    <div class="bg-[linear-gradient(135deg,#1B4A3D_0%,#2C725E_100%)] px-5 py-7 sm:px-7 sm:py-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-[#F7E8C6]">
                    <Sparkles class="h-3.5 w-3.5" />
                    Store settings
                </div>

                <h1 class="text-3xl font-semibold tracking-tight !text-white sm:text-4xl">
                    {{ storeName }}
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-7 text-[#F2F7F4] sm:text-base">
                    Keep your storefront polished, informative, and easy to manage across desktop and mobile.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[430px]">
                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">Lead time</p>
                    <p class="mt-2 text-xl font-semibold !text-white">{{ storeForm.pickupLeadTime }}</p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">Order notice</p>
                    <p class="mt-2 text-xl font-semibold !text-white">{{ storeForm.orderNotice }}</p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/15 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#DDE9E4]">Setup</p>
                    <p class="mt-2 text-xl font-semibold !text-white">{{ setupProgress }}% complete</p>
                </div>
            </div>
        </div>
    </div>
</section>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_360px]">
                    <div class="grid gap-6">
                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm sm:p-6">
                            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#EDF6F1] px-3 py-1 text-xs font-semibold text-[#245C4A]">
                                        <Store class="h-3.5 w-3.5" />
                                        Storefront
                                    </div>
                                    <h2 class="text-xl font-semibold text-[#183D34]">Basic information</h2>
                                    <p class="mt-1 text-sm text-[#6E817A]">
                                        These details help customers quickly understand your store.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#245C4A] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#1D4B3C]"
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
                                        v-model="storeForm.storeName"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Store slug</span>
                                    <div class="flex overflow-hidden rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] focus-within:border-[#245C4A] focus-within:ring-2 focus-within:ring-[#245C4A]/10">
                                        <span class="flex items-center border-r border-[#E5EEEA] bg-[#F3F7F5] px-3 text-xs text-[#70867D]">
                                            itinda.app/store/
                                        </span>
                                        <input
                                            v-model="storeForm.storeSlug"
                                            type="text"
                                            class="h-11 min-w-0 flex-1 bg-transparent px-4 text-sm text-[#1E4138] outline-none"
                                        />
                                    </div>
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
                                        v-model="storeForm.pickupAddress"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">About your store</span>
                                    <textarea
                                        v-model="storeForm.about"
                                        rows="4"
                                        class="w-full rounded-2xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 py-3 text-sm leading-6 text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    ></textarea>
                                </label>
                            </div>
                        </section>

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm sm:p-6">
                            <div class="mb-6">
                                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#FFF7EA] px-3 py-1 text-xs font-semibold text-[#A56D17]">
                                    <Clock3 class="h-3.5 w-3.5" />
                                    Pick-up operations
                                </div>
                                <h2 class="text-xl font-semibold text-[#183D34]">Schedule and order settings</h2>
                                <p class="mt-1 text-sm text-[#6E817A]">
                                    Set the hours and notes customers see when ordering from your store.
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Estimated lead time</span>
                                    <input
                                        v-model="storeForm.pickupLeadTime"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Customer order notice</span>
                                    <input
                                        v-model="storeForm.orderNotice"
                                        type="text"
                                        class="h-11 w-full rounded-xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 text-sm text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    />
                                </label>

                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-medium text-[#183D34]">Pick-up instructions</span>
                                    <textarea
                                        v-model="storeForm.pickupNotes"
                                        rows="3"
                                        class="w-full rounded-2xl border border-[#D7E3DC] bg-[#FAFCFB] px-4 py-3 text-sm leading-6 text-[#1E4138] outline-none transition focus:border-[#245C4A] focus:ring-2 focus:ring-[#245C4A]/10"
                                    ></textarea>
                                </label>
                            </div>

                            <div class="mt-6 grid gap-3">
                                <div
                                    v-for="day in hours"
                                    :key="day.day"
                                    class="grid gap-3 rounded-2xl border border-[#E4ECE8] bg-[#FBFCFC] p-4 sm:grid-cols-[minmax(0,1fr)_128px_128px_auto] sm:items-center"
                                >
                                    <div class="flex items-center justify-between gap-3 sm:block">
                                        <div>
                                            <p class="font-semibold text-[#183D34]">{{ day.day }}</p>
                                            <p class="text-sm text-[#74867F]">
                                                {{ day.enabled ? 'Available for pick-up' : 'Closed' }}
                                            </p>
                                        </div>

                                        <label class="inline-flex items-center gap-2 sm:hidden">
                                            <span class="text-xs font-medium text-[#61766F]">Open</span>
                                            <input
                                                v-model="day.enabled"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]"
                                            />
                                        </label>
                                    </div>

                                    <input
                                        v-model="day.open"
                                        type="time"
                                        :disabled="!day.enabled"
                                        class="h-11 rounded-xl border border-[#D7E3DC] bg-white px-3 text-sm text-[#1E4138] outline-none disabled:cursor-not-allowed disabled:bg-[#EFF4F1] disabled:text-[#90A39B]"
                                    />

                                    <input
                                        v-model="day.close"
                                        type="time"
                                        :disabled="!day.enabled"
                                        class="h-11 rounded-xl border border-[#D7E3DC] bg-white px-3 text-sm text-[#1E4138] outline-none disabled:cursor-not-allowed disabled:bg-[#EFF4F1] disabled:text-[#90A39B]"
                                    />

                                    <label class="hidden items-center justify-end gap-2 sm:inline-flex">
                                        <span class="text-sm font-medium text-[#61766F]">Open</span>
                                        <input
                                            v-model="day.enabled"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-[#B8CCC3] text-[#245C4A] focus:ring-[#245C4A]"
                                        />
                                    </label>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="grid gap-6">
                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <CheckCircle2 class="h-5 w-5 text-[#245C4A]" />
                                <h3 class="text-lg font-semibold text-[#183D34]">Store readiness</h3>
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

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <Settings2 class="h-5 w-5 text-[#245C4A]" />
                                <h3 class="text-lg font-semibold text-[#183D34]">Store preferences</h3>
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

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <Bell class="h-5 w-5 text-[#245C4A]" />
                                <h3 class="text-lg font-semibold text-[#183D34]">Notifications</h3>
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

                        <section class="rounded-[28px] border border-[#DCE8E1] bg-[#F7FAF8] p-5 shadow-sm">
                            <div class="mb-4 flex items-center gap-2">
                                <ShieldCheck class="h-5 w-5 text-[#245C4A]" />
                                <h3 class="text-lg font-semibold text-[#183D34]">Public preview</h3>
                            </div>

                            <div class="space-y-3 text-sm leading-6 text-[#6C8079]">
                                <p>
                                    Your store currently shows a
                                    <span class="font-semibold text-[#183D34]">{{ storeForm.pickupLeadTime }}</span>
                                    estimated lead time and a customer notice of
                                    <span class="font-semibold text-[#183D34]">{{ storeForm.orderNotice }}</span>.
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