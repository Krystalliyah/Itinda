<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/components/ui/dropdown-menu'
import { ChevronDown } from 'lucide-vue-next'
import {
  Avatar,
  AvatarImage,
  AvatarFallback
} from '@/components/ui/avatar'


const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));

interface Store {
  id: number
  name: string
  address: string
  phone: string
  hours: string
  isOpen: boolean
  logo?: string
  cover?: string
}

// Mock Data
const stores = ref<Store[]>([
  {
    id: 1,
    name: 'Emerald Fresh Market',
    address: '123 Green Valley Rd, NY',
    phone: '(123) 456-7890',
    hours: 'Mon - Fri: 8AM - 8PM',
    isOpen: true,
    logo: '',
    cover: 'https://picsum.photos/600/400?random=1',
  },
  {
    id: 2,
    name: 'Golden Harvest Grocery',
    address: '45 Sunset Blvd, CA',
    phone: '(987) 654-3210',
    hours: 'Daily: 9AM - 6PM',
    isOpen: false,
    logo: '',
    cover: 'https://picsum.photos/600/400?random=2',
  },
  {
    id: 3,
    name: 'Urban Organic Hub',
    address: '78 City Center Ave, TX',
    phone: '(555) 234-5678',
    hours: 'Mon - Sat: 7AM - 9PM',
    isOpen: true,
    logo: '',
    cover: 'https://picsum.photos/600/400?random=3',
  },
])

const search = ref('')
const searchBy = ref<'name' | 'address'>('name')

const filterStatus = ref<'all' | 'open'>('all')

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
                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-md hover:bg-muted transition"
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

            <!-- Store Grid -->
            <div
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
            v-if="filteredStores.length === 0"
            class="text-center py-12 text-muted-foreground"
            >
            No stores found.
            </div>
        </div>
        </main>
    </div>
</template>
