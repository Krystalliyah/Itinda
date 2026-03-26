<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, ChevronDown, ShoppingCart } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/components/ui/dropdown-menu'

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));

const props = defineProps<{
  storeId: string
}>()

// Real Store Data
const store = ref({
  name: '',
  description: '',
  address: '',
  phone: '',
  hours: '',
  isOpen: false,
  cover: '',
  logo: '',
})

const loading = ref(true)
const error = ref<string | null>(null)

// Fetch store details from API
const fetchStoreDetails = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await fetch(`/customer/stores-data/${props.storeId}`, {
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
    store.value = {
      name: data.data.name || 'Unnamed Store',
      description: data.data.description || 'No description provided',
      address: data.data.address || 'No address provided',
      phone: data.data.phone || 'No phone provided',
      hours: data.data.hours || 'Mon - Fri: 8AM - 5PM',
      isOpen: data.data.isOpen || false,
      cover: data.data.cover !== 'NA' ? data.data.cover : `https://picsum.photos/1200/400?random=${props.storeId}`,
      logo: data.data.logo !== 'NA' ? data.data.logo : `https://ui-avatars.com/api/?name=${encodeURIComponent(data.data.name || 'Store')}`,
    }
  } catch (err) {
    console.error('Error fetching store details:', err)
    error.value = err instanceof Error ? err.message : 'Failed to load store details'
  } finally {
    loading.value = false
  }
}

// Real Products from API
const products = ref<any[]>([])
const productsLoading = ref(false)

