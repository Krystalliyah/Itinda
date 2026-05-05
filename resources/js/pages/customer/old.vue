<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
  ArrowLeft,
  ChevronDown,
  Minus,
  Plus,
  ShoppingBag,
  Store,
  Trash2,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

import Header from '@/components/Header.vue'
import CustomerNav from '@/components/navigation/CustomerNav.vue'
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue'
import Sidebar from '@/components/Sidebar.vue'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogTrigger,
} from '@/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { useSidebar } from '@/composables/useSidebar'


type StoreInfo = {
  id: number
  name: string
  description: string
  logo_url?: string | null
}

type ProductInfo = {
  id: number
  name: string
  description: string
  price: number
  image_url?: string | null
}

type CartItem = {
  id: number
  store: StoreInfo
  product: ProductInfo
  quantity: number
  selected: boolean
  added_at: string // mock timestamp for sorting
}

const { isCollapsed } = useSidebar()
const contentClass = computed(() => ({
  'dashboard-content': true,
  'sidebar-collapsed': isCollapsed.value,
}))

/** Search (same vibe as Orders) */
type SearchBy = 'product_name' | 'store_name'
const search = ref('')
const searchBy = ref<SearchBy>('product_name')
const searchByLabel = computed(() =>
  searchBy.value === 'product_name' ? 'Product Name' : 'Store Name'
)

/** Filter + Sort */
type SortBy = 'recent' | 'price_asc' | 'price_desc' | 'name_asc'
const sortBy = ref<SortBy>('recent')

const shopFilter = ref<number | 'all'>('all') // simple filter by store

/** Modal */
const preorderOpen = ref(false)

/** Mock cart data (replace later with props) */
const cartItems = ref<CartItem[]>([
  {
    id: 1,
    store: {
      id: 11,
      name: 'Green Basket Grocery',
      description: 'Fresh produce and essentials delivered to your neighborhood.',
      logo_url:
        'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?auto=format&fit=crop&w=128&q=60',
    },
    product: {
      id: 9001,
      name: 'Fresh Carrots (1kg)',
      description: 'Crisp and sweet carrots, perfect for stews and salads.',
      price: 120,
      image_url:
        'https://images.unsplash.com/photo-1582515073490-39981397c445?auto=format&fit=crop&w=256&q=60',
    },
    quantity: 2,
    selected: true,
    added_at: '2026-02-26T10:12:00+08:00',
  },
  {
    id: 2,
    store: {
      id: 11,
      name: 'Green Basket Grocery',
      description: 'Fresh produce and essentials delivered to your neighborhood.',
      logo_url:
        'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?auto=format&fit=crop&w=128&q=60',
    },
    product: {
      id: 9002,
      name: 'Organic Eggs (12pcs)',
      description: 'Farm-fresh eggs, rich yolks and great for baking.',
      price: 265,
      image_url:
        'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?auto=format&fit=crop&w=256&q=60',
    },
    quantity: 1,
    selected: false,
    added_at: '2026-02-26T10:15:00+08:00',
  },
  {
    id: 3,
    store: {
      id: 21,
      name: 'Bake & Brew',
      description: 'Artisan breads and coffee made fresh every morning.',
      logo_url:
        'https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=128&q=60',
    },
    product: {
      id: 9103,
      name: 'Chocolate Muffin',
      description: 'Moist chocolate muffin with dark chocolate chips.',
      price: 105,
      image_url:
        'https://images.unsplash.com/photo-1509440159598-0249088772ff?auto=format&fit=crop&w=256&q=60',
    },
    quantity: 3,
    selected: true,
    added_at: '2026-02-26T09:40:00+08:00',
  },
  {
    id: 4,
    store: {
      id: 21,
      name: 'Bake & Brew',
      description: 'Artisan breads and coffee made fresh every morning.',
      logo_url:
        'https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=128&q=60',
    },
    product: {
      id: 9104,
      name: 'Cinnamon Roll',
      description: 'Buttery cinnamon roll topped with cream cheese glaze.',
      price: 105,
      image_url:
        'https://images.unsplash.com/photo-1608198093002-ad4e005484ec?auto=format&fit=crop&w=256&q=60',
    },
    quantity: 1,
    selected: false,
    added_at: '2026-02-26T09:41:00+08:00',
  },
])

/** Derived options */
const stores = computed(() => {
  const map = new Map<number, StoreInfo>()
  for (const item of cartItems.value) {
    if (!map.has(item.store.id)) map.set(item.store.id, item.store)
  }
  return Array.from(map.values()).sort((a, b) => a.name.localeCompare(b.name))
})

