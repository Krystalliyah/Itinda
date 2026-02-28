<script setup lang="ts">
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const auth = computed(() => page.props.auth as { user: any; hasStore: boolean; storeIsApproved: boolean; });

const showBanner = computed(() => {
    const user = auth.value?.user;
    const hasStore = auth.value?.hasStore;
    const storeIsApproved = auth.value?.storeIsApproved;
    
    // In our new architecture, Spatie roles are arrays, but user.role might still exist depending on the frontend payload. 
    // We check if it is a vendor. HandleInertiaRequests sends standard $user object.
    const isVendor = user?.roles?.some(r => r.name === 'vendor') || user?.role === 'vendor';
    
    return isVendor && (!hasStore || !storeIsApproved);
});

const isPending = computed(() => auth.value.hasStore && !auth.value.storeIsApproved);
</script>

<template>
    <div v-if="showBanner" :class="isPending ? 'bg-gradient-to-r from-amber-50 to-orange-50 border-amber-200' : 'bg-gradient-to-r from-emerald-50 to-teal-50 border-emerald-200'" class="border-b">
        <div class="px-4 sm:px-6 py-3">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div :class="isPending ? 'bg-amber-600' : 'bg-emerald-700'" class="w-10 h-10 rounded-lg flex items-center justify-center">
                        <svg v-if="isPending" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-gray-900">{{ isPending ? 'Store Awaiting Approval' : 'Complete Your Store Setup' }}</h3>
                    <p class="text-xs text-gray-600 mt-0.5">
                        {{ isPending ? 'Your store logic is currently under review by our team. Check back soon.' : "You're in preview mode. Complete the setup on the Dashboard to unlock all features." }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
