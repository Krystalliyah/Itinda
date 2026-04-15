<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch, reactive } from 'vue';
import { watchDebounced } from '@vueuse/core';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { ChevronDown, ShoppingCart, Star, Package } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from '@/components/ui/dropdown-menu'

// ── Types ────────────────────────────────────────────────────
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
const page = usePage();
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));

// ── Data ─────────────────────────────────────────────────────
const products = ref<any[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

// ── Filter state ─────────────────────────────────────────────
const searchProduct = ref('')
const selectedCategory = ref<'all' | number>('all')
const sortBy = ref<'name' | 'price_low' | 'price_high'>('name')

// ── Category label helper ─────────────────────────────────────
const selectedCategoryLabel = computed(() => {
  if (selectedCategory.value === 'all') return 'Category'
  // Search parent categories
  for (const cat of categories.value) {
    if (cat.id === selectedCategory.value) return cat.name
    for (const child of cat.children ?? []) {
      if (child.id === selectedCategory.value) return `${cat.name} › ${child.name}`
    }
  }
  return 'Category'
})

const sortLabel = computed(() => {
  if (sortBy.value === 'price_low') return 'Price ↑'
  if (sortBy.value === 'price_high') return 'Price ↓'
  return 'Name'
})

// ── API calls ─────────────────────────────────────────────────
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

const fetchAllProducts = async () => {
  try {
    loading.value = true
    error.value = null
    const params = new URLSearchParams()
    if (searchProduct.value) params.append('search', searchProduct.value)
    if (selectedCategory.value !== 'all') params.append('category_id', selectedCategory.value.toString())
    if (sortBy.value) params.append('sort_by', sortBy.value)
    const queryString = params.toString()
    const url = `/customer/products-data${queryString ? '?' + queryString : ''}`
    const response = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
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

watchDebounced(searchProduct, () => fetchAllProducts(), { debounce: 300 })
watch([selectedCategory, sortBy], () => fetchAllProducts())

// ── Simplified Quantity helpers ──────────────────────────────────────────
const quantities = reactive<Record<string, number>>({})
const getQtyKey = (product: any): string => `${product.store.id}_${product.id}`
const getQty = (product: any): number => quantities[getQtyKey(product)] ?? 1

// Simple update - let the backend validate
const updateQuantity = (product: any, value: number | string) => {
  let numValue = typeof value === 'string' ? parseInt(value, 10) : value
  
  // Handle invalid input by defaulting to 1
  if (isNaN(numValue) || numValue < 1) {
    numValue = 1
  }
  
  quantities[getQtyKey(product)] = numValue
}

const increment = (product: any) => {
  const current = getQty(product)
  updateQuantity(product, current + 1)
}

const decrement = (product: any) => {
  const current = getQty(product)
  if (current > 1) {
    updateQuantity(product, current - 1)
  }
}

// ── Toast ─────────────────────────────────────────────────────
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null)
let toastTimer: ReturnType<typeof setTimeout> | null = null
const showToast = (message: string, type: 'success' | 'error' = 'success') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { message, type }
  toastTimer = setTimeout(() => { toast.value = null }, 3000)
}

// ── Cart ──────────────────────────────────────────────────────
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
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      },
      body: JSON.stringify({ store_id: product.store.id, product_id: product.id, quantity: qty }),
    })
    
    const data = await response.json()
    
    if (!response.ok) {
      throw new Error(data.message || 'Failed to add to cart')
    }
    
    // Reset quantity to 1 on success
    quantities[getQtyKey(product)] = 1
    showToast(`${qty}× "${product.product_name}" added to cart!`)
  } catch (err) {
    showToast(err instanceof Error ? err.message : 'Failed to add to cart.', 'error')
  } finally {
    addingToCart.value[productKey] = false
  }
}

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
      <div class="browse-page">

        <!-- ── Page Header ───────────────────────────────── -->
        <div class="page-header">
          <div class="page-header-text">
            <h1 class="page-title">Browse Products</h1>
            <p class="page-subtitle">
              Discover products from local vendors — compare prices, check availability, and shop with ease.
            </p>
          </div>
        </div>

        <!-- ── Filters ───────────────────────────────────── -->
        <div class="filter-bar">
          <!-- Search -->
          <div class="search-wrap">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input
              v-model="searchProduct"
              placeholder="Search products..."
              class="search-input"
              type="text"
            />
          </div>

          <div class="filter-pills">
            <!-- Category Dropdown -->
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <button class="filter-pill" :class="{ 'filter-pill--active': selectedCategory !== 'all' }">
                  <span>{{ selectedCategoryLabel }}</span>
                  <ChevronDown class="w-3.5 h-3.5 opacity-60" />
                </button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="filter-dropdown">
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
                    <span
                      class="cat-dot"
                      :style="{ background: cat.color || '#6366f1' }"
                    ></span>
                    {{ cat.name }}
                  </DropdownMenuItem>

                  <DropdownMenuItem
                    v-for="child in cat.children ?? []"
                    :key="child.id"
                    class="filter-dropdown-item filter-dropdown-item--child"
                    @click="selectedCategory = child.id"
                    :class="{ 'filter-dropdown-item--selected': selectedCategory === child.id }"
                  >
                    <span
                      class="cat-dot cat-dot--small"
                      :style="{ background: child.color || cat.color || '#6366f1' }"
                    ></span>
                    {{ child.name }}
                  </DropdownMenuItem>
                </template>
              </DropdownMenuContent>
            </DropdownMenu>

            <!-- Sort Dropdown -->
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <button class="filter-pill" :class="{ 'filter-pill--active': sortBy !== 'name' }">
                  <span>{{ sortLabel }}</span>
                  <ChevronDown class="w-3.5 h-3.5 opacity-60" />
                </button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="filter-dropdown">
                <DropdownMenuItem class="filter-dropdown-item" @click="sortBy = 'name'">Name A–Z</DropdownMenuItem>
                <DropdownMenuItem class="filter-dropdown-item" @click="sortBy = 'price_low'">Price: Low to High</DropdownMenuItem>
                <DropdownMenuItem class="filter-dropdown-item" @click="sortBy = 'price_high'">Price: High to Low</DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            <!-- Clear filters chip -->
            <button
              v-if="selectedCategory !== 'all' || searchProduct || sortBy !== 'name'"
              class="filter-clear"
              @click="selectedCategory = 'all'; searchProduct = ''; sortBy = 'name'"
            >
              Clear ✕
            </button>
          </div>
        </div>

        <!-- ── Loading ────────────────────────────────────── -->
        <div v-if="loading" class="state-center">
          <div class="elegant-loader">
            <div class="loader-ring loader-ring--outer"></div>
            <div class="loader-ring loader-ring--inner"></div>
            <div class="loader-core"></div>
          </div>
          <p class="loading-text">Finding products for you…</p>
        </div>

        <!-- ── Error ──────────────────────────────────────── -->
        <div v-else-if="error" class="state-center">
          <p class="text-red-500 font-medium mb-4">{{ error }}</p>
          <button class="filter-pill" @click="fetchAllProducts">Try Again</button>
        </div>

        <!-- ── Empty ──────────────────────────────────────── -->
        <div v-else-if="filteredProducts.length === 0" class="empty-state">
          <div class="empty-icon">
            <ShoppingCart class="h-10 w-10" />
          </div>
          <p class="empty-title">No products found</p>
          <p class="empty-sub">Try adjusting your search or category filter</p>
        </div>

        <!-- ── Product Grid ───────────────────────────────── -->
        <div v-else class="product-grid">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="product-card"
          >
            <!-- Image -->
            <Link
              :href="`/customer/products/${product.store.id}/${product.id}`"
              class="card-image-wrap"
            >
              <img
                :src="product.image_url"
                :alt="product.product_name"
                class="card-image"
              />
              <span v-if="!product.is_available" class="badge badge--soldout">Sold Out</span>
              <span v-else-if="product.stock_level <= 5 && product.stock_level > 0" class="badge badge--low">Low Stock</span>
            </Link>

            <!-- Body -->
            <div class="card-body">
              <!-- Store -->
              <Link
                :href="`/customer/stores/${product.store.id}`"
                class="store-link"
              >
                <img
                  :src="product.store.logo"
                  :alt="product.store.name"
                  class="store-logo"
                />
                <span class="store-name">{{ product.store.name }}</span>
              </Link>

              <!-- Product name -->
              <Link
                :href="`/customer/products/${product.store.id}/${product.id}`"
                class="product-name"
              >
                {{ product.product_name }}
              </Link>

              <!-- Price + Rating row -->
              <div class="meta-row">
                <span class="price">₱{{ product.unit_price.toLocaleString() }}</span>
                <div class="rating">
                  <Star class="w-3 h-3 fill-amber-400 text-amber-400" />
                  <span>{{ product.rating }}</span>
                  <span class="text-muted-foreground">· {{ product.sold_count }} sold</span>
                </div>
              </div>

              <!-- Divider -->
              <div class="card-divider"></div>

              <!-- Quantity + Cart - SIMPLIFIED -->
              <div class="qty-row">
                <button
                  class="qty-btn"
                  :disabled="getQty(product) <= 1"
                  @click.stop="decrement(product)"
                  aria-label="Decrease"
                >−</button>

                <input
                  type="number"
                  :value="getQty(product)"
                  :min="1"
                  :max="product.stock_level > 0 ? product.stock_level : undefined"
                  class="qty-input"
                  @input="(e) => updateQuantity(product, (e.target as HTMLInputElement).value)"
                  @keydown.up.prevent="increment(product)"
                  @keydown.down.prevent="decrement(product)"
                  aria-label="Quantity"
                />

                <button
                  class="qty-btn"
                  :disabled="product.stock_level > 0 && getQty(product) >= product.stock_level"
                  @click.stop="increment(product)"
                  aria-label="Increase"
                >+</button>

                <span v-if="product.stock_level > 0" class="qty-max">/ {{ product.stock_level }}</span>
              </div>

              <button
                class="add-btn"
                :class="{ 'add-btn--disabled': !product.is_available }"
                :disabled="!product.is_available || addingToCart[getQtyKey(product)]"
                @click="addToCart(product)"
              >
                <span v-if="addingToCart[getQtyKey(product)]" class="add-btn-spinner"></span>
                {{ addingToCart[getQtyKey(product)] ? 'Adding…' : (product.is_available ? 'Add to Cart' : 'Out of Stock') }}
              </button>
            </div>
          </div>
        </div>

      </div><!-- /browse-page -->
    </main>
  </div>

  <!-- Floating Cart Button -->
  <Link href="/customer/cart" class="cart-fab" aria-label="View cart">
    <ShoppingCart class="h-5 w-5" />
  </Link>

  <!-- Toast -->
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
        class="toast-notification"
        :class="toast.type === 'error' ? 'toast-notification--error' : ''"
      >
        {{ toast.message }}
        <button class="toast-close" @click="toast = null">✕</button>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ══════════════════════════════════════════════
   PAGE SHELL