/** Helpers */
const normalized = (v: string) => v.toLowerCase().trim()

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(
    amount
  )

/** Filtering + sorting */
const filteredSortedItems = computed(() => {
  const q = normalized(search.value)
  let list = [...cartItems.value]

  if (shopFilter.value !== 'all') {
    list = list.filter((i) => i.store.id === shopFilter.value)
  }

  if (q) {
    list = list.filter((i) => {
      const byProduct = normalized(i.product.name).includes(q)
      const byStore = normalized(i.store.name).includes(q)
      return searchBy.value === 'product_name' ? byProduct : byStore
    })
  }

  list.sort((a, b) => {
    if (sortBy.value === 'recent') {
      return new Date(b.added_at).getTime() - new Date(a.added_at).getTime()
    }
    return a.product.name.localeCompare(b.product.name)
  })

  return list
})

/** Group by store (after filters) */
const groupedByStore = computed(() => {
  const groups = new Map<number, { store: StoreInfo; items: CartItem[] }>()
  for (const item of filteredSortedItems.value) {
    const key = item.store.id
    if (!groups.has(key)) groups.set(key, { store: item.store, items: [] })
    groups.get(key)!.items.push(item)
  }

  // stable order: store name asc
  return Array.from(groups.values()).sort((a, b) =>
    a.store.name.localeCompare(b.store.name)
  )
})

/** =========================
 *  SELECTION + TOTAL LOGIC
 *  ========================= */

/** Global selected count */
const selectedCount = computed(
  () => cartItems.value.filter((i) => i.selected).length
)

/** Global select all */
const allSelected = computed({
  get() {
    return (
      cartItems.value.length > 0 &&
      selectedCount.value === cartItems.value.length
    )
  },
  set(val: boolean) {
    cartItems.value = cartItems.value.map((i) => ({
      ...i,
      selected: val,
    }))
  },
})

/** Store select all */
const storeAllSelected = (storeId: number) => {
  const items = cartItems.value.filter((i) => i.store.id === storeId)
  return items.length > 0 && items.every((i) => i.selected)
}

const setStoreSelected = (storeId: number, val: boolean) => {
  cartItems.value = cartItems.value.map((i) =>
    i.store.id === storeId ? { ...i, selected: val } : i
  )
}

/** Store subtotal (selected only) */
const storeSubtotal = (storeId: number) => {
  return cartItems.value
    .filter((i) => i.store.id === storeId && i.selected)
    .reduce((sum, i) => sum + i.product.price * i.quantity, 0)
}

/** Global total */
const totalSelected = computed(() =>
  cartItems.value
    .filter((i) => i.selected)
    .reduce((sum, i) => sum + i.product.price * i.quantity, 0)
)

/** =========================
 *  REMOVE LOGIC (MODAL ONLY)
 *  ========================= */

const removeSelectedGlobal = () => {
  removeModalScope.value = 'global'
  removeModalOpen.value = true
}

const removeSelectedStore = (storeId: number) => {
  removeModalScope.value = storeId
  removeModalOpen.value = true
}

/** Confirmation modal state */
const removeModalOpen = ref(false)
const removeModalScope = ref<'global' | number>('global')

/** Item level */
const toggleItemSelected = (id: number, val: boolean) => {
  cartItems.value = cartItems.value.map((i) =>
    i.id === id ? { ...i, selected: val } : i
  )
}

const removeItem = (id: number) => {
  cartItems.value = cartItems.value.filter((i) => i.id !== id)
}

const adjustQty = (id: number, delta: number) => {
  cartItems.value = cartItems.value.map((i) => {
    if (i.id !== id) return i
    const nextQty = Math.max(1, i.quantity + delta)
    return { ...i, quantity: nextQty }
  })
}
</script>

