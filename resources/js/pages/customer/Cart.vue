<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'

import Header from '@/components/Header.vue'
import Sidebar from '@/components/Sidebar.vue'
import CustomerNav from '@/components/navigation/CustomerNav.vue'
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue'
import { useSidebar } from '@/composables/useSidebar'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogTrigger,
} from '@/components/ui/dialog'

import {
  ArrowLeft,
  ChevronDown,
  Minus,
  Plus,
  ShoppingBag,
  Store,
  Trash2,
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'

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
  added_at: string
}

const { isCollapsed } = useSidebar()
const contentClass = computed(() => ({
  'dashboard-content': true,
  'sidebar-collapsed': isCollapsed.value,
}))

type SearchBy = 'product_name' | 'store_name'
const search = ref('')
const searchBy = ref<SearchBy>('product_name')
const searchByLabel = computed(() =>
  searchBy.value === 'product_name' ? 'Product Name' : 'Store Name'
)

type SortBy = 'recent' | 'price_asc' | 'price_desc' | 'name_asc'
const sortBy = ref<SortBy>('recent')

const shopFilter = ref<number | 'all'>('all')

const preorderOpen = ref(false)

const cartItems = ref<CartItem[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

const selectedIds = ref<number[]>([])

const fetchCart = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await fetch('/customer/cart-data', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    const items: CartItem[] = []
    data.data.forEach((storeCart: any) => {
      storeCart.items.forEach((item: any) => {
        items.push({
          id: item.id,
          store: {
            id: storeCart.store_id,
            name: storeCart.store_name ?? storeCart.store_id,
            description: '',
            logo_url: storeCart.store_logo ?? null,
          },
          product: {
            id: item.product.id,
            name: item.product.name,
            description: '',
            price: parseFloat(item.product.price),
            image_url: item.product.image_path ? `/storage/${item.product.image_path}` : null,
          },
          quantity: item.quantity,
          selected: false,
          added_at: new Date().toISOString(),
        })
      })
    })

    cartItems.value = items
    selectedIds.value = []
  } catch (err) {
    console.error('Error fetching cart:', err)
    error.value = err instanceof Error ? err.message : 'Failed to load cart'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCart()
})

const stores = computed(() => {
  const map = new Map<number, StoreInfo>()
  for (const item of cartItems.value) {
    if (!map.has(item.store.id)) map.set(item.store.id, item.store)
  }
  return Array.from(map.values()).sort((a, b) => a.name.localeCompare(b.name))
})

const normalized = (v: string) => v.toLowerCase().trim()

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount)

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

const groupedByStore = computed(() => {
  const groups = new Map<number, { store: StoreInfo; items: CartItem[] }>()
  for (const item of filteredSortedItems.value) {
    const key = item.store.id
    if (!groups.has(key)) groups.set(key, { store: item.store, items: [] })
    groups.get(key)!.items.push(item)
  }

  return Array.from(groups.values()).sort((a, b) =>
    a.store.name.localeCompare(b.store.name)
  )
})

const selectedCount = computed(() => selectedIds.value.length)

const allSelected = computed(() =>
  cartItems.value.length > 0 &&
  cartItems.value.every((i) => selectedIds.value.includes(i.id))
)

const toggleAllSelected = (val: boolean) => {
  selectedIds.value = val ? cartItems.value.map((i) => i.id) : []
}

const storeAllSelected = (storeId: number) => {
  const items = cartItems.value.filter((i) => i.store.id === storeId)
  return items.length > 0 && items.every((i) => selectedIds.value.includes(i.id))
}

const setStoreSelected = (storeId: number, val: boolean) => {
  const storeIds = cartItems.value
    .filter((i) => i.store.id === storeId)
    .map((i) => i.id)

  if (val) {
    selectedIds.value = Array.from(new Set([...selectedIds.value, ...storeIds]))
  } else {
    selectedIds.value = selectedIds.value.filter((id) => !storeIds.includes(id))
  }
}