══════════════════════════════════════════════ */
.dashboard-wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
.dashboard-content {
  flex: 1;
  margin-left: 256px;
  transition: margin-left 0.3s ease;
}
.dashboard-content.sidebar-collapsed {
  margin-left: 80px;
}
@media (max-width: 767px) {
  .dashboard-content,
  .dashboard-content.sidebar-collapsed {
    margin-left: 0;
  }
}

.browse-page {
  padding: 28px 32px 80px;
  max-width: 1400px;
  margin: 0 auto;
}
@media (max-width: 767px) {
  .browse-page { padding: 20px 16px 80px; }
}

/* ══════════════════════════════════════════════
   PAGE HEADER
══════════════════════════════════════════════ */
.page-header {
  margin-bottom: 24px;
}
.page-title {
  font-size: 1.65rem;
  font-weight: 700;
  color: var(--brand-green);
  margin: 0 0 4px;
  letter-spacing: -0.02em;
}
.dark .page-title {
  color: #34d399;
}
.page-subtitle {
  font-size: 0.875rem;
  color: var(--muted-foreground);
  margin: 0;
}

/* ══════════════════════════════════════════════
   FILTER BAR
══════════════════════════════════════════════ */
.filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  margin-bottom: 28px;
}

.search-wrap {
  position: relative;
  flex: 1;
  min-width: 200px;
  max-width: 360px;
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 15px;
  height: 15px;
  color: var(--muted-foreground);
  pointer-events: none;
}
.search-input {
  width: 100%;
  height: 38px;
  padding: 0 12px 0 36px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--card);
  color: var(--foreground);
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.search-input:focus {
  border-color: var(--brand-green);
  box-shadow: 0 0 0 3px rgba(27, 77, 62, 0.1);
}
.dark .search-input:focus {
  border-color: #34d399;
  box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.12);
}
.search-input::placeholder { color: var(--muted-foreground); }

