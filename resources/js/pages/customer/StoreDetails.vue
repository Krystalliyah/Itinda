<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, reactive, watch, nextTick } from 'vue';
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

const products = ref<any[]>([])
const productsLoading = ref(false)

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

const quantities = reactive<Record<number, number>>({})

const getQty = (product: any): number => quantities[product.id] ?? 1

const setQty = (product: any, val: number) => {
  const max = product.stock_level > 0 ? product.stock_level : Infinity
  quantities[product.id] = Math.min(Math.max(1, val), max)
}

const increment = (product: any) => setQty(product, getQty(product) + 1)
const decrement = (product: any) => setQty(product, getQty(product) - 1)

const cartItems = ref<any[]>([])
const storeCartModalOpen = ref(false)
const selectedStoreItemIds = ref<number[]>([])

// Fixed: Compare as strings and ensure reactivity
const storeCartItems = computed(() => {
  const filtered = cartItems.value.filter(item => String(item.store_id) === String(props.storeId))
  return filtered
})

const storeCartTotal = computed(() =>
  storeCartItems.value.reduce((sum, item) => sum + Number(item.unit_price) * item.quantity, 0)
)

const storeAllSelected = computed(() =>
  storeCartItems.value.length > 0 &&
  storeCartItems.value.every(item => selectedStoreItemIds.value.includes(item.id))
)

const storeSelectedTotal = computed(() =>
  storeCartItems.value
    .filter(item => selectedStoreItemIds.value.includes(item.id))
    .reduce((sum, item) => sum + Number(item.unit_price) * item.quantity, 0)
)

const toggleStoreAll = (val: boolean) => {
  selectedStoreItemIds.value = val ? storeCartItems.value.map(item => item.id) : []
}

// Watch for cart items changes
watch(() => cartItems.value, (newVal) => {
  console.log('cartItems updated:', newVal.length, 'items')
  console.log('Filtered store items:', storeCartItems.value.length)
}, { deep: true })

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount)

const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null)
let toastTimer: ReturnType<typeof setTimeout> | null = null

const showToast = (message: string, type: 'success' | 'error' = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { message, type }
  toastTimer = setTimeout(() => { toast.value = null }, 3000)
}

const addToCart = async (product: any) => {
  const qty = getQty(product)
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
        quantity: qty,
      }),
    })

    if (!response.ok) throw new Error('Failed to add to cart')
    quantities[product.id] = 1
    showToast(`${qty}× "${product.product_name}" added to cart!`)
    
    await fetchCartItems()
    // Small delay to ensure reactivity updates
    await nextTick()
    storeCartModalOpen.value = true
  } catch (err) {
    console.error(err)
    showToast('Failed to add to cart. Please try again.', 'error')
  }
}

