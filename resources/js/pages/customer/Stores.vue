<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ChevronDown, Store, MapPin } from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue';
import Header from '@/components/Header.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import Sidebar from '@/components/Sidebar.vue';
import {
  Avatar,
  AvatarImage,
  AvatarFallback
} from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));

interface Store {
  id: string
  name: string
  address: string
  phone: string
  hours: string
  isOpen: boolean
  logo?: string
  cover?: string
}

// Real data from API
const stores = ref<Store[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

const search = ref('')
const searchBy = ref<'name' | 'address'>('name')
const filterStatus = ref<'all' | 'open'>('all')

// Fetch stores from API
const fetchStores = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await fetch('/customer/stores-data', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    
    // Map API response to component interface
    stores.value = data.data.map((store: any) => ({
      id: store.id,
      name: store.name || 'Unnamed Store',
      address: store.address || 'No address provided',
      phone: store.phone || 'No phone provided',
      hours: store.hours || 'Mon - Fri: 8AM - 5PM',
      isOpen: store.isOpen || false,
      logo: store.logo !== 'NA' ? store.logo : undefined,
      cover: store.cover !== 'NA' ? store.cover : `https://picsum.photos/600/400?random=${store.id}`,
    }))
  } catch (err) {
    console.error('Error fetching stores:', err)
    error.value = err instanceof Error ? err.message : 'Failed to load stores'
  } finally {
    loading.value = false
  }
}

const filteredStores = computed(() => {
  return stores.value.filter(store => {
    const valueToSearch =
      searchBy.value === 'name'
        ? store.name
        : store.address

    const matchesSearch = valueToSearch
      .toLowerCase()
      .includes(search.value.toLowerCase())

    const matchesOpen =
      filterStatus.value === 'open'
        ? store.isOpen
        : true

    return matchesSearch && matchesOpen
  })
})

// Load stores on component mount
onMounted(() => {
  fetchStores()
})
</script>

<template>
    <Head title="Find Stores" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="customer">
            <CustomerNav />
            <template #icons>
                <CustomerNavIcons />
            </template>
        </Sidebar>

        <main :class="contentClass">
            <Head title="Stores" />

        <div class="p-6 space-y-6">
            <!-- Page Title -->
            <div>
            <h1 class="text-2xl font-semibold text-[#245c4a]">
                Find Stores
            </h1>
            <p class="text-muted-foreground">
                Browse available stores near you
            </p>
            </div>

            <!-- Search + Filter -->
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex w-full max-w-1xl gap-3">

                <DropdownMenu>
                    <div class="relative w-full max-w-3xl cursor-pointer">
                    
                        <!-- Input -->
                        <Input
                            v-model="search"
                            :placeholder="`Search by ${searchBy}...`"
                            class="pr-10"
                        />
                        <DropdownMenuTrigger as-child>
                            <button
                                type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-xl hover:bg-muted transition"
                            >
                                <ChevronDown class="w-4 h-4 opacity-60" />
                            </button>
                        </DropdownMenuTrigger>
                    </div>
                

                <DropdownMenuContent align="end" class="w-40">
                    <DropdownMenuItem @click="searchBy = 'name'">
                    Search by Name
                    </DropdownMenuItem>

                    <DropdownMenuItem @click="searchBy = 'address'">
                    Search by Address
                    </DropdownMenuItem>
                </DropdownMenuContent>
                </DropdownMenu>

                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="min-w-[160px] justify-between">
                            <span>{{ filterStatus === 'open' ? 'Open Now' : 'All Stores' }}</span>
                            <ChevronDown class="w-4 h-4 opacity-60" />
                        </Button>
                    </DropdownMenuTrigger>

                    <DropdownMenuContent align="end" class="w-40">
                        <DropdownMenuItem @click="filterStatus = 'all'">
                        All Stores
                        </DropdownMenuItem>

                        <DropdownMenuItem @click="filterStatus = 'open'">
                        Open Now
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <!-- ── Loading State ── -->
            <div v-if="loading" class="state-center">
                <div class="elegant-loader">
                    <div class="loader-ring loader-ring--outer"></div>
                    <div class="loader-ring loader-ring--inner"></div>
                    <div class="loader-core"></div>
                </div>
                <p class="loading-text">Finding stores for you…</p>
            </div>

            <!-- ── Error State ── -->
            <div v-else-if="error" class="state-center">
                <p class="text-red-500 font-medium mb-4">{{ error }}</p>
                <Button @click="fetchStores" variant="outline" class="rounded-xl">
                    Try Again
                </Button>
            </div>

            <div
                v-else
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
            >
            <Card
                v-for="store in filteredStores"
                :key="store.id"
                class="rounded-xl shadow-sm hover:shadow-md transition-all duration-200 border border-border"
            >

                <!-- Cover Image -->
                <div class="relative h-40 w-full overflow-hidden">
                    <img
                    :src="store.cover"
                    alt="Store cover"
                    class="h-full w-full object-cover"
                    />

                    <!-- Optional gradient overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                </div>

                <CardHeader>
                    <div class="flex items-center gap-3">
                        
                        <!-- Logo -->
                        <Avatar class="h-10 w-10">
                        <AvatarImage v-if="store.logo" :src="store.logo" />
                        <AvatarFallback class="bg-[#245c4a] text-white text-sm font-medium">
                            {{ store.name.slice(0, 2).toUpperCase() }}
                        </AvatarFallback>
                        </Avatar>

                        <!-- Store Name -->
                        <CardTitle class="text-lg text-[#245c4a]">
                        {{ store.name }}
                        </CardTitle>

                    </div>
                </CardHeader>

                <CardContent class="space-y-2 text-sm">
                <p>
                    <span class="font-medium">Address:</span>
                    {{ store.address }}
                </p>

                <p>
                    <span class="font-medium">Phone:</span>
                    {{ store.phone }}
                </p>

                <p>
                    <span class="font-medium">Hours:</span>
                    {{ store.hours }}
                </p>

                <div
                    class="inline-block mt-2 px-3 py-1 text-xs rounded-full"
                    :class="store.isOpen
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'"
                >
                    {{ store.isOpen ? 'Open Now' : 'Closed' }}
                </div>
                
                <div class="flex justify-end pt-4">
                    <Button
                    size="sm"
                    class="bg-[#245c4a] hover:bg-[#1B4D3E] text-white"
                    @click="$inertia.visit(`/customer/stores/${store.id}`)"
                    >
                    Visit Store →
                    </Button>
                </div>
                </CardContent>
            </Card>
            </div>

            <!-- Empty State -->
            <div
                v-if="!loading && !error && filteredStores.length === 0"
                class="empty-state"
            >
                <div class="empty-icon">
                    <Store class="h-10 w-10" />
                </div>
                <p class="empty-title">No stores found</p>
                <p class="empty-sub">Try adjusting your search or status filter</p>
            </div>
        </div>
        </main>
    </div>