.filter-pills {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.filter-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 38px;
  padding: 0 14px;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: var(--card);
  color: var(--foreground);
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}
.filter-pill:hover {
  border-color: var(--brand-green);
  color: var(--brand-green);
}
.dark .filter-pill:hover {
  border-color: #34d399;
  color: #34d399;
}
.filter-pill--active {
  border-color: var(--brand-green);
  color: var(--brand-green);
  background: rgba(27, 77, 62, 0.07);
}
.dark .filter-pill--active {
  border-color: #34d399;
  color: #34d399;
  background: rgba(52, 211, 153, 0.08);
}

.filter-clear {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  height: 30px;
  padding: 0 10px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--muted-foreground);
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.15s;
}
.filter-clear:hover {
  background: var(--destructive);
  color: white;
  border-color: var(--destructive);
}

/* Dropdown */
.filter-dropdown {
  background: var(--card) !important;
  border: 1px solid var(--border) !important;
  border-radius: 12px !important;
  padding: 6px !important;
  min-width: 200px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}
.dark .filter-dropdown {
  box-shadow: 0 8px 32px rgba(0,0,0,0.4) !important;
}
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

/* ══════════════════════════════════════════════
   STATES (loading / empty / error)
══════════════════════════════════════════════ */
.state-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 20px;
}