// Fetch products from API
const fetchProducts = async () => {
  try {
    productsLoading.value = true
    
    const response = await fetch(`/customer/stores-data/${props.storeId}/products`, {
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
    products.value = []
  } finally {
    productsLoading.value = false
  }
}

const searchProduct = ref('')
const selectedCategory = ref<'all' | number>('all')
const sortBy = ref<'name' | 'price_low' | 'price_high'>('name')

const filteredProducts = computed(() => {
  let result = products.value.filter(product => {
    const matchesSearch = product.product_name.toLowerCase().includes(searchProduct.value.toLowerCase())
    const matchesCategory = selectedCategory.value === 'all' ? true : product.category_id === selectedCategory.value
    return matchesSearch && matchesCategory && product.is_active
  })

  if (sortBy.value === 'price_low') {
    result.sort((a, b) => a.unit_price - b.unit_price)
  } else if (sortBy.value === 'price_high') {
    result.sort((a, b) => b.unit_price - a.unit_price)
  } else {
    result.sort((a, b) => a.product_name.localeCompare(b.product_name))
  }

  return result
})

const cartOpen = ref(false)
const cartItems = ref([
  {
    id: 1,
    product_name: 'Organic Fuji Apples (1kg Pack)',
    description: 'Fresh imported apples.',
    image_url: 'https://picsum.photos/100?1',
    unit_price: 3.99,
    quantity: 1,
    selected: true,
  },
  {
    id: 2,
    product_name: 'Whole Fresh Milk 1L',
    description: 'Daily fresh milk.',
    image_url: 'https://picsum.photos/100?2',
    unit_price: 2.49,
    quantity: 2,
    selected: true,
  },
])

const total = computed(() =>
  cartItems.value
    .filter(item => item.selected)
    .reduce((sum, item) => sum + item.unit_price * item.quantity, 0)
)

const allSelected = computed({
  get: () => cartItems.value.every(item => item.selected),
  set: (value: boolean) => {
    cartItems.value.forEach(item => item.selected = value)
  }
})

const removeSelected = () => {
  cartItems.value = cartItems.value.filter(item => !item.selected)
}

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
        store_id: props.storeId,
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

onMounted(() => {
  fetchStoreDetails()
  fetchProducts()
})
</script>

<template>
  <Head title="Store Details" />

  <div class="dashboard-wrapper">
    <Header />
    <Sidebar role="customer">
      <CustomerNav />
      <template #icons>
        <CustomerNavIcons />
      </template>
    </Sidebar>

    <main :class="contentClass">
      <Head :title="store.name || 'Store Details'" />

      <!-- Loading State -->
      <div v-if="loading" class="max-w-6xl mx-auto px-6 pt-6">
        <div class="text-center py-12">
          <p class="text-muted-foreground">Loading store details...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="max-w-6xl mx-auto px-6 pt-6">
        <div class="text-center py-12">
          <p class="text-red-600 mb-4">{{ error }}</p>
          <Button @click="fetchStoreDetails" variant="outline">
            Try Again
          </Button>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="space-y-5 pb-12">
        <!-- Back Button -->
        <div class="max-w-6xl mx-auto px-6 pt-6">
          <Link href="/customer/stores">
            <Button variant="ghost" class="gap-2 text-[#245c4a]">
              <ArrowLeft class="w-4 h-4" />
              Back to Stores
            </Button>
          </Link>
        </div>

        <!-- Cover Section -->
        <div class="max-w-6xl mx-auto px-6">
          <div class="relative h-64 w-full overflow-hidden rounded-2xl">
            <img :src="store.cover" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
          </div>
        </div>

        <!-- Store Info -->
        <div class="max-w-6xl mx-auto px-6">
          <div class="p-8 space-y-6">
            <h1 class="text-3xl font-semibold text-[#245c4a]">{{ store.name }}</h1>

            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <img :src="store.logo" class="h-14 w-14 rounded-xl shadow-md object-cover border" />
                <div>
                  <p class="text-base text-muted-foreground">{{ store.address }}</p>
                  <p class="text-sm text-muted-foreground">{{ store.phone }}</p>
                </div>
              </div>
              <span
                class="px-4 py-1.5 text-sm rounded-full font-medium"
                :class="store.isOpen ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >
                {{ store.isOpen ? 'Open' : 'Closed' }}
              </span>
            </div>

            <p v-if="store.description" class="text-base leading-relaxed text-muted-foreground">
              {{ store.description }}
            </p>

            <div class="pt-4 border-t border-border">
              <p class="text-sm text-muted-foreground">
                <span class="font-medium text-foreground">Operating Hours:</span>
                {{ store.hours }}
              </p>
            </div>
          </div>
        </div>

        <!-- Products Section -->
        <div class="max-w-6xl mx-auto px-6">
          <h2 class="text-xl font-semibold text-[#245c4a] mb-6">Products</h2>

          <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between mb-6">
            <Input v-model="searchProduct" placeholder="Search products..." class="w-full lg:max-w-sm" />

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
                  <DropdownMenuItem @click="selectedCategory = 'all'">All</DropdownMenuItem>
                  <DropdownMenuItem @click="selectedCategory = 1">Fruits</DropdownMenuItem>
                  <DropdownMenuItem @click="selectedCategory = 2">Dairy</DropdownMenuItem>
                  <DropdownMenuItem @click="selectedCategory = 3">Snacks</DropdownMenuItem>
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
                  <DropdownMenuItem @click="sortBy = 'name'">Name</DropdownMenuItem>
                  <DropdownMenuItem @click="sortBy = 'price_low'">Price: Low to High</DropdownMenuItem>
                  <DropdownMenuItem @click="sortBy = 'price_high'">Price: High to Low</DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
            <Card
              v-for="product in filteredProducts"
              :key="product.id"
              class="group rounded-xl border border-border bg-white dark:bg-muted shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden cursor-pointer p-0"
            >
              <div class="relative w-full aspect-square overflow-hidden bg-gray-100">
                <img :src="product.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                <span v-if="!product.is_available" class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-xl">
                  Sold Out
                </span>
              </div>

              <CardContent class="space-y-2">
                <h3 class="text-sm font-medium text-foreground line-clamp-2 min-h-[40px]">{{ product.product_name }}</h3>

                <div class="flex items-center justify-between">
                  <span class="text-lg font-semibold text-[#245c4a]">${{ product.unit_price.toFixed(2) }}</span>
                  <span v-if="product.stock_level <= 5 && product.stock_level > 0" class="text-xs text-orange-600">
                    Low stock
                  </span>
                </div>

                <div class="flex items-center justify-between text-xs text-muted-foreground">
                  <div class="flex items-center gap-1">
                    <span>⭐</span>
                    <span>{{ product.rating }}</span>
                  </div>
                  <span>{{ product.sold_count }} sold</span>
                </div>

                <Button size="sm" :disabled="!product.is_available" class="w-full mt-2 mb-4 bg-[#245c4a] hover:bg-[#1B4D3E] text-white" @click.stop="addToCart(product)">
                  Add to Cart
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Cart Button -->
      <div @click="cartOpen = true" class="fixed bottom-6 right-6 z-50 cursor-pointer">
        <div class="relative h-14 w-14 rounded-full bg-[#245c4a] hover:bg-[#1B4D3E] text-white flex items-center justify-center shadow-lg hover:shadow-xl transition">
          <ShoppingCart class="h-5 w-5" />
        </div>
        <span v-if="cartItems.length" class="absolute -top-1 -right-1 bg-[#C5A059] text-white text-xs h-5 w-5 rounded-full flex items-center justify-center">
          {{ cartItems.length }}
        </span>
      </div>

      <!-- Cart Modal -->
      <transition name="fade">
        <div v-if="cartOpen" class="fixed inset-0 z-40 bg-black/40" @click="cartOpen = false" />
      </transition>

      <transition name="slide">
        <div v-if="cartOpen" class="fixed top-20 right-6 w-[380px] max-h-[80vh] bg-white dark:bg-muted rounded-2xl shadow-xl border border-border z-50 overflow-hidden flex flex-col">
          <div class="flex items-center justify-between p-4 border-b border-border">
            <div class="flex items-center gap-2">
              <ShoppingCart class="h-6 w-6 text-[#245c4a]" />
              <h3 class="font-semibold text-[#245c4a]">Cart</h3>
            </div>
            <button @click="cartOpen = false">✕</button>
          </div>

          <div class="px-4 py-2 text-sm text-muted-foreground border-b border-border">{{ store.name }}</div>

          <div class="flex items-center justify-between px-4 py-2 border-b border-border">
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" v-model="allSelected" />
              Select All
            </label>
            <button class="text-xs text-red-500 hover:underline" @click="removeSelected">
              Remove Selected
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <div v-for="item in cartItems" :key="item.id" class="border rounded-xl p-3 space-y-2">
              <div class="flex gap-3">
                <input type="checkbox" v-model="item.selected" />
                <img :src="item.image_url" class="h-14 w-14 rounded-lg object-cover" />
                <div class="flex-1">
                  <p class="text-sm font-medium line-clamp-1">{{ item.product_name }}</p>
                  <p class="text-xs text-muted-foreground line-clamp-1">{{ item.description }}</p>
                  <p class="text-sm font-semibold text-[#245c4a]">${{ item.unit_price }}</p>
                </div>
              </div>

              <div class="flex items-center justify-between pt-2">
                <button class="text-xs text-red-500 hover:underline" @click="cartItems = cartItems.filter(i => i.id !== item.id)">
                  Remove
                </button>

                <div class="flex items-center gap-2 text-sm">
                  <button class="h-7 w-7 border rounded-xl flex items-center justify-center hover:bg-muted transition" @click="item.quantity > 1 && item.quantity--">
                    -
                  </button>
                  <span class="min-w-[20px] text-center">{{ item.quantity }}</span>
                  <button class="h-7 w-7 border rounded-xl flex items-center justify-center hover:bg-muted transition" @click="item.quantity++">
                    +
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="border-t border-border p-4 space-y-3">
            <div class="flex justify-between font-semibold">
              <span>Total</span>
              <span class="text-[#245c4a]">${{ total.toFixed(2) }}</span>
            </div>

            <Link href="/customer/cart" class="block text-sm text-[#245c4a] hover:underline">
              View all cart
            </Link>

            <Button class="w-full">Pre-order</Button>
          </div>
        </div>
      </transition>
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
