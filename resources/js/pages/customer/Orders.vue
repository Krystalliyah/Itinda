<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'

import Header from '@/components/Header.vue'
import Sidebar from '@/components/Sidebar.vue'
import CustomerNav from '@/components/navigation/CustomerNav.vue'
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue'
import { useSidebar } from '@/composables/useSidebar'

import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible'

import { ChevronDown, Search, ShoppingCart, XCircle } from 'lucide-vue-next'

// Status vocabulary matches customer_orders in the central DB
type OrderStatus = 'pending' | 'preparing' | 'ready_for_pickup' | 'picked_up' | 'cancelled'

type Store = {
  id: string
  name: string
  logo_url?: string | null
}

type Product = {
  id: number
  name: string
  description: string
  price: number
  image_url?: string | null
}

type OrderItem = {
  id: number
  inventory_id: number
  quantity: number
  product: Product
  created_at: string
}

type Order = {
  id: number
  customer_id: number
  store_id: string
  order_number: string
  status: OrderStatus
  total_amount: number
  pickup_date?: string | null
  notes?: string | null
  created_at: string
  updated_at: string
  store: Store
  items: OrderItem[]
}

const { isCollapsed } = useSidebar()
const contentClass = computed(() => ({
  'dashboard-content': true,
  'sidebar-collapsed': isCollapsed.value,
}))

/** Tabs */
const tabs = [
  { key: 'pending' as const,          label: 'Pending' },
  { key: 'preparing' as const,        label: 'Preparing' },
  { key: 'ready_for_pickup' as const, label: 'Ready for Pickup' },
  { key: 'picked_up' as const,        label: 'Picked Up' },
  { key: 'cancelled' as const,        label: 'Cancelled' },
]
const activeTab = ref<OrderStatus>('pending')

/** Search (dropdown inside input) */
type SearchBy = 'order_number' | 'store_name' | 'product_name'
const search = ref('')
const searchBy = ref<SearchBy>('order_number')

const searchByLabel = computed(() => {
  if (searchBy.value === 'order_number') return 'Order Number'
  if (searchBy.value === 'store_name') return 'Store Name'
  return 'Product Name'
})

/** Sort */
type SortBy = 'newest' | 'oldest'
const sortBy = ref<SortBy>('newest')

/** Expanded cards */
const expanded = ref<Set<number>>(new Set())
const isExpanded = (orderId: number) => expanded.value.has(orderId)
const toggleExpanded = (orderId: number) => {
  const next = new Set(expanded.value)
  if (next.has(orderId)) next.delete(orderId)
  else next.add(orderId)
  expanded.value = next
}