/* Loader */
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
  border-top-color: var(--brand-green);
  border-bottom-color: transparent;
  border-left-color: transparent;
  border-right-color: transparent;
  animation-duration: 1.2s;
}
.dark .loader-ring--outer {
  border-top-color: #34d399;
}

.loader-ring--inner {
  inset: 10px;
  border-bottom-color: var(--brand-gold);
  border-top-color: transparent;
  border-left-color: transparent;
  border-right-color: transparent;
  animation-duration: 0.9s;
  animation-direction: reverse;
}

.loader-core {
  position: absolute;
  inset: 20px;
  border-radius: 50%;
  background: var(--brand-green);
  opacity: 0.25;
  animation: core-pulse 1.5s ease-in-out infinite;
}
.dark .loader-core {
  background: #34d399;
}

@keyframes elegant-spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
@keyframes core-pulse {
  0%, 100% { transform: scale(0.85); opacity: 0.2; }
  50%       { transform: scale(1.1);  opacity: 0.4; }
}

.loading-text {
  font-size: 0.8125rem;
  color: var(--muted-foreground);
  letter-spacing: 0.04em;
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
  border-radius: 20px;
  gap: 8px;
}
.empty-icon {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(27, 77, 62, 0.08);
  border-radius: 16px;
  color: var(--brand-green);
  margin-bottom: 8px;
}
.dark .empty-icon { background: rgba(52, 211, 153, 0.1); color: #34d399; }
.empty-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--foreground);
  margin: 0;
}
.empty-sub {
  font-size: 0.8125rem;
  color: var(--muted-foreground);
  margin: 0;
}

/* ══════════════════════════════════════════════
   PRODUCT GRID
══════════════════════════════════════════════ */
.product-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}
@media (min-width: 640px)  { .product-grid { grid-template-columns: repeat(2, 1fr); gap: 18px; } }
@media (min-width: 900px)  { .product-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1200px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }

/* ══════════════════════════════════════════════
   PRODUCT CARD
══════════════════════════════════════════════ */
.product-card {
  display: flex;
  flex-direction: column;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 16px;
  overflow: hidden;
  transition: box-shadow 0.2s, transform 0.2s, border-color 0.2s;
}
.product-card:hover {
  box-shadow: 0 8px 28px rgba(0,0,0,0.09);
  transform: translateY(-2px);
  border-color: rgba(27, 77, 62, 0.25);
}
.dark .product-card:hover {
  box-shadow: 0 8px 32px rgba(0,0,0,0.35);
  border-color: rgba(52, 211, 153, 0.2);
}

/* Image */
.card-image-wrap {
  position: relative;
  display: block;
  width: 100%;
  aspect-ratio: 1 / 1;
  background: var(--secondary);
  overflow: hidden;
  flex-shrink: 0;
}
.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}
.product-card:hover .card-image {
  transform: scale(1.04);
}

