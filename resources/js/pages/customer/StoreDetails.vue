<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, ChevronDown, ShoppingCart, MapPin, Phone, Clock, Star } from 'lucide-vue-next'
import { ref, computed, onMounted, reactive, watch, nextTick } from 'vue';
import Header from '@/components/Header.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import Sidebar from '@/components/Sidebar.vue';
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { useSidebar } from '@/composables/useSidebar';

type Category = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  color: string;
  parent_id: number | null;
  children?: Category[];
};

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
    
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
    
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
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
    const data = await response.json()
    products.value = data.data || []
  } catch (err) {
    console.error('Error fetching products:', err)
    products.value = []
  } finally {
    productsLoading.value = false
  }
}

const categories = ref<Category[]>([])

const selectedCategoryLabel = computed(() => {
  if (selectedCategory.value === 'all') return 'Category'
  for (const cat of categories.value) {
    if (cat.id === selectedCategory.value) return cat.name
    for (const child of cat.children ?? []) {
      if (child.id === selectedCategory.value) return `${cat.name} › ${child.name}`
    }
  }
  return 'Category'
})

const fetchCategories = async () => {
  try {
    const response = await fetch('/customer/categories', {
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

const searchProduct = ref('')
const selectedCategory = ref<'all' | number>('all')
const sortBy = ref<'name' | 'price_low' | 'price_high'>('name')

const filteredProducts = computed(() => {
  const result = products.value.filter(product => {
    const matchesSearch = product.product_name.toLowerCase().includes(searchProduct.value.toLowerCase())
    const matchesCategory = selectedCategory.value === 'all' ? true : product.category_id === selectedCategory.value
    return matchesSearch && matchesCategory && product.is_active
  })
  if (sortBy.value === 'price_low') result.sort((a, b) => a.unit_price - b.unit_price)
  else if (sortBy.value === 'price_high') result.sort((a, b) => b.unit_price - a.unit_price)
  else result.sort((a, b) => a.product_name.localeCompare(b.product_name))
  return result
})

const quantities = reactive<Record<string, number>>({})
const getQtyKey = (product: any): string => `${props.storeId}_${product.id}`
const getQty = (product: any): number => quantities[getQtyKey(product)] ?? 1

const setQty = (product: any, val: number, event?: Event) => {
  const max = product.stock_level > 0 ? product.stock_level : Infinity
  const cleanVal = isNaN(val) ? 1 : Math.max(1, Math.floor(val))
  const finalVal = Math.min(cleanVal, max)
  if (cleanVal > max && max !== Infinity) showToast(`Maximum stock reached: Only ${max} units available.`, 'error')
  quantities[getQtyKey(product)] = finalVal
  if (event && event.target && (event.target as HTMLInputElement).value !== finalVal.toString()) {
    (event.target as HTMLInputElement).value = finalVal.toString()
  }
}

const increment = (product: any) => setQty(product, getQty(product) + 1)
const decrement = (product: any) => setQty(product, getQty(product) - 1)
const onQuantityKeydown = (e: KeyboardEvent) => { if (['e', 'E', '+', '-', '.'].includes(e.key)) e.preventDefault() }

const cartItems = ref<any[]>([])
const storeCartModalOpen = ref(false)
const selectedStoreItemIds = ref<number[]>([])

const storeCartItems = computed(() => cartItems.value.filter(item => String(item.store_id) === String(props.storeId)))
const storeCartTotal = computed(() => storeCartItems.value.reduce((sum, item) => sum + Number(item.unit_price) * item.quantity, 0))
const storeSelectedTotal = computed(() =>
  storeCartItems.value.filter(item => selectedStoreItemIds.value.includes(item.id))
    .reduce((sum, item) => sum + Number(item.unit_price) * item.quantity, 0)
)

watch(() => cartItems.value, () => {}, { deep: true })

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount)

const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null)
let toastTimer: ReturnType<typeof setTimeout> | null = null
const showToast = (message: string, type: 'success' | 'error' = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { message, type }
  toastTimer = setTimeout(() => { toast.value = null }, 3000)
}

const addingToCart = ref<Record<string, boolean>>({})

const addToCart = async (product: any) => {
  const productKey = getQtyKey(product)
  if (addingToCart.value[productKey]) return
  addingToCart.value[productKey] = true
  const qty = getQty(product)
  try {
    const response = await fetch('/customer/cart/add', {
      method: 'POST',
      headers: {
        'Accept': 'application/json', 'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      },
      body: JSON.stringify({ store_id: props.storeId, product_id: product.id, quantity: qty }),
    })
    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.message || 'Failed to add to cart')
    }
    quantities[getQtyKey(product)] = 1
    showToast(`${qty}× "${product.product_name}" added to cart!`)
    await fetchCartItems()
    await nextTick()
    storeCartModalOpen.value = true
  } catch (err) {
    showToast(err instanceof Error ? err.message : 'Failed to add to cart. Please try again.', 'error')
  } finally {
    addingToCart.value[productKey] = false
  }
}

const fetchCartItems = async () => {
  try {
    const response = await fetch('/customer/cart-data', {
      method: 'GET',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    if (response.ok) {
      const data = await response.json()
      const items: any[] = []
      data.data.forEach((storeCart: any) => {
        storeCart.items.forEach((item: any) => {
          items.push({
            id: item.id,
            store_id: String(storeCart.store_id),
            product_name: item.product.name,
            unit_price: Number(item.product.price) || 0,
            image_path: item.product.image_path,
            quantity: item.quantity,
          })
        })
      })
      cartItems.value = items
    }
  } catch (err) { console.error('Error fetching cart:', err) }
}

const removeCartItem = async (itemId: number) => {
  try {
    const response = await fetch(`/customer/cart/${itemId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      }
    })
    if (response.ok) {
      cartItems.value = cartItems.value.filter(item => item.id !== itemId)
      selectedStoreItemIds.value = selectedStoreItemIds.value.filter(id => id !== itemId)
    }
  } catch (err) { console.error('Error removing item:', err) }
}

const updateCartItemQty = async (itemId: number, quantity: number) => {
  try {
    const response = await fetch(`/customer/cart/${itemId}`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      },
      body: JSON.stringify({ quantity })
    })
    if (response.ok) {
      const item = cartItems.value.find(i => i.id === itemId)
      if (item) item.quantity = quantity
    }
  } catch (err) { console.error('Error updating quantity:', err) }
}

const openCartModal = async () => {
  await fetchCartItems()
  await nextTick()
  storeCartModalOpen.value = true
}

onMounted(() => {
  fetchStoreDetails()
  fetchProducts()
  fetchCategories()
  fetchCartItems()
})
</script>

<template>
  <Head title="Store Details" />

  <div class="dashboard-wrapper">
    <Header />
    <Sidebar role="customer">
      <CustomerNav />
      <template #icons><CustomerNavIcons /></template>
    </Sidebar>

    <main :class="contentClass">
      <!-- Loading -->
      <div v-if="loading" class="max-w-6xl mx-auto px-6 pt-6">
        <div class="text-center py-12 text-muted-foreground">Loading store details...</div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="max-w-6xl mx-auto px-6 pt-6">
        <div class="text-center py-12">
          <p class="text-red-600 mb-4">{{ error }}</p>
          <Button @click="fetchStoreDetails" variant="outline">Try Again</Button>
        </div>
      </div>

      <div v-else class="pb-16">

        <!-- ── STORE HEADER ── social media style, full bleed -->
        <div class="relative">
          <!-- Cover photo — edge to edge, no horizontal padding -->
          <div class="relative h-52 sm:h-64 w-full overflow-hidden bg-secondary">
            <img :src="store.cover" class="h-full w-full object-cover" />
            <!-- gradient scrim for readability -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

            <!-- Back button floating top-left over the cover -->
            <div class="absolute top-4 left-4 sm:left-6">
              <Link href="/customer/stores">
                <button class="inline-flex items-center gap-1.5 rounded-full bg-black/30 backdrop-blur-md border border-white/20 px-3 py-1.5 text-xs font-medium text-white hover:bg-black/50 transition">
                  <ArrowLeft class="h-3.5 w-3.5" />
                  All Stores
                </button>
              </Link>
            </div>

            <!-- Open/Closed badge floating top-right -->
            <div class="absolute top-4 right-4 sm:right-6">
              <span
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold backdrop-blur-md border"
                :class="store.isOpen
                  ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'
                  : 'bg-red-500/20 text-red-300 border-red-500/30'"
              >
                <span class="h-1.5 w-1.5 rounded-full" :class="store.isOpen ? 'bg-emerald-400' : 'bg-red-400'"></span>
                {{ store.isOpen ? 'Open now' : 'Closed' }}
              </span>
            </div>
          </div>

          <!-- Profile area: avatar overlapping cover, name & meta beside it -->
          <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-end gap-4 -mt-10 sm:-mt-12 relative z-10">
              <!-- Avatar with ring -->
              <div class="shrink-0">
                <img
                  :src="store.logo"
                  class="h-20 w-20 sm:h-24 sm:w-24 rounded-2xl object-cover ring-4 ring-background shadow-xl border border-border"
                />
              </div>

              <!-- Name + quick meta, sitting just below cover level -->
              <div class="flex-1 min-w-0 pb-1">
                <h1 class="text-xl sm:text-2xl font-bold text-foreground leading-tight truncate">
                  {{ store.name }}
                </h1>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                  <span class="flex items-center gap-1 text-xs text-muted-foreground">
                    <MapPin class="h-3 w-3 shrink-0" />
                    <span class="truncate max-w-[180px]">{{ store.address }}</span>
                  </span>
                  <span class="flex items-center gap-1 text-xs text-muted-foreground">
                    <Phone class="h-3 w-3 shrink-0" />
                    {{ store.phone }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Description + hours row -->
            <div class="mt-4 pb-5 border-b border-border space-y-3">
              <p v-if="store.description" class="text-sm text-muted-foreground leading-relaxed max-w-2xl">
                {{ store.description }}
              </p>
              <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                <Clock class="h-3.5 w-3.5 shrink-0 text-brand-green dark:text-emerald-500" />
                <span><span class="font-medium text-foreground">Hours:</span> {{ store.hours }}</span>
              </div>
            </div>
          </div>
        </div>
        <!-- ── END STORE HEADER ── -->

        <!-- Products section -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 mt-6 space-y-5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-base font-semibold text-foreground">
              Products
              <span v-if="!productsLoading" class="ml-1.5 text-xs font-normal text-muted-foreground">({{ filteredProducts.length }})</span>
            </h2>

            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
              <Input v-model="searchProduct" placeholder="Search products…" class="w-full sm:w-52 bg-background h-9 text-sm" />

              <div class="flex gap-2">
                <!-- Category dropdown — from main branch (dynamic, hierarchical) -->
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button
                      variant="outline"
                      size="sm"
                      class="gap-1.5 bg-background text-xs h-9 min-w-[7rem] max-w-[11rem] justify-between"
                      :class="{ 'border-brand-green text-brand-green': selectedCategory !== 'all' }"
                    >
                      <span class="truncate">{{ selectedCategoryLabel }}</span>
                      <ChevronDown class="w-3.5 h-3.5 opacity-60 flex-shrink-0" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent class="bg-card border-border">
                    <DropdownMenuItem class="filter-dropdown-item" @click="selectedCategory = 'all'">
                      <span class="cat-dot" style="background:#94a3b8"></span>
                      All Categories
                    </DropdownMenuItem>

                    <template v-for="cat in categories" :key="cat.id">
                      <DropdownMenuSeparator class="filter-sep" />
                      <DropdownMenuItem
                        class="filter-dropdown-item filter-dropdown-item--parent"
                        @click="selectedCategory = cat.id"
                        :class="{ 'filter-dropdown-item--selected': selectedCategory === cat.id }"
                      >
                        <span class="cat-dot" :style="{ background: cat.color || '#6366f1' }"></span>
                        {{ cat.name }}
                      </DropdownMenuItem>

                      <DropdownMenuItem
                        v-for="child in cat.children ?? []"
                        :key="child.id"
                        class="filter-dropdown-item filter-dropdown-item--child"
                        @click="selectedCategory = child.id"
                        :class="{ 'filter-dropdown-item--selected': selectedCategory === child.id }"
                      >
                        <span class="cat-dot cat-dot--small" :style="{ background: child.color || cat.color || '#6366f1' }"></span>
                        {{ child.name }}
                      </DropdownMenuItem>
                    </template>
                  </DropdownMenuContent>
                </DropdownMenu>

                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="sm" class="gap-1.5 bg-background text-xs h-9">
                      Sort <ChevronDown class="w-3.5 h-3.5 opacity-60" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent class="bg-card border-border">
                    <DropdownMenuItem @click="sortBy = 'name'">Name</DropdownMenuItem>
                    <DropdownMenuItem @click="sortBy = 'price_low'">Price: Low to High</DropdownMenuItem>
                    <DropdownMenuItem @click="sortBy = 'price_high'">Price: High to Low</DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </div>

          <!-- Product grid -->
          <div v-if="productsLoading" class="text-center py-12 text-muted-foreground text-sm">
            Loading products…
          </div>

          <div v-else class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            <Card
              v-for="product in filteredProducts"
              :key="product.id"
              class="group rounded-2xl border border-border bg-card shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden"
            >
              <Link :href="`/customer/products/${props.storeId}/${product.id}?from=store`" class="block relative w-full aspect-square overflow-hidden bg-secondary/20">
                <img :src="product.image_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                <span v-if="!product.is_available" class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full">
                  Sold Out
                </span>
                <span v-if="product.stock_level <= 5 && product.stock_level > 0" class="absolute top-2 right-2 bg-orange-500 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full">
                  Low stock
                </span>
              </Link>

              <CardContent class="p-3 space-y-2">
                <Link
                  :href="`/customer/products/${props.storeId}/${product.id}?from=store`"
                  class="block text-sm font-medium text-foreground hover:text-brand-green dark:hover:text-emerald-500 transition line-clamp-2 leading-snug min-h-[36px]"
                >
                  {{ product.product_name }}
                </Link>

                <div class="flex items-center justify-between">
                  <span class="text-base font-bold text-brand-green dark:text-emerald-500">
                    {{ formatCurrency(product.unit_price) }}
                  </span>
                  <span class="flex items-center gap-0.5 text-[11px] text-muted-foreground">
                    <Star class="h-3 w-3 fill-amber-400 text-amber-400" />
                    {{ product.rating }}
                    <span class="opacity-60 ml-1">· {{ product.sold_count }} sold</span>
                  </span>
                </div>

                <!-- Quantity controls -->
                <div class="flex items-center gap-1.5">
                  <button
                    class="h-7 w-7 rounded-lg border border-border flex items-center justify-center text-base font-medium hover:bg-secondary transition disabled:opacity-40"
                    :disabled="getQty(product) <= 1"
                    @click.stop="decrement(product)"
                    aria-label="Decrease"
                  >−</button>

                  <input
                    type="number"
                    :value="getQty(product)"
                    :min="1"
                    :max="product.stock_level > 0 ? product.stock_level : undefined"
                    class="w-10 h-7 text-center text-sm border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-brand-green [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                    @input="setQty(product, parseInt(($event.target as HTMLInputElement).value), $event)"
                    @keydown="onQuantityKeydown"
                    @keydown.up.prevent="increment(product)"
                    @keydown.down.prevent="decrement(product)"
                  />

                  <button
                    class="h-7 w-7 rounded-lg border border-border flex items-center justify-center text-base font-medium hover:bg-secondary transition disabled:opacity-40"
                    :disabled="product.stock_level > 0 && getQty(product) >= product.stock_level"
                    @click.stop="increment(product)"
                    aria-label="Increase"
                  >+</button>

                  <span v-if="product.stock_level > 0" class="text-[10px] text-muted-foreground ml-auto">
                    /{{ product.stock_level }}
                  </span>
                </div>

                <Button
                  size="sm"
                  class="w-full bg-brand-green hover:opacity-90 text-white dark:bg-emerald-600 text-xs h-8"
                  :disabled="!product.is_available || addingToCart[getQtyKey(product)]"
                  @click.stop="addToCart(product)"
                >
                  {{ addingToCart[getQtyKey(product)] ? 'Adding…' : (product.is_available ? 'Add to Cart' : 'Out of Stock') }}
                </Button>
              </CardContent>
            </Card>
          </div>

          <div v-if="filteredProducts.length === 0 && !productsLoading" class="text-center py-12 text-sm text-muted-foreground">
            No products found.
          </div>
        </div>

        <!-- Floating Cart FAB -->
        <div class="fixed bottom-6 right-6 z-50">
          <button
            @click="openCartModal"
            class="relative h-14 w-14 rounded-full bg-brand-green hover:opacity-90 text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 dark:bg-emerald-600"
            aria-label="Open cart"
          >
            <ShoppingCart class="h-5 w-5" />
            <span
              v-if="storeCartItems.length > 0"
              class="absolute -top-1 -right-1 bg-amber-500 text-white text-[10px] h-5 w-5 rounded-full flex items-center justify-center font-bold"
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
        class="fixed bottom-24 right-6 z-50 w-[90vw] sm:w-96 bg-card rounded-2xl shadow-2xl border border-border overflow-hidden flex flex-col max-h-[75vh]"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-border bg-secondary/30">
          <div class="flex items-center gap-2">
            <ShoppingCart class="h-4 w-4 text-brand-green dark:text-emerald-500" />
            <div>
              <p class="text-sm font-semibold text-foreground leading-none">Your Cart</p>
              <p class="text-[11px] text-muted-foreground mt-0.5 truncate max-w-[180px]">{{ store.name }}</p>
            </div>
          </div>
          <button @click="storeCartModalOpen = false" class="p-1.5 hover:bg-secondary rounded-lg transition text-muted-foreground">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Items -->
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2.5">
          <div v-if="storeCartItems.length === 0" class="py-8 text-center text-sm text-muted-foreground">
            No items from this store yet.
          </div>

          <div
            v-for="item in storeCartItems"
            :key="item.id"
            class="flex items-start gap-3 p-3 rounded-xl border border-border bg-background/60"
          >
            <input
              type="checkbox"
              :checked="selectedStoreItemIds.includes(item.id)"
              @change="(e) => {
                if ((e.target as HTMLInputElement).checked) selectedStoreItemIds.push(item.id)
                else selectedStoreItemIds = selectedStoreItemIds.filter(id => id !== item.id)
              }"
              class="h-4 w-4 rounded border-border bg-background text-brand-green mt-0.5 cursor-pointer shrink-0"
            />
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium line-clamp-2 text-foreground leading-snug">{{ item.product_name }}</p>
              <p class="text-sm font-bold text-brand-green dark:text-emerald-500 mt-1">{{ formatCurrency(item.unit_price) }}</p>
            </div>
            <div class="flex flex-col items-end gap-2 shrink-0">
              <button @click="removeCartItem(item.id)" class="text-[11px] text-red-500 hover:text-red-600 transition font-medium">
                Remove
              </button>
              <div class="flex items-center gap-0.5 border border-border rounded-lg bg-background overflow-hidden">
                <button
                  @click="updateCartItemQty(item.id, Math.max(1, item.quantity - 1))"
                  :disabled="item.quantity <= 1"
                  class="h-7 w-7 flex items-center justify-center text-sm hover:bg-secondary disabled:opacity-40 transition"
                >−</button>
                <!-- From main: editable quantity input in cart modal -->
                <input
                  type="number"
                  :value="item.quantity"
                  :min="1"
                  :max="item.stock_level > 0 ? item.stock_level : undefined"
                  class="w-8 h-7 text-center text-sm font-semibold text-foreground bg-transparent border-none focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                  @input="(e) => updateCartItemQty(item.id, parseInt((e.target as HTMLInputElement).value) || 1)"
                  aria-label="Quantity"
                />
                <button
                  @click="updateCartItemQty(item.id, item.quantity + 1)"
                  class="h-7 w-7 flex items-center justify-center text-sm hover:bg-secondary transition"
                >+</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="storeCartItems.length > 0" class="border-t border-border p-4 space-y-3 bg-secondary/20">
          <div class="flex items-center justify-between">
            <span class="text-xs text-muted-foreground">{{ selectedStoreItemIds.length > 0 ? 'Selected total' : 'Total' }}</span>
            <span class="text-base font-bold text-brand-green dark:text-emerald-500">
              {{ formatCurrency(selectedStoreItemIds.length > 0 ? storeSelectedTotal : storeCartTotal) }}
            </span>
          </div>
          <Link href="/customer/cart" class="block text-xs text-brand-green dark:text-emerald-500 hover:underline text-center font-medium">
            View full cart →
          </Link>
          <Button class="w-full bg-brand-green hover:opacity-90 text-white dark:bg-emerald-600 font-semibold text-sm h-10">
            Checkout Store Items
          </Button>
        </div>
      </div>
    </Transition>

    <!-- Toast -->
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
        :class="toast.type === 'error' ? 'bg-red-500 text-white' : 'bg-brand-green text-white dark:bg-emerald-600'"
      >
        {{ toast.message }}
        <button class="ml-2 opacity-70 hover:opacity-100 transition-opacity text-base leading-none" @click="toast = null">✕</button>
      </div>
    </Transition>
  </Teleport>
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
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Category dropdown styles — from main branch */
.filter-dropdown-item {
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 7px 10px !important;
  border-radius: 8px !important;
  font-size: 0.8125rem !important;
  color: var(--foreground) !important;
  cursor: pointer;
  transition: background 0.1s;
}
.filter-dropdown-item:hover { background: var(--accent) !important; }
.filter-dropdown-item--parent { font-weight: 600 !important; }
.filter-dropdown-item--child {
  padding-left: 26px !important;
  font-weight: 400 !important;
  color: var(--muted-foreground) !important;
}
.filter-dropdown-item--child:hover { color: var(--foreground) !important; }
.filter-dropdown-item--selected { color: var(--brand-green) !important; }
.dark .filter-dropdown-item--selected { color: #34d399 !important; }
.filter-sep { background: var(--border) !important; margin: 4px 0 !important; }

.cat-dot {
  display: inline-block;
  width: 9px;
  height: 9px;
  border-radius: 50%;
  flex-shrink: 0;
}
.cat-dot--small {
  width: 7px;
  height: 7px;
}
</style>