/** Mock data (replace later with Inertia props) */
const orders = ref<Order[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

// Fetch orders from API
const fetchOrders = async () => {
  try {
    loading.value = true
    error.value = null
    
    const response = await fetch('/customer/orders-data', {
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
    orders.value = data.data.map((order: any) => ({
      id: order.id,
      customer_id: order.customer_id,
      store_id: order.store_id,
      order_number: order.order_number,
      status: order.status,
      total_amount: order.total_amount,
      pickup_date: order.pickup_date,
      notes: order.notes,
      created_at: order.created_at,
      updated_at: order.updated_at,
      store: {
        id: order.store.id,
        name: order.store.name,
        logo_url: order.store.logo_url
      },
      items: order.items.map((item: any) => ({
        id: item.id,
        inventory_id: item.inventory_id,
        quantity: item.quantity,
        created_at: item.created_at,
        product: {
          id: item.product.id,
          name: item.product.name,
          description: item.product.description,
          price: item.product.price,
          image_url: item.product.image_url
        }
      }))
    }))
  } catch (err) {
    console.error('Error fetching orders:', err)
    error.value = err instanceof Error ? err.message : 'Failed to load orders'
  } finally {
    loading.value = false
  }
}

/** Filtering + sorting */
const normalized = (v: string) => v.toLowerCase().trim()

const filteredOrders = computed(() => {
  const q = normalized(search.value)
  const tabStatus = activeTab.value

  let list = orders.value.filter((o) => o.status === tabStatus)

  if (q) {
    list = list.filter((o) => {
      const orderNumber = normalized(o.order_number)
      const storeName = normalized(o.store?.name ?? '')
      const productNames = normalized(
        o.items.map((i) => i.product?.name ?? '').join(' ')
      )

      if (searchBy.value === 'order_number') return orderNumber.includes(q)
      if (searchBy.value === 'store_name') return storeName.includes(q)
      return productNames.includes(q)
    })
  }

  list = [...list].sort((a, b) => {
    const ta = new Date(a.created_at).getTime()
    const tb = new Date(b.created_at).getTime()
    return sortBy.value === 'newest' ? tb - ta : ta - tb
  })

  return list
})

/** Small helpers */
const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(
    amount
  )

const formatDateTime = (iso: string) =>
  new Intl.DateTimeFormat('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(iso))

const formatDateOnly = (iso: string) =>
  new Intl.DateTimeFormat('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  }).format(new Date(iso))

const statusLabel = (s: OrderStatus) => {
  const labels: Record<OrderStatus, string> = {
    pending:          'Pending',
    preparing:        'Preparing',
    ready_for_pickup: 'Ready for Pickup',
    picked_up:        'Picked Up',
    cancelled:        'Cancelled',
  }
  return labels[s] ?? s
}

const statusClasses = (s: OrderStatus) => {
  switch (s) {
    case 'pending':
      return 'bg-muted text-foreground'
    case 'preparing':
      return 'bg-blue-500/10 text-blue-700 border border-blue-500/20 dark:text-blue-300'
    case 'ready_for_pickup':
      return 'bg-[#C5A059]/10 text-[#C5A059] border border-[#C5A059]/20'
    case 'picked_up':
      return 'bg-emerald-500/10 text-emerald-700 border border-emerald-500/20 dark:text-emerald-300'
    case 'cancelled':
      return 'bg-red-500/10 text-red-700 border border-red-500/20 dark:text-red-300'
  }
}

/** Cancel order — hits the real API and syncs to tenant */
const cancelOrder = async (orderId: number) => {
  const order = orders.value.find((o) => o.id === orderId)
  if (!order || order.status !== 'pending') return
  if (!window.confirm('Cancel this order?')) return

  try {
    const response = await fetch(`/customer/orders/${orderId}/cancel`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
      },
    })
    if (!response.ok) {
      const body = await response.json()
      alert(body.message ?? 'Failed to cancel order.')
      return
    }
    // Refresh the list so status is accurate
    await fetchOrders()
  } catch {
    alert('Failed to cancel order. Please try again.')
  }
}

// Poll every 10 s so vendor status changes appear without a manual refresh
let pollTimer: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  fetchOrders()
  pollTimer = setInterval(fetchOrders, 10000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<template>
  <Head title="My Orders" />

  <div class="dashboard-wrapper">
    <Header />

    <Sidebar role="customer">
      <CustomerNav />
    </Sidebar>

    <main :class="contentClass">
      <div class="p-6 space-y-6">
        <!-- Page Title -->
        <div class="space-y-1">
          <h1 class="text-2xl font-semibold text-[#245c4a]">My Orders</h1>
          <p class="text-muted-foreground">
            View your order history, track order status, and manage your pre-orders.
          </p>
        </div>

        <!-- Tabs + View Cart -->
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-4 border-b border-border w-full md:w-auto">
            <button
              v-for="t in tabs"
              :key="t.key"
              type="button"
              class="relative pb-3 text-sm font-medium transition"
              :class="[
                activeTab === t.key
                  ? 'text-[#245c4a]'
                  : 'text-muted-foreground hover:text-foreground',
              ]"
              @click="activeTab = t.key"
            >
              {{ t.label }}
              <span
                v-if="activeTab === t.key"
                class="absolute left-0 -bottom-[1px] h-[2px] w-full bg-[#C5A059]"
              />
            </button>
          </div>

          <Link href="/customer/cart">
            <Button class="gap-2 bg-[#245c4a] hover:bg-[#1B4D3E]">
              <ShoppingCart class="h-4 w-4" />
              View Cart
            </Button>
          </Link>
        </div>

        <!-- Search + Sort (no filter for now) -->
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <!-- Search (matches your pattern) -->
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

              <DropdownMenuContent align="end" class="w-52">
                <DropdownMenuItem @click="searchBy = 'order_number'">
                  Search by Order Number
                </DropdownMenuItem>
                <DropdownMenuItem @click="searchBy = 'store_name'">
                  Search by Store Name
                </DropdownMenuItem>
                <DropdownMenuItem @click="searchBy = 'product_name'">
                  Search by Product Name
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Sort -->
          <div class="flex items-center gap-2">
            <span class="text-sm text-muted-foreground">Sort:</span>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="gap-2">
                  <span class="text-sm">
                    {{ sortBy === 'newest' ? 'Newest first' : 'Oldest first' }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-60" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" class="w-44">
                <DropdownMenuItem @click="sortBy = 'newest'">Newest first</DropdownMenuItem>
                <DropdownMenuItem @click="sortBy = 'oldest'">Oldest first</DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        <!-- Orders List -->
        <div class="space-y-4">
          <div v-if="loading" class="text-center py-12">
            <p class="text-muted-foreground">Loading orders...</p>
          </div>

          <div v-else-if="error" class="text-center py-12">
            <p class="text-red-600 mb-4">{{ error }}</p>
            <Button @click="fetchOrders" variant="outline">
              Try Again
            </Button>
          </div>

          <div v-else-if="filteredOrders.length === 0" class="rounded-xl border border-border p-8">
            <div class="flex items-center gap-3 text-muted-foreground">
              <Search class="h-5 w-5 opacity-70" />
              <p class="text-sm">
                No orders found in <span class="font-medium text-foreground">{{ statusLabel(activeTab) }}</span>.
              </p>
            </div>
          </div>

          <Card v-for="order in filteredOrders" :key="order.id" class="rounded-xl shadow-sm">
            <Collapsible>
              <CardHeader class="space-y-3">
                <div class="flex items-start justify-between gap-4">
                  <div class="flex items-start gap-3">
                    <div class="h-10 w-10 overflow-hidden rounded-lg border border-border bg-muted">
                      <img
                        v-if="order.store.logo_url"
                        :src="order.store.logo_url"
                        :alt="order.store.name"
                        class="h-full w-full object-cover"
                        loading="lazy"
                      />
                    </div>

                    <div class="space-y-1">
                      <CardTitle class="text-base">{{ order.store.name }}</CardTitle>
                      <p class="text-xs text-muted-foreground">
                        Order #
                        <span class="font-medium text-foreground">{{ order.order_number }}</span>
                        • Created {{ formatDateTime(order.created_at) }}
                      </p>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                      :class="statusClasses(order.status)"
                    >
                      {{ statusLabel(order.status) }}
                    </span>

                    <CollapsibleTrigger as-child>
                      <Button
                        variant="ghost"
                        size="icon"
                        class="h-9 w-9"
                        :aria-label="isExpanded(order.id) ? 'Collapse order' : 'Expand order'"
                      >
                        <ChevronDown
                          class="h-4 w-4 transition-transform"
                          :class="isExpanded(order.id) ? 'rotate-180' : 'rotate-0'"
                        />
                      </Button>
                    </CollapsibleTrigger>
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                  <div class="rounded-lg bg-muted/40 p-3">
                    <p class="text-xs text-muted-foreground">Total</p>
                    <p class="text-sm font-semibold">{{ formatCurrency(order.total_amount) }}</p>
                  </div>

                  <div class="rounded-lg bg-muted/40 p-3" v-if="order.status !== 'pending'">
                    <p class="text-xs text-muted-foreground">Pickup Date</p>
                    <p class="text-sm font-semibold" v-if="order.pickup_date">
                      {{ formatDateOnly(order.pickup_date) }}
                    </p>
                    <p class="text-sm text-muted-foreground" v-else>—</p>
                  </div>

                  <div class="rounded-lg bg-muted/40 p-3">
                    <p class="text-xs text-muted-foreground">Items</p>
                    <p class="text-sm font-semibold">{{ order.items.length }}</p>
                  </div>
                </div>

                <p v-if="order.notes" class="text-sm text-muted-foreground">
                  <span class="font-medium text-foreground">Notes:</span> {{ order.notes }}
                </p>
              </CardHeader>

              <CollapsibleContent>
                <CardContent class="space-y-4">
                  <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-[#245c4a] pt-10">Order Items</h3>

                    <div class="space-y-3">
                      <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-start gap-3 rounded-lg border border-border p-3"
                      >
                        <div class="h-14 w-14 overflow-hidden rounded-lg bg-muted border border-border">
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
                    </div>
                  </div>
                </CardContent>

                <CardFooter class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pt-5">
                  <div class="text-sm text-muted-foreground">
                    Last updated: <span class="text-foreground">{{ formatDateTime(order.updated_at) }}</span>
                  </div>

                  <div class="flex items-center gap-2">
                    <!-- Pending-only cancel -->
                    <Button
                      v-if="activeTab === 'pending' && order.status === 'pending'"
                      variant="destructive"
                      class="gap-2"
                      @click="cancelOrder(order.id)"
                    >
                      <XCircle class="h-4 w-4" />
                      Cancel Order
                    </Button>
                  </div>
                </CardFooter>
              </CollapsibleContent>
            </Collapsible>
          </Card>
        </div>
      </div>
    </main>
  </div>
</template>
