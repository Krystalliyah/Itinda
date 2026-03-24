<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { watchDebounced } from '@vueuse/core';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { ChevronDown, ShoppingCart } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/components/ui/dropdown-menu'

const { isCollapsed } = useSidebar();
const page = usePage();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));

// Real Products from API
const products = ref<any[]>([])
const categories = ref<any[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

// Filter state - declare before using in functions
const searchProduct = ref('')
const selectedCategory = ref<'all' | number>('all')
const sortBy = ref<'name' | 'price_low' | 'price_high'>('name')

// Fetch all categories from API
const fetchCategories = async () => {
  try {
    const response = await fetch('/customer/categories', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      categories.value = data.data || []
    }
  } catch (err) {
    console.error('Error fetching categories:', err)
  }
}

// Fetch all products from all stores via API with filters
const fetchAllProducts = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Build query parameters
    const params = new URLSearchParams()
    
    if (searchProduct.value) {
      params.append('search', searchProduct.value)
    }
    
    if (selectedCategory.value !== 'all') {
      params.append('category_id', selectedCategory.value.toString())
    }
    
    if (sortBy.value) {
      params.append('sort_by', sortBy.value)
    }
    
    const queryString = params.toString()
    const url = `/customer/products-data${queryString ? '?' + queryString : ''}`
    
    const response = await fetch(url, {
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
    products.value = data.data || []
    
  } catch (err) {
    console.error('Error fetching products:', err)
    error.value = err instanceof Error ? err.message : 'Failed to load products'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCategories()
  fetchAllProducts()
})

// Watch for filter changes and refetch (debounced for search)
watchDebounced(searchProduct, () => {
  fetchAllProducts()
}, { debounce: 300 })

watch([selectedCategory, sortBy], () => {
  fetchAllProducts()
})

const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null)
let toastTimer: ReturnType<typeof setTimeout> | null = null

const showToast = (message: string, type: 'success' | 'error' = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { message, type }
  toastTimer = setTimeout(() => { toast.value = null }, 3000)
}

const addToCart = async (product: any) => {
  try {
    const response = await fetch('/customer/cart/add', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      },
      body: JSON.stringify({
        store_id: product.store.id,
        product_id: product.id,
        quantity: 1,
      }),
    })

    if (!response.ok) throw new Error('Failed to add to cart')
    showToast(`"${product.product_name}" added to cart!`)
  } catch (err) {
    console.error(err)
    showToast('Failed to add to cart. Please try again.', 'error')
  }
}

// Products are already filtered and sorted by the API
const filteredProducts = computed(() => products.value)

</script>