<template>
  <Head title="Cart" />

  <div class="dashboard-wrapper">
    <Header />

    <Sidebar role="customer">
      <CustomerNav />
      <template #icons>
        <CustomerNavIcons />
      </template>
    </Sidebar>

    <main :class="contentClass">
      <!-- pb so sticky bar doesn't overlap content -->
      <div class="p-6 space-y-6 pb-28">
        <!-- Back link -->
        <div>
          <Link href="/customer/orders">
            <Button variant="ghost" class="gap-2 text-[#245c4a]">
              <ArrowLeft class="w-4 h-4" />
              Back to My Orders
            </Button>
          </Link>
        </div>

        <!-- Page Title -->
        <div class="space-y-1">
          <h1 class="text-2xl font-semibold text-[#245c4a]">My Cart</h1>
          <p class="text-muted-foreground">
            View and manage items in your cart.
          </p>
        </div>

        <!-- Search / Filter / Sort -->
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <!-- Search (same pattern as Orders) -->
          <div class="flex w-full max-w-1xl gap-3">
            <DropdownMenu>
              <div class="relative w-full max-w-3xl cursor-pointer">
                <Input
                  v-model="search"
                  :placeholder="`Search by ${searchByLabel}...`"
                  class="pr-10"
                />
                <DropdownMenuTrigger as-child>
                  <button
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-xl hover:bg-muted transition"
                    aria-label="Select search type"
                  >
                    <ChevronDown class="w-4 h-4 opacity-60" />
                  </button>
                </DropdownMenuTrigger>
              </div>

              <DropdownMenuContent align="end" class="w-44">
                <DropdownMenuItem @click="searchBy = 'product_name'">
                  Search by Product
                </DropdownMenuItem>
                <DropdownMenuItem @click="searchBy = 'store_name'">
                  Search by Store
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <!-- Sort -->
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="gap-2">
                  <span class="text-sm">
                    {{
                      sortBy === 'recent'
                        ? 'Recently added' : 'Name: A to Z'
                    }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-60" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" class="w-56">
                <DropdownMenuItem @click="sortBy = 'recent'">Recently added</DropdownMenuItem>
                <DropdownMenuItem @click="sortBy = 'name_asc'">Name: A to Z</DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        <!-- Empty State -->
        <Card v-if="cartItems.length === 0" class="rounded-xl shadow-sm">
          <CardHeader>
            <CardTitle class="text-base">Your cart is empty</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <p class="text-sm text-muted-foreground">
              Browse stores or products to add items to your cart.
            </p>
            <div class="flex flex-wrap gap-2">
              <Link href="/customer/products">
                <Button class="gap-2 bg-[#245c4a] hover:bg-[#1B4D3E]">
                  <ShoppingBag class="h-4 w-4" />
                  Browse Products
                </Button>
              </Link>
              <Link href="/customer/stores">
                <Button variant="outline" class="gap-2">
                  <Store class="h-4 w-4" />
                  Browse Stores
                </Button>
              </Link>
            </div>
          </CardContent>
        </Card>

        <!-- Grouped Cart -->
        <div v-else class="space-y-4">
          <Card
            v-for="group in groupedByStore"
            :key="group.store.id"
            class="rounded-xl shadow-sm"
          >
            <CardHeader class="space-y-3">
              <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                  <div class="h-10 w-10 overflow-hidden rounded-lg border border-border bg-muted">
                    <img
                      v-if="group.store.logo_url"
                      :src="group.store.logo_url"
                      :alt="group.store.name"
                      class="h-full w-full object-cover"
                      loading="lazy"
                    />
                  </div>

                  <div class="space-y-1">
                    <CardTitle class="text-base">{{ group.store.name }}</CardTitle>
                    <p class="text-xs text-muted-foreground">
                      {{ group.store.description }}
                    </p>
                  </div>
                </div>

                <!-- Shop select all -->
                <div class="flex items-center gap-2">
                  <Checkbox
                    :checked="storeAllSelected(group.store.id)"
                    @update:checked="(v: boolean) => setStoreSelected(group.store.id, v)"
                  />
                  <span class="text-xs text-muted-foreground">Select All</span>
                </div>
              </div>
            </CardHeader>

            <CardContent class="space-y-3">
              <!-- Items table-like rows -->
              <div
                v-for="item in group.items"
                :key="item.id"
                class="flex flex-col gap-3 rounded-lg border border-border p-3 sm:flex-row sm:items-center"
              >
                <!-- checkbox -->
                <div class="flex items-center gap-3">
                  <Checkbox
                    :checked="item.selected"
                    @update:checked="(v: boolean) => toggleItemSelected(item.id, v)"
                  />
                </div>

                <!-- image -->
                <div class="h-14 w-14 overflow-hidden rounded-lg bg-muted border border-border shrink-0">
                  <img
                    v-if="item.product.image_url"
                    :src="item.product.image_url"
                    :alt="item.product.name"
                    class="h-full w-full object-cover"
                    loading="lazy"
                  />
                </div>

                <!-- details -->
                <div class="min-w-0 flex-1 space-y-1">
                  <p class="text-sm font-medium">{{ item.product.name }}</p>
                  <p class="text-xs text-muted-foreground line-clamp-2">
                    {{ item.product.description }}
                  </p>
                  <p class="text-sm font-semibold">{{ formatCurrency(item.product.price) }}</p>
                </div>

                <!-- qty + actions -->
                <div class="flex items-center justify-between gap-2 sm:justify-end sm:min-w-[220px]">
                  <div class="flex items-center gap-2">
                    <Button
                      variant="outline"
                      size="icon"
                      class="h-9 w-9"
                      aria-label="Decrease quantity"
                      @click="adjustQty(item.id, -1)"
                    >
                      <Minus class="h-4 w-4" />
                    </Button>
                    <div class="min-w-[40px] text-center text-sm font-medium">
                      {{ item.quantity }}
                    </div>
                    <Button
                      variant="outline"
                      size="icon"
                      class="h-9 w-9"
                      aria-label="Increase quantity"
                      @click="adjustQty(item.id, 1)"
                    >
                      <Plus class="h-4 w-4" />
                    </Button>
                  </div>

                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9 text-red-600 hover:text-red-700"
                    aria-label="Remove item"
                    @click="removeItem(item.id)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </div>
              <!-- Store Footer -->
                <div
                class="flex items-center justify-between border-t border-border pt-3 mt-3"
                >
                <!-- Remove selected (store) -->
                <Button
                    variant="ghost"
                    class="gap-2 text-red-600 hover:text-red-700"
                    :disabled="storeSubtotal(group.store.id) === 0"
                    @click="removeSelectedStore(group.store.id)"
                >
                    <Trash2 class="h-4 w-4" />
                    Remove Selected
                </Button>

                <!-- Subtotal -->
                <div class="text-sm">
                    <span class="text-muted-foreground">Subtotal:</span>
                    <span class="ml-2 font-semibold text-[#245c4a]">
                    {{ formatCurrency(storeSubtotal(group.store.id)) }}
                    </span>
                </div>
                </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Sticky Bottom Bar -->
      <div
        class="fixed bottom-0 left-0 right-0 border-t border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80"
      >
        <div
          class="mx-auto flex max-w-7xl flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
          :class="[{ 'ml-[250px]': !isCollapsed }, { 'ml-[70px]': isCollapsed }]"
        >
          <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
              <Checkbox v-model:checked="allSelected" />
              <span class="text-sm font-medium">Select all</span>
              <span class="text-xs text-muted-foreground">
                ({{ selectedCount }} selected)
              </span>
            </div>

            <Button
              variant="outline"
              class="gap-2"
              :disabled="selectedCount === 0"
              @click="removeSelectedGlobal"
            >
              <Trash2 class="h-4 w-4" />
              Remove selected
            </Button>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <div class="text-sm">
              <span class="text-muted-foreground">Total:</span>
              <span class="ml-2 font-semibold text-[#245c4a]">
                {{ formatCurrency(totalSelected) }}
              </span>
            </div>

            <Dialog v-model:open="preorderOpen">
              <DialogTrigger as-child>
                <Button
                  class="gap-2 bg-[#C5A059] text-black hover:bg-[#d9b87a]"
                  :disabled="selectedCount === 0"
                >
                  Pre-Order
                </Button>
              </DialogTrigger>

              <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                  <DialogTitle>Pre-Order</DialogTitle>
                  <DialogDescription>
                    This modal is intentionally blank for now. Later, you can add pickup date,
                    payment option, and confirmation steps here.
                  </DialogDescription>
                </DialogHeader>

                <div class="pt-2">
                  <div class="rounded-lg border border-border bg-muted/30 p-4 text-sm text-muted-foreground">
                    Placeholder content
                  </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                  <Button variant="outline" @click="preorderOpen = false">Close</Button>
                </div>
              </DialogContent>
            </Dialog>
          </div>
        </div>
      </div>

        <!-- Remove Confirmation Modal -->
        <Dialog v-model:open="removeModalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
            <DialogTitle>Remove Selected Items</DialogTitle>
            <DialogDescription>
                This action cannot be undone. This will permanently remove all selected
                items.
            </DialogDescription>
            </DialogHeader>

            <div class="flex justify-end gap-2 pt-4">
            <Button variant="outline" @click="removeModalOpen = false">
                Cancel
            </Button>
            <Button
                class="bg-red-600 text-white hover:bg-red-700"
            >
                Remove All
            </Button>
            </div>
        </DialogContent>
        </Dialog>
    </main>
  </div>
</template>