const selectedGroupedByStore = computed(() => {
  const groups = new Map<number, { store: StoreInfo; items: CartItem[] }>()
  for (const item of cartItems.value.filter((i) => selectedIds.value.includes(i.id))) {
    const key = item.store.id
    if (!groups.has(key)) groups.set(key, { store: item.store, items: [] })
    groups.get(key)!.items.push(item)
  }
  return Array.from(groups.values()).sort((a, b) =>
    a.store.name.localeCompare(b.store.name)
  )
})

const storeSubtotal = (storeId: number) =>
  cartItems.value
    .filter((i) => i.store.id === storeId)
    .reduce((sum, i) => sum + i.product.price * i.quantity, 0)

const totalSelected = computed(() =>
  cartItems.value
    .filter((i) => selectedIds.value.includes(i.id))
    .reduce((sum, i) => sum + i.product.price * i.quantity, 0)
)

const removeSelectedGlobal = () => {
  removeModalScope.value = 'global'
  removeModalOpen.value = true
}

const removeSelectedStore = (storeId: number) => {
  removeModalScope.value = storeId
  removeModalOpen.value = true
}

const clearAllOpen = ref(false)

const confirmClearAll = async () => {
  // Dismiss any undo toast (delete already fired)
  if (pendingDelete.value) {
    clearTimeout(pendingDelete.value.timeoutId)
    pendingDelete.value = null
    undoVisible.value = false
  }

  cartItems.value = []
  selectedIds.value = []
  clearAllOpen.value = false

  try {
    const res = await fetch('/customer/cart', {
      method: 'DELETE',
      headers: jsonHeaders(),
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
  } catch (err) {
    console.error('Failed to clear cart:', err)
    await fetchCart()
  }
}

const removeModalOpen = ref(false)
const removeModalScope = ref<'global' | number>('global')

const toggleItemSelected = (id: number, val: boolean) => {
  if (val) {
    if (!selectedIds.value.includes(id)) {
      selectedIds.value = [...selectedIds.value, id]
    }
  } else {
    selectedIds.value = selectedIds.value.filter((x) => x !== id)
  }
}

const csrfToken = () =>
  (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? ''

const jsonHeaders = () => ({
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'X-CSRF-TOKEN': csrfToken(),
})

type UndoItem = {
  item: CartItem
  index: number
  selectedBefore: boolean
  timeoutId: ReturnType<typeof setTimeout>
}

const pendingDelete = ref<UndoItem | null>(null)
const undoVisible = ref(false)
const undoMessage = ref('')
const UNDO_TOAST_MS = 5000

const removeItem = async (id: number) => {
  const index = cartItems.value.findIndex((i) => i.id === id)
  if (index === -1) return

  const item = cartItems.value[index]
  const selectedBefore = selectedIds.value.includes(id)

  // Dismiss any previous undo toast (its delete already fired)
  if (pendingDelete.value) {
    clearTimeout(pendingDelete.value.timeoutId)
    pendingDelete.value = null
    undoVisible.value = false
  }

  // Remove from UI immediately
  cartItems.value = cartItems.value.filter((i) => i.id !== id)
  selectedIds.value = selectedIds.value.filter((x) => x !== id)

  // Fire the DELETE right away — backend is always in sync
  try {
    const res = await fetch(`/customer/cart/${id}`, {
      method: 'DELETE',
      headers: jsonHeaders(),
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
  } catch (err) {
    console.error('Failed to remove item:', err)
    // Restore on failure
    const restored = [...cartItems.value]
    restored.splice(Math.min(index, restored.length), 0, item)
    cartItems.value = restored
    if (selectedBefore) selectedIds.value = [...selectedIds.value, id]
    return
  }

  // Show undo toast — undo will re-add via POST
  undoMessage.value = `${item.product.name} removed`
  undoVisible.value = true

  const timeoutId = setTimeout(() => {
    pendingDelete.value = null
    undoVisible.value = false
  }, UNDO_TOAST_MS)

  pendingDelete.value = { item, index, selectedBefore, timeoutId }
}

const undoRemoveItem = async () => {
  const pending = pendingDelete.value
  if (!pending) return

  clearTimeout(pending.timeoutId)
  pendingDelete.value = null
  undoVisible.value = false

  // Re-add the item to the backend
  try {
    const res = await fetch('/customer/cart/add', {
      method: 'POST',
      headers: jsonHeaders(),
      body: JSON.stringify({
        store_id: pending.item.store.id,
        product_id: pending.item.product.id,
        quantity: pending.item.quantity,
      }),
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)

    // Restore in local state at original position
    const restored = [...cartItems.value]
    restored.splice(Math.min(pending.index, restored.length), 0, pending.item)
    cartItems.value = restored

    if (pending.selectedBefore) {
      selectedIds.value = Array.from(new Set([...selectedIds.value, pending.item.id]))
    }
  } catch (err) {
    console.error('Failed to undo remove:', err)
    await fetchCart()
  }
}

const adjustQty = async (id: number, delta: number) => {
  const item = cartItems.value.find((i) => i.id === id)
  if (!item) return
  const nextQty = Math.max(1, item.quantity + delta)
  cartItems.value = cartItems.value.map((i) =>
    i.id === id ? { ...i, quantity: nextQty } : i
  )
  try {
    const res = await fetch(`/customer/cart/${id}`, {
      method: 'PUT',
      headers: jsonHeaders(),
      body: JSON.stringify({ quantity: nextQty }),
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
  } catch (err) {
    console.error('Failed to update quantity:', err)
    await fetchCart()
  }
}

const confirmRemoveSelected = async () => {
  // Dismiss any undo toast (delete already fired)
  if (pendingDelete.value) {
    clearTimeout(pendingDelete.value.timeoutId)
    pendingDelete.value = null
    undoVisible.value = false
  }

  const isGlobalAll =
    removeModalScope.value === 'global' &&
    cartItems.value.every((i) => selectedIds.value.includes(i.id))

  if (isGlobalAll) {
    // Fast path: clear everything via the dedicated clear endpoint
    cartItems.value = []
    selectedIds.value = []
    removeModalOpen.value = false

    try {
      const res = await fetch('/customer/cart', {
        method: 'DELETE',
        headers: jsonHeaders(),
      })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
    } catch (err) {
      console.error('Failed to clear cart:', err)
      await fetchCart()
    }
    return
  }

  const ids = cartItems.value
    .filter((i) =>
      removeModalScope.value === 'global'
        ? selectedIds.value.includes(i.id)
        : selectedIds.value.includes(i.id) && i.store.id === removeModalScope.value
    )
    .map((i) => i.id)

  if (ids.length === 0) {
    removeModalOpen.value = false
    return
  }

  cartItems.value = cartItems.value.filter((i) => !ids.includes(i.id))
  selectedIds.value = selectedIds.value.filter((id) => !ids.includes(id))
  removeModalOpen.value = false

  try {
    const res = await fetch('/customer/cart/bulk', {
      method: 'DELETE',
      headers: jsonHeaders(),
      body: JSON.stringify({ ids }),
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
  } catch (err) {
    console.error('Failed to bulk remove:', err)
    await fetchCart()
  }
}

const preorderSubmitting = ref(false)

const submitPreorder = async () => {
  if (selectedIds.value.length === 0 || preorderSubmitting.value) return

  try {
    preorderSubmitting.value = true

    const res = await fetch('/customer/cart/preorder', {
      method: 'POST',
      headers: jsonHeaders(),
      body: JSON.stringify({
        cart_ids: selectedIds.value,
      }),
    })

    const data = await res.json()

    if (!res.ok) {
      throw new Error(data.message || `HTTP ${res.status}`)
    }

    preorderOpen.value = false
    selectedIds.value = []

    await fetchCart()

    toast.success(data.message || 'Preorder placed successfully.')
    // optional:
    // window.location.href = '/customer/orders'
  } catch (err) {
    console.error('Failed to preorder:', err)
    toast.error(err instanceof Error ? err.message : 'Failed to place preorder.')
  } finally {
    preorderSubmitting.value = false
  }
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
      <div class="p-6 space-y-6 pb-28">
        <div>
          <Link href="/customer/orders">
            <Button variant="ghost" class="gap-2 text-[#245c4a]">
              <ArrowLeft class="w-4 h-4" />
              Back to My Orders
            </Button>
          </Link>
        </div>

        <div class="space-y-1">
          <h1 class="text-2xl font-semibold text-[#245c4a]">My Cart</h1>
          <p class="text-muted-foreground">
            View and manage items in your cart.
          </p>
        </div>

        <div class="flex flex-col gap-2 sm:gap-3 md:flex-row md:items-center md:justify-between">
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

        <div v-if="loading" class="text-center py-12">
          <p class="text-muted-foreground">Loading cart...</p>
        </div>

        <div v-else-if="error" class="text-center py-12">
          <p class="text-red-600 mb-4">{{ error }}</p>
          <Button @click="fetchCart" variant="outline">
            Try Again
          </Button>
        </div>

        <Card v-else-if="cartItems.length === 0" class="rounded-xl shadow-sm">
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

                <div class="flex items-center gap-2">
                  <input
                    type="checkbox"
                    :checked="storeAllSelected(group.store.id)"
                    @change="setStoreSelected(group.store.id, ($event.target as HTMLInputElement).checked)"
                    class="h-4 w-4 rounded border-gray-300"
                  />
                  <span class="text-xs text-muted-foreground">Select All</span>
                </div>
              </div>
            </CardHeader>

            <CardContent class="space-y-3">
              <div
                v-for="item in group.items"
                :key="item.id"
                class="flex flex-col gap-2 rounded-lg border border-border p-2 sm:p-3 sm:flex-row sm:items-center sm:gap-3"
              >
                <div class="flex items-center gap-3">
                  <input
                    type="checkbox"
                    :checked="selectedIds.includes(item.id)"
                    @change="toggleItemSelected(item.id, ($event.target as HTMLInputElement).checked)"
                    class="h-4 w-4 rounded border-gray-300"
                  />
                </div>

                <div class="h-12 w-12 overflow-hidden rounded-lg bg-muted border border-border shrink-0 sm:h-14 sm:w-14">
                  <img
                    v-if="item.product.image_url"
                    :src="item.product.image_url"
                    :alt="item.product.name"
                    class="h-full w-full object-cover"
                    loading="lazy"
                  />
                </div>

                <div class="min-w-0 flex-1 space-y-1">
                  <p class="text-sm font-medium">{{ item.product.name }}</p>
                  <p class="text-xs text-muted-foreground line-clamp-2">
                    {{ item.product.description }}
                  </p>
                  <p class="text-sm font-semibold">{{ formatCurrency(item.product.price) }}</p>
                </div>

                <div class="flex items-center justify-between gap-1 sm:gap-2 sm:justify-end sm:min-w-[200px]">
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
                    class="h-9 w-9 text-muted-foreground hover:text-rose-500 dark:hover:text-rose-400"
                    aria-label="Remove item"
                    @click="removeItem(item.id)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </div>

              <div class="flex items-center justify-between border-t border-border pt-3 mt-3">
                <Button
                  variant="ghost"
                  class="gap-2 text-muted-foreground hover:text-rose-500 dark:hover:text-rose-400"
                  :disabled="!cartItems.some(i => selectedIds.includes(i.id) && i.store.id === group.store.id)"
                  @click="removeSelectedStore(group.store.id)"
                >
                  <Trash2 class="h-4 w-4" />
                  Remove Selected
                </Button>

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

        <div
          v-if="undoVisible && pendingDelete"
          class="fixed bottom-24 right-4 z-50 rounded-lg border bg-background px-4 py-3 shadow-lg"
        >
          <div class="flex items-center gap-3">
            <span class="text-sm">{{ undoMessage }}</span>
            <Button variant="outline" size="sm" @click="undoRemoveItem">
              Undo
            </Button>
          </div>
        </div>
      </div>

      <div
        class="fixed bottom-0 left-0 right-0 border-t border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80"
      >
        <div
          class="mx-auto flex max-w-7xl flex-col gap-2 px-3 py-3 sm:gap-3 sm:px-6 sm:py-4 sm:flex-row sm:items-center sm:justify-between"
          :class="[{ 'ml-[250px]': !isCollapsed }, { 'ml-[70px]': isCollapsed }]"
        >
          <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleAllSelected(($event.target as HTMLInputElement).checked)"
                class="h-4 w-4 rounded border-gray-300"
              />
              <span class="text-sm font-medium">Select all</span>
              <span class="text-xs text-muted-foreground">
                ({{ selectedCount }} selected)
              </span>
            </div>

            <Button
              variant="outline"
              class="gap-2 text-muted-foreground hover:text-rose-500 dark:hover:text-rose-400 border-border hover:border-rose-300 dark:hover:border-rose-500/30"
              :disabled="cartItems.length === 0"
              @click="clearAllOpen = true"
            >
              <Trash2 class="h-4 w-4" />
              Remove all products
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

              <DialogContent class="sm:max-w-3xl mt-8 z-[1000]">
                <DialogHeader>
                  <DialogTitle>Pre-Order Summary</DialogTitle>
                  <DialogDescription>
                    Review your selected items grouped by shop. You can’t modify items here.
                  </DialogDescription>
                </DialogHeader>

                <div class="max-h-[60vh] overflow-auto pr-1">
                  <div
                    v-if="selectedGroupedByStore.length === 0"
                    class="rounded-lg border border-border bg-muted/30 p-4 text-sm text-muted-foreground"
                  >
                    No items selected.
                  </div>

                  <div v-else class="space-y-4">
                    <Card
                      v-for="group in selectedGroupedByStore"
                      :key="group.store.id"
                      class="rounded-xl shadow-sm"
                    >
                      <CardHeader class="space-y-2">
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

                          <div class="text-sm font-semibold text-[#245c4a]">
                            Subtotal: {{ formatCurrency(storeSubtotal(group.store.id)) }}
                          </div>
                        </div>
                      </CardHeader>

                      <CardContent class="space-y-3">
                        <div
                          v-for="item in group.items"
                          :key="item.id"
                          class="flex items-start gap-3 rounded-lg border border-border p-3"
                        >
                          <div class="h-14 w-14 overflow-hidden rounded-lg bg-muted border border-border shrink-0">
                            <img
                              v-if="item.product.image_url"
                              :src="item.product.image_url"
                              :alt="item.product.name"
                              class="h-full w-full object-cover"
                              loading="lazy"
                            />
                          </div>

                          <div class="min-w-0 flex-1 space-y-1">
                            <p class="text-sm font-medium">{{ item.product.name }}</p>
                            <p class="text-xs text-muted-foreground line-clamp-2">
                              {{ item.product.description }}
                            </p>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
                              <span>
                                Price:
                                <span class="font-medium text-foreground">
                                  {{ formatCurrency(item.product.price) }}
                                </span>
                              </span>
                              <span>
                                Qty:
                                <span class="font-medium text-foreground">{{ item.quantity }}</span>
                              </span>
                              <span>
                                Subtotal:
                                <span class="font-medium text-foreground">
                                  {{ formatCurrency(item.product.price * item.quantity) }}
                                </span>
                              </span>
                            </div>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  </div>
                </div>

                <div
                  class="mt-4 flex flex-col gap-3 border-t border-border pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                  <div class="text-sm">
                    <span class="text-muted-foreground">Overall Total:</span>
                    <span class="ml-2 font-semibold text-[#245c4a]">
                      {{ formatCurrency(totalSelected) }}
                    </span>
                  </div>

                  <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="preorderOpen = false">Cancel</Button>
                    <Button 
                      class="bg-[#C5A059] text-black hover:bg-[#d9b87a]"
                      :disabled="selectedIds.length === 0 || preorderSubmitting"
                      @click="submitPreorder">
                      {{ preorderSubmitting ? 'Submitting...' : 'Confirm Pre-Order' }}
                    </Button>
                  </div>
                </div>
              </DialogContent>
            </Dialog>
          </div>
        </div>
      </div>

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
              @click="confirmRemoveSelected"
            >
              Remove All
            </Button>
          </div>
        </DialogContent>
      </Dialog>

      <Dialog v-model:open="clearAllOpen">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Remove All Products</DialogTitle>
            <DialogDescription>
              This will permanently clear your entire cart. This action cannot be undone.
            </DialogDescription>
          </DialogHeader>

          <div class="flex justify-end gap-2 pt-4">
            <Button variant="outline" @click="clearAllOpen = false">
              Cancel
            </Button>
            <Button
              class="bg-red-600 text-white hover:bg-red-700"
              @click="confirmClearAll"
            >
              Clear Cart
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </main>
  </div>
</template>