<template>
    <Head title="Browse Products" />

    <div class="dashboard-wrapper">
        <Header />
        <Sidebar role="customer">
            <CustomerNav />
            <template #icons>
                <CustomerNavIcons />
            </template>
        </Sidebar>

        <main :class="contentClass">
            <Head title="Products" />

            <div class="p-6 space-y-6">
                <!-- Page Title -->
                <div>
                <h1 class="text-2xl font-semibold text-[#245c4a]">
                    Browse Products
                </h1>
                <p class="text-muted-foreground">
                    Browse products by category, compare prices across vendors, and view product availability.
                </p>
                </div>

                <!-- Search + Filter -->
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-3">

                    <!-- Search -->
                    <Input
                        v-model="searchProduct"
                        placeholder="Search products..."
                        class="w-full lg:max-w-sm"
                    />

                    <div class="flex gap-3 w-full lg:w-auto">

                        <!-- Category Filter -->
                        <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="w-36 justify-between">
                            Category
                            <ChevronDown class="w-4 h-4 opacity-60" />
                            </Button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent>
                            <DropdownMenuItem @click="selectedCategory = 'all'">
                            All
                            </DropdownMenuItem>
                            <DropdownMenuItem
                            v-for="category in categories"
                            :key="category.id"
                            @click="selectedCategory = category.id"
                            >
                            {{ category.name }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                        </DropdownMenu>

                        <!-- Sort -->
                        <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="w-30 justify-between">
                            Sort
                            <ChevronDown class="w-4 h-4 opacity-60" />
                            </Button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent>
                            <DropdownMenuItem @click="sortBy = 'name'">
                            Name
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="sortBy = 'price_low'">
                            Price: Low to High
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="sortBy = 'price_high'">
                            Price: High to Low
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                        </DropdownMenu>

                    </div>
                </div>

                <!-- Product Grid -->
                <div
                v-if="loading"
                class="text-center py-12 text-muted-foreground"
                >
                Loading products...
                </div>

                <div
                v-else-if="error"
                class="text-center py-12 text-red-600"
                >
                {{ error }}
                </div>

                <div
                v-else
                class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6"
                >
                <Card
                v-for="product in filteredProducts"
                :key="product.id"
                class="group rounded-xl border border-border bg-white dark:bg-muted shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden cursor-pointer p-0"
                >

                <!-- Image Section -->
                <div class="relative w-full aspect-square overflow-hidden bg-gray-100">

                    <img
                    :src="product.image_url"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />

                    <!-- Top Badge -->
                    <span
                    v-if="!product.is_available"
                    class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-xl"
                    >
                    Sold Out
                    </span>

                </div>


                <!-- Content -->
                <CardContent class="space-y-2">

                    <!-- Store Info -->
                    <Link
                        :href="`/customer/stores/${product.store.id}`"
                        class="flex items-center gap-2 text-xs text-muted-foreground hover:text-[#245c4a] transition"
                    >
                        <img
                        :src="product.store.logo"
                        class="h-5 w-5 rounded-full object-cover"
                        />
                        <span class="line-clamp-1">
                        {{ product.store.name }}
                        </span>
                    </Link>

                    <!-- Product Name -->
                    <h3 class="text-sm font-medium text-foreground line-clamp-2 min-h-[40px]">
                    {{ product.product_name }}
                    </h3>

                    <!-- Price -->
                    <div class="flex items-center justify-between">

                    <span class="text-lg font-semibold text-[#245c4a]">
                        ${{ product.unit_price.toFixed(2) }}
                    </span>

                    <span
                        v-if="product.stock_level <= 5 && product.stock_level > 0"
                        class="text-xs text-orange-600"
                    >
                        Low stock
                    </span>

                    </div>

                    <!-- Rating + Sold -->
                    <div class="flex items-center justify-between text-xs text-muted-foreground">

                    <div class="flex items-center gap-1">
                        <span>⭐</span>
                        <span>{{ product.rating }}</span>
                    </div>

                    <span>{{ product.sold_count }} sold</span>

                    </div>

                    <!-- Add Button -->
                    <Button
                    size="sm"
                    class="w-full mt-2 mb-4 bg-[#245c4a] hover:bg-[#1B4D3E] text-white"
                    @click="addToCart(product)"
                    >
                    {{ product.is_available ? 'Add to Cart' : 'Add to Cart (Low Stock)' }}
                    </Button>

                </CardContent>

                </Card>
                </div>

                <!-- Empty State -->
                <div
                v-if="filteredProducts.length === 0"
                class="text-center py-12 text-muted-foreground"
                >
                No products found.
                </div>
            </div>
            
            <Link
            href="/customer/cart"
            class="fixed bottom-6 right-6 z-50"
            >
            <div
                class="h-14 w-14 rounded-full bg-[#245c4a] hover:bg-[#1B4D3E] text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1"
            >
                <ShoppingCart class="h-5 w-5" />
            </div>
            
            </Link>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
      >
        <div
          v-if="toast"
          class="fixed bottom-24 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3 px-5 py-3 rounded-2xl shadow-xl text-sm font-medium"
          :class="toast.type === 'success'
            ? 'bg-[#245c4a] text-white'
            : 'bg-red-600 text-white'"
        >
          <span v-if="toast.type === 'success'" class="text-lg">🛒</span>
          <span v-else class="text-lg">⚠️</span>
          {{ toast.message }}
          <button
            class="ml-2 opacity-70 hover:opacity-100 transition-opacity"
            @click="toast = null"
          >✕</button>
        </div>
      </Transition>
    </Teleport>
</template>