/* Badges */
.badge {
  position: absolute;
  top: 10px;
  left: 10px;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  padding: 3px 8px;
  border-radius: 20px;
  text-transform: uppercase;
}
.badge--soldout { background: #ef4444; color: #fff; }
.badge--low     { background: #f97316; color: #fff; }

/* Body */
.card-body {
  display: flex;
  flex-direction: column;
  padding: 14px;
  gap: 8px;
  flex: 1;
}

/* Store link */
.store-link {
  display: flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
}
.store-logo {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid var(--border);
  flex-shrink: 0;
}
.store-name {
  font-size: 0.7rem;
  font-weight: 500;
  color: var(--muted-foreground);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.15s;
}
.store-link:hover .store-name {
  color: var(--brand-green);
}
.dark .store-link:hover .store-name { color: #34d399; }

/* Product name */
.product-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--foreground);
  text-decoration: none;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  line-height: 1.35;
  min-height: 2.36em;
  transition: color 0.15s;
}
.product-name:hover { color: var(--brand-green); }
.dark .product-name:hover { color: #34d399; }

/* Meta row */
.meta-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}
.price {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--brand-green);
  letter-spacing: -0.02em;
}
.dark .price { color: #34d399; }
.rating {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 0.7rem;
  color: var(--foreground);
  font-weight: 500;
}

.card-divider {
  height: 1px;
  background: var(--border);
  margin: 2px 0;
}

/* Quantity controls - SIMPLIFIED */
.qty-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.qty-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--background);
  color: var(--foreground);
  font-size: 1rem;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
  flex-shrink: 0;
}
.qty-btn:hover:not(:disabled) {
  background: var(--accent);
  border-color: var(--brand-green);
}
.qty-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.qty-input {
  width: 55px;
  height: 30px;
  text-align: center;
  font-size: 0.8125rem;
  font-weight: 600;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--background);
  color: var(--foreground);
  outline: none;
}
.qty-input:focus {
  border-color: var(--brand-green);
  box-shadow: 0 0 0 2px rgba(27,77,62,0.1);
}
/* Remove spinner buttons for better typing experience */
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.qty-input[type=number] {
  -moz-appearance: textfield;
  appearance: textfield;
}
.qty-max {
  font-size: 0.7rem;
  color: var(--muted-foreground);
  margin-left: auto;
}

/* Add to cart button */
.add-btn {
  width: 100%;
  height: 36px;
  border-radius: 10px;
  background: var(--brand-green);
  color: white;
  font-size: 0.8125rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: opacity 0.15s, transform 0.1s;
  letter-spacing: 0.01em;
}
.add-btn:hover:not(:disabled) { opacity: 0.88; transform: translateY(-1px); }
.add-btn:active:not(:disabled) { transform: translateY(0); }
.add-btn--disabled { background: var(--muted); cursor: not-allowed; opacity: 0.7; }
.dark .add-btn { background: #1a5c48; }
.dark .add-btn:hover:not(:disabled) { background: #1e6b54; }

.add-btn-spinner {
  width: 13px;
  height: 13px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: white;
  border-radius: 50%;
  animation: elegant-spin 0.7s linear infinite;
}

/* ══════════════════════════════════════════════
   FLOATING CART
══════════════════════════════════════════════ */
.cart-fab {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 50;
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: var(--brand-green);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(27, 77, 62, 0.35);
  transition: transform 0.2s, box-shadow 0.2s;
  text-decoration: none;
}
.cart-fab:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(27, 77, 62, 0.45);
}
.dark .cart-fab {
  background: #1a5c48;
  box-shadow: 0 4px 16px rgba(0,0,0,0.5);
}

/* ══════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════ */
.toast-notification {
  position: fixed;
  bottom: 88px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 18px;
  border-radius: 14px;
  background: var(--brand-green);
  color: white;
  font-size: 0.875rem;
  font-weight: 500;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  white-space: nowrap;
  max-width: 90vw;
}
.toast-notification--error { background: #dc2626; }
.dark .toast-notification { background: #1a5c48; }
.dark .toast-notification--error { background: #b91c1c; }
.toast-close {
  background: transparent;
  border: none;
  color: rgba(255,255,255,0.75);
  cursor: pointer;
  font-size: 0.75rem;
  padding: 0;
  transition: color 0.15s;
}
.toast-close:hover { color: white; }
</style>