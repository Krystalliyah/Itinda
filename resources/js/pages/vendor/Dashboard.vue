<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import VendorLayout from '@/layouts/VendorLayout.vue';
import InputError from '@/components/InputError.vue';

const page = usePage();
const auth = computed(() => page.props.auth as { user: any; hasStore: boolean; storeIsApproved: boolean; });
const hasStore = computed(() => auth.value.hasStore);
const storeIsApproved = computed(() => auth.value.storeIsApproved);

// Tenant info and products from backend
const tenantInfo = computed(() => (page.props as any).tenantInfo);
const products = computed(() => (page.props as any).products || []);
const productCount = computed(() => (page.props as any).productCount || 0);

const showModal = ref(false);

const form = useForm({
    store_name: '',
    domain_slug: '',
    address: '',
    city: '',
    phone: '',
    operating_hours: '',
});

function openModal() {
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
}

function submit() {
    form.post('/vendor/store/setup', {
        onSuccess: () => {
            closeModal();
        }
    });
}
</script>

<template>
    <Head title="Vendor Dashboard" />

    <VendorLayout>
        <div class="p-6">
            <!-- Tenant Database Info (Debug) -->
            <div v-if="tenantInfo" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-bold text-blue-900 mb-2">🎯 Tenant Database Info</h3>
                <div class="text-sm text-blue-800 space-y-1">
                    <p><strong>Tenant ID:</strong> {{ tenantInfo.id }}</p>
                    <p><strong>Store Name:</strong> {{ tenantInfo.name }}</p>
                    <p><strong>Database:</strong> {{ tenantInfo.database }}</p>
                    <p><strong>Products in DB:</strong> {{ productCount }}</p>
                </div>
                
                <div v-if="products.length > 0" class="mt-3 pt-3 border-t border-blue-200">
                    <p class="font-semibold text-blue-900 mb-2">Products from THIS tenant's database:</p>
                    <ul class="space-y-1 text-sm text-blue-800">
                        <li v-for="product in products" :key="product.id" class="flex justify-between">
                            <span>{{ product.name }}</span>
                            <span class="font-mono">₱{{ product.price }} (Stock: {{ product.stock }})</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Without Store: Setup Prompt -->
            <div v-if="!hasStore" class="max-w-3xl mx-auto">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-emerald-700 px-6 py-6 text-center">
                        <div class="w-14 h-14 bg-emerald-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h1 class="text-xl font-bold text-white mb-1" style="color: #ffffff !important;">Complete Your Store Setup</h1>
                        <p class="text-sm font-medium" style="color: #ffffff !important; opacity: 0.95;">Get started by adding your store information</p>
                    </div>

                    <!-- Features -->
                    <div class="px-6 py-5 space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">Manage Your Inventory</h3>
                                <p class="text-sm text-gray-600">Track products, stock levels, and pricing</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">Accept Online Orders</h3>
                                <p class="text-sm text-gray-600">Customers can browse and pre-order</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">Track Your Sales</h3>
                                <p class="text-sm text-gray-600">View analytics and insights</p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="px-6 pb-5">
                        <button
                            @click="openModal"
                            class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Setup Store
                        </button>
                        <p class="text-center text-sm text-gray-500 mt-2">Less than 2 minutes to complete</p>
                    </div>
                </div>
            </div>

            <!-- Pending Approval Prompt -->
            <div v-else-if="hasStore && !storeIsApproved" class="max-w-3xl mx-auto">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden text-center p-10">
                    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Awaiting Approval</h2>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        Your store registration has been submitted and is currently being reviewed by our administrators. 
                        We will provision your dedicated store space as soon as it is approved!
                    </p>
                    <div class="inline-flex items-center justify-center gap-2 text-sm font-medium text-amber-700 bg-amber-50 px-4 py-2 rounded-full border border-amber-200">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Pending Review
                    </div>
                </div>
            </div>

            <!-- With Store: Dashboard Content -->
            <div v-else>
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-sm text-gray-600 mt-1">Welcome back! Here's your store overview.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg p-5 shadow-sm border border-gray-200">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-gray-700 text-xs font-semibold uppercase tracking-wide mb-1">Total Sales</h3>
                        <p class="text-2xl font-bold text-gray-900">₱0</p>
                    </div>
                    <div class="bg-white rounded-lg p-5 shadow-sm border border-gray-200">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <h3 class="text-gray-700 text-xs font-semibold uppercase tracking-wide mb-1">Orders</h3>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                    <div class="bg-white rounded-lg p-5 shadow-sm border border-gray-200">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-gray-700 text-xs font-semibold uppercase tracking-wide mb-1">Products</h3>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                    <div class="bg-white rounded-lg p-5 shadow-sm border border-gray-200">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-gray-700 text-xs font-semibold uppercase tracking-wide mb-1">Customers</h3>
                        <p class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <a href="/vendor/products" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 transition group">
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition">
                                <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">Add Products</h3>
                                <p class="text-sm text-gray-600">Build your inventory</p>
                            </div>
                        </a>
                        <a href="/vendor/orders" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition group">
                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition">
                                <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">View Orders</h3>
                                <p class="text-sm text-gray-600">Manage orders</p>
                            </div>
                        </a>
                        <a href="/vendor/store-settings" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition group">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition">
                                <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">Store Settings</h3>
                                <p class="text-sm text-gray-600">Update details</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== STORE SETUP MODAL ===================== -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>

                <!-- Modal Panel -->
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">

                    <!-- Emerald Header -->
                    <div class="bg-emerald-700 px-6 pt-5 pb-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold" style="color: #ffffff !important;">Setup Your Store</h3>
                                    <p class="text-xs mt-0.5" style="color: rgba(255,255,255,0.8) !important;">Fill in the details below to get started</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="closeModal"
                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex-shrink-0"
                                style="color: #ffffff !important;"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Form Body -->
                    <form @submit.prevent="submit" class="px-6 py-5 space-y-4 bg-white">

                        <!-- Store Name -->
                        <div>
                            <label for="store_name" class="block text-sm font-semibold text-gray-800 mb-1.5">
                                Store Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="store_name"
                                v-model="form.store_name"
                                type="text"
                                required
                                placeholder="e.g. Maria's Sari-Sari Store"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                            />
                            <InputError :message="form.errors.store_name" class="mt-1" />
                        </div>

                        <!-- Domain Slug -->
                        <div>
                            <label for="domain_slug" class="block text-sm font-semibold text-gray-800 mb-1.5">
                                Web Address (Domain Alias) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <input
                                    id="domain_slug"
                                    v-model="form.domain_slug"
                                    type="text"
                                    required
                                    placeholder="mariastore"
                                    class="w-full rounded-l-xl border border-gray-200 border-r-0 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                                />
                                <span class="inline-flex items-center px-3 py-2.5 rounded-r-xl border border-l-0 border-gray-200 bg-gray-100 text-gray-500 text-sm">
                                    .storekoto.test
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Letters, numbers, and hyphens only.</p>
                            <InputError :message="form.errors.domain_slug" class="mt-1" />
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-semibold text-gray-800 mb-1.5">
                                Address <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                required
                                placeholder="e.g. 123 Main Street, Barangay San Isidro"
                                rows="2"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition resize-none"
                            ></textarea>
                            <InputError :message="form.errors.address" class="mt-1" />
                        </div>

                        <!-- City + Phone row -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="city" class="block text-sm font-semibold text-gray-800 mb-1.5">City</label>
                                <input
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    placeholder="e.g. Quezon City"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                                />
                                <InputError :message="form.errors.city" class="mt-1" />
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-800 mb-1.5">Phone</label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="+63 912 345 6789"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                                />
                                <InputError :message="form.errors.phone" class="mt-1" />
                            </div>
                        </div>

                        <!-- Operating Hours -->
                        <div>
                            <label for="operating_hours" class="block text-sm font-semibold text-gray-800 mb-1.5">
                                Operating Hours
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <input
                                    id="operating_hours"
                                    v-model="form.operating_hours"
                                    type="text"
                                    placeholder="e.g. Mon–Sat 8AM–8PM"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                                />
                            </div>
                            <InputError :message="form.errors.operating_hours" class="mt-1" />
                        </div>

                        <!-- Actions -->
                        <div class="border-t border-gray-100 pt-3 flex items-center justify-end gap-2">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 disabled:opacity-60 disabled:cursor-not-allowed rounded-xl transition-colors flex items-center gap-2"
                            >
                                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ form.processing ? 'Creating...' : 'Create Store' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

    </VendorLayout>
</template>