</template>

<style scoped>
.dashboard-content {
    margin-left: 256px;
    transition: margin-left 0.3s ease;
    min-height: 100vh;
}
.dashboard-content.sidebar-collapsed {
    margin-left: 80px;
}
@media (max-width: 767px) {
    .dashboard-content { margin-left: 0; }
}

/* ── States ── */
.state-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    gap: 20px;
}

.elegant-loader {
    position: relative;
    width: 56px;
    height: 56px;
}

.loader-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid transparent;
    animation: elegant-spin linear infinite;
}

.loader-ring--outer {
    border-top-color: #245c4a;
    animation-duration: 1.2s;
}
.dark .loader-ring--outer { border-top-color: #34d399; }

.loader-ring--inner {
    inset: 10px;
    border-bottom-color: #F7E8C6;
    animation-duration: 0.9s;
    animation-direction: reverse;
}

.loader-core {
    position: absolute;
    inset: 20px;
    border-radius: 50%;
    background: #245c4a;
    opacity: 0.2;
    animation: core-pulse 1.5s ease-in-out infinite;
}
.dark .loader-core { background: #34d399; }

@keyframes elegant-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
@keyframes core-pulse {
    0%, 100% { transform: scale(0.8); opacity: 0.2; }
    50%       { transform: scale(1.1);  opacity: 0.4; }
}

.loading-text {
    font-size: 0.875rem;
    color: var(--muted-foreground);
    letter-spacing: 0.02em;
}

/* Empty state */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 32px;
    gap: 8px;
    margin-top: 2rem;
}
.empty-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(36, 92, 74, 0.08);
    border-radius: 20px;
    color: #245c4a;
    margin-bottom: 12px;
}
.dark .empty-icon { background: rgba(52, 211, 153, 0.1); color: #34d399; }
.empty-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--foreground);
    margin: 0;
}
.empty-sub {
    font-size: 0.875rem;
    color: var(--muted-foreground);
    margin: 0;
}
</style>