const fetchCartItems = async () => {
  try {
    const response = await fetch('/customer/cart-data', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (response.ok) {
      const data = await response.json()
      const items: any[] = []
      data.data.forEach((storeCart: any) => {
        storeCart.items.forEach((item: any) => {
          items.push({
            id: item.id,
            store_id: String(storeCart.store_id), // Store as string for consistent comparison
            product_name: item.product.name,
            unit_price: Number(item.product.price) || 0,
            image_path: item.product.image_path,
            quantity: item.quantity,
          })
        })
      })
      cartItems.value = items
    }
  } catch (err) {
    console.error('Error fetching cart:', err)
  }
}

const removeCartItem = async (itemId: number) => {
  try {
    const response = await fetch(`/customer/cart/${itemId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      }
    })
    if (response.ok) {
      cartItems.value = cartItems.value.filter(item => item.id !== itemId)
      selectedStoreItemIds.value = selectedStoreItemIds.value.filter(id => id !== itemId)
    }
  } catch (err) {
    console.error('Error removing item:', err)
  }
}

const removeSelectedItems = async () => {
  const ids = selectedStoreItemIds.value
  if (ids.length === 0) return

  try {
    const response = await fetch('/customer/cart/bulk', {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      },
      body: JSON.stringify({ ids })
    })
    if (response.ok) {
      cartItems.value = cartItems.value.filter(item => !ids.includes(item.id))
      selectedStoreItemIds.value = []
    }
  } catch (err) {
    console.error('Error removing items:', err)
  }
}

const updateCartItemQty = async (itemId: number, quantity: number) => {
  try {
    const response = await fetch(`/customer/cart/${itemId}`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      },
      body: JSON.stringify({ quantity })
    })
    if (response.ok) {
      const item = cartItems.value.find(i => i.id === itemId)
      if (item) item.quantity = quantity
    }
  } catch (err) {
    console.error('Error updating quantity:', err)
  }
}

// Function to handle opening cart modal
const openCartModal = async () => {
  await fetchCartItems()
  await nextTick()
  storeCartModalOpen.value = true
}

onMounted(() => {
  fetchStoreDetails()
  fetchProducts()
  fetchCartItems()
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
      <div v-if="loading" class="max-w-6xl mx-auto px-6 pt-6">
        <div class="text-center py-12">
          <p class="text-muted-foreground">Loading store details...</p>
        </div>
      </div>

      <div v-else-if="error" class="max-w-6xl mx-auto px-6 pt-6">
        <div class="text-center py-12">
          <p class="text-red-600 mb-4">{{ error }}</p>
          <Button @click="fetchStoreDetails" variant="outline">
            Try Again
          </Button>
        </div>
      </div>

      <div v-else class="space-y-5 pb-12">
        <div class="max-w-6xl mx-auto px-6 pt-6">
          <Link href="/customer/stores">
            <Button variant="ghost" class="gap-2 text-[#245c4a]">
              <ArrowLeft class="w-4 h-4" />
              Back to Stores
            </Button>
          </Link>
        </div>

        <div class="max-w-6xl mx-auto px-6">
          <div class="relative h-64 w-full overflow-hidden rounded-2xl">
            <img :src="store.cover" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
          </div>
        </div>

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

        <div class="max-w-6xl mx-auto px-6">
          <h2 class="text-xl font-semibold text-[#245c4a] mb-6">Products</h2>

          <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between mb-6">
            <Input v-model="searchProduct" placeholder="Search products..." class="w-full lg:max-w-sm" />

            <div class="flex gap-3 w-full lg:w-auto">
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

          <div v-if="productsLoading" class="text-center py-12 text-muted-foreground">
            Loading products...
          </div>

          <div v-else class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
            <Card
              v-for="product in filteredProducts"
              :key="product.id"
              class="group rounded-xl border border-border bg-white dark:bg-muted shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden cursor-pointer p-0"
            >
              <Link :href="`/customer/products/${props.storeId}/${product.id}?from=store`" class="block relative w-full aspect-square overflow-hidden bg-gray-100">
                <img :src="product.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                <span v-if="!product.is_available" class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-xl">
                  Sold Out
                </span>
              </Link>

              <CardContent class="space-y-2">
                <Link :href="`/customer/products/${props.storeId}/${product.id}?from=store`" class="block text-sm font-medium text-foreground hover:text-[#245c4a] transition line-clamp-2 min-h-[40px]">
                  {{ product.product_name }}
                </Link>

                <div class="flex items-center justify-between">
                  <span class="text-lg font-semibold text-[#245c4a]">
                    {{ formatCurrency(product.unit_price) }}
                  </span>
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

                <div class="mt-2 mb-4 space-y-2">
                  <div class="flex items-center gap-2">
                    <button
                      class="h-8 w-8 rounded-md border border-border flex items-center justify-center text-lg font-medium hover:bg-muted transition disabled:opacity-40"
                      :disabled="getQty(product) <= 1"
                      @click.stop="decrement(product)"
                      aria-label="Decrease quantity"
                    >−</button>

                    <input
                      type="number"
                      :value="getQty(product)"
                      :min="1"
                      :max="product.stock_level > 0 ? product.stock_level : undefined"
                      class="w-12 h-8 text-center text-sm border border-border rounded-md bg-background focus:outline-none focus:ring-1 focus:ring-[#245c4a] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                      @change="setQty(product, Number(($event.target as HTMLInputElement).value))"
                      @keydown.up.prevent="increment(product)"
                      @keydown.down.prevent="decrement(product)"
                      aria-label="Quantity"
                    />

                    <button
                      class="h-8 w-8 rounded-md border border-border flex items-center justify-center text-lg font-medium hover:bg-muted transition disabled:opacity-40"
                      :disabled="product.stock_level > 0 && getQty(product) >= product.stock_level"
                      @click.stop="increment(product)"
                      aria-label="Increase quantity"
                    >+</button>

                    <span v-if="product.stock_level > 0" class="text-xs text-muted-foreground ml-auto">
                      / {{ product.stock_level }}
                    </span>
                  </div>

                  <Button
                    size="sm"
                    class="w-full bg-[#245c4a] hover:bg-[#1B4D3E] text-white"
                    :disabled="!product.is_available"
                    @click.stop="addToCart(product)"
                  >
                    {{ product.is_available ? 'Add to Cart' : 'Out of Stock' }}
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>

          <div v-if="filteredProducts.length === 0 && !productsLoading" class="text-center py-12 text-muted-foreground">
            No products found.
          </div>
        </div>

        <!-- Floating Cart Button -->
        <div class="fixed bottom-6 right-6 z-50">
          <button
            @click="openCartModal"
            class="relative h-14 w-14 rounded-full bg-[#245c4a] hover:bg-[#1B4D3E] text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1"
            aria-label="Open cart"
          >
            <ShoppingCart class="h-5 w-5" />
            <span
              v-if="storeCartItems.length > 0"
              class="absolute -top-1 -right-1 bg-[#C5A059] text-white text-xs h-5 w-5 rounded-full flex items-center justify-center font-semibold"
            >
              {{ storeCartItems.length }}
            </span>
          </button>
        </div>
      </div>
    </main>
  </div>

  <!-- Cart Modal -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="scale-95 opacity-0"
      enter-to-class="scale-100 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="scale-100 opacity-100"
      leave-to-class="scale-95 opacity-0"
    >
      <div 
        v-if="storeCartModalOpen" 
        :key="`cart-modal-${storeCartItems.length}-${props.storeId}`"
        class="fixed bottom-24 right-6 z-50 w-96 bg-white rounded-2xl shadow-2xl border border-border overflow-hidden flex flex-col max-h-[75vh]"
      >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-border">
          <div class="flex items-center gap-2">
            <ShoppingCart class="h-5 w-5 text-[#245c4a]" />
            <h2 class="text-base font-semibold">Cart — {{ store.name }}</h2>
          </div>
          <button @click="storeCartModalOpen = false" class="p-1 hover:bg-muted rounded-lg transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Select All / Remove Selected -->
        <div v-if="storeCartItems.length > 0" class="flex items-center justify-between px-4 py-2 border-b border-border">
          <label class="flex items-center gap-2 cursor-pointer text-sm font-medium">
            <input
              type="checkbox"
              :checked="storeAllSelected"
              @change="toggleStoreAll(($event.target as HTMLInputElement).checked)"
              class="h-4 w-4 rounded border-gray-300"
            />
            Select All
            <span class="text-xs text-muted-foreground">({{ selectedStoreItemIds.length }})</span>
          </label>
          <button
            v-if="selectedStoreItemIds.length > 0"
            @click="removeSelectedItems"
            class="text-sm font-medium text-red-600 hover:text-red-700 transition"
          >
            Remove Selected
          </button>
        </div>

        <!-- Items List -->
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
          <div v-if="storeCartItems.length === 0" class="py-8 text-center text-sm text-muted-foreground">
            No items from this store yet.
          </div>

          <div
            v-for="item in storeCartItems"
            :key="item.id"
            class="flex items-start gap-3 p-3 rounded-lg border border-border"
          >
            <!-- Checkbox -->
            <input
              type="checkbox"
              :checked="selectedStoreItemIds.includes(item.id)"
              @change="(e) => {
                if ((e.target as HTMLInputElement).checked) {
                  selectedStoreItemIds.push(item.id)
                } else {
                  selectedStoreItemIds = selectedStoreItemIds.filter(id => id !== item.id)
                }
              }"
              class="h-4 w-4 rounded border-gray-300 mt-1 cursor-pointer"
            />

            <!-- Image -->
            <div class="h-12 w-12 overflow-hidden rounded-lg bg-muted border border-border shrink-0">
              <img
                v-if="item.image_path"
                :src="item.image_path && /^https?:\/\//.test(item.image_path) ? item.image_path : `/storage/${item.image_path}`"
                :alt="item.product_name"
                class="h-full w-full object-cover"
                loading="lazy"
              />
            </div>

            <!-- Info -->
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium line-clamp-2">{{ item.product_name }}</p>
              <p class="text-sm font-semibold text-[#245c4a] mt-1">{{ formatCurrency(item.unit_price) }}</p>
            </div>

            <!-- Qty + Remove -->
            <div class="flex flex-col items-end gap-2 shrink-0">
              <button
                @click="removeCartItem(item.id)"
                class="text-xs text-red-600 hover:text-red-700 transition"
              >
                Remove
              </button>
              <div class="flex items-center gap-1 border border-border rounded-lg">
                <button
                  @click="updateCartItemQty(item.id, Math.max(1, item.quantity - 1))"
                  :disabled="item.quantity <= 1"
                  class="h-7 w-7 flex items-center justify-center text-sm hover:bg-muted disabled:opacity-40 transition"
                >−</button>
                <span class="w-6 text-center text-sm font-medium">{{ item.quantity }}</span>
                <button
                  @click="updateCartItemQty(item.id, item.quantity + 1)"
                  class="h-7 w-7 flex items-center justify-center text-sm hover:bg-muted transition"
                >+</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="storeCartItems.length > 0" class="border-t border-border p-4 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">
              {{ selectedStoreItemIds.length > 0 ? 'Selected' : 'Total' }}
            </span>
            <span class="text-base font-semibold text-[#245c4a]">
              {{ formatCurrency(selectedStoreItemIds.length > 0 ? storeSelectedTotal : storeCartTotal) }}
            </span>
          </div>
          <Link href="/customer/cart" class="block text-sm text-[#245c4a] hover:underline text-center font-medium">
            View full cart
          </Link>
          <Button class="w-full bg-[#245c4a] hover:bg-[#1B4D3E] text-white font-medium">
            Pre-order
          </Button>
        </div>
      </div>
    </Transition>

    <!-- Toast Notification -->
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

<style scoped>
.dashboard-content {
  margin-left: 250px;
  transition: margin-left 0.3s ease;
  min-height: 100vh;
  background-color: #f9fafb;
}

.dashboard-content.sidebar-collapsed {
  margin-left: 70px;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>