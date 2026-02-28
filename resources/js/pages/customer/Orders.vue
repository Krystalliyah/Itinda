<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

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

type OrderStatus = 'pending' | 'confirmed' | 'ready' | 'completed' | 'cancelled'

type Store = {
  id: number
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
  store_id: number
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
  { key: 'pending' as const, label: 'Pending' },
  { key: 'confirmed' as const, label: 'Accepted' },
  { key: 'ready' as const, label: 'Ready' },
  { key: 'completed' as const, label: 'Completed' },
  { key: 'cancelled' as const, label: 'Cancelled' },
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
const orders = ref<Order[]>([
  {
    id: 101,
    customer_id: 1,
    store_id: 11,
    order_number: 'ORD-2026-00021',
    status: 'pending',
    total_amount: 785,
    pickup_date: null,
    notes: 'Please pack separately if possible.',
    created_at: '2026-02-25T13:45:00+08:00',
    updated_at: '2026-02-25T13:45:00+08:00',
    store: {
      id: 11,
      name: 'Green Basket Grocery',
      logo_url:
        'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?auto=format&fit=crop&w=128&q=60',
    },
    items: [
      {
        id: 1,
        inventory_id: 501,
        quantity: 2,
        created_at: '2026-02-25T13:45:10+08:00',
        product: {
          id: 9001,
          name: 'Fresh Carrots (1kg)',
          description: 'Crisp and sweet carrots, perfect for stews and salads.',
          price: 120,
          image_url:
            'https://images.unsplash.com/photo-1582515073490-39981397c445?auto=format&fit=crop&w=256&q=60',
        },
      },
      {
        id: 2,
        inventory_id: 502,
        quantity: 1,
        created_at: '2026-02-25T13:45:10+08:00',
        product: {
          id: 9002,
          name: 'Organic Eggs (12pcs)',
          description: 'Farm-fresh eggs, rich yolks and great for baking.',
          price: 265,
          image_url:
            'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?auto=format&fit=crop&w=256&q=60',
        },
      },
      {
        id: 3,
        inventory_id: 503,
        quantity: 1,
        created_at: '2026-02-25T13:45:10+08:00',
        product: {
          id: 9003,
          name: 'Brown Rice (2kg)',
          description: 'Healthy whole grain rice with a nutty flavor.',
          price: 280,
          image_url:
            'https://images.unsplash.com/photo-1604909052743-94e07f05f6f1?auto=format&fit=crop&w=256&q=60',
        },
      },
    ],
  },
  {
    id: 102,
    customer_id: 1,
    store_id: 21,
    order_number: 'ORD-2026-00018',
    status: 'confirmed',
    total_amount: 520,
    pickup_date: '2026-02-26T16:00:00+08:00',
    notes: null,
    created_at: '2026-02-24T08:50:00+08:00',
    updated_at: '2026-02-24T09:11:00+08:00',
    store: {
      id: 21,
      name: 'Bake & Brew',
      logo_url:
        'https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=128&q=60',
    },
    items: [
      {
        id: 4,
        inventory_id: 601,
        quantity: 2,
        created_at: '2026-02-24T08:50:10+08:00',
        product: {
          id: 9101,
          name: 'Sourdough Loaf',
          description: 'Slow-fermented sourdough with a crispy crust.',
          price: 180,
          image_url:
            'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?auto=format&fit=crop&w=256&q=60',
        },
      },
      {
        id: 5,
        inventory_id: 602,
        quantity: 1,
        created_at: '2026-02-24T08:50:10+08:00',
        product: {
          id: 9102,
          name: 'Cold Brew (500ml)',
          description: 'Smooth and strong cold brew coffee, lightly sweet.',
          price: 160,
          image_url:
            'https://images.unsplash.com/photo-1528826194825-0d76c2b7c97b?auto=format&fit=crop&w=256&q=60',
        },
      },
    ],
  },
  {
    id: 103,
    customer_id: 1,
    store_id: 11,
    order_number: 'ORD-2026-00012',
    status: 'ready',
    total_amount: 310,
    pickup_date: '2026-02-26T11:30:00+08:00',
    notes: 'Call me when ready.',
    created_at: '2026-02-20T15:20:00+08:00',
    updated_at: '2026-02-20T15:32:00+08:00',
    store: {
      id: 11,
      name: 'Green Basket Grocery',
      logo_url:
        'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?auto=format&fit=crop&w=128&q=60',
    },
    items: [
      {
        id: 6,
        inventory_id: 504,
        quantity: 1,
        created_at: '2026-02-20T15:20:10+08:00',
        product: {
          id: 9004,
          name: 'Bananas (1kg)',
          description: 'Ripe bananas, great for snacks and smoothies.',
          price: 120,
          image_url:
            'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=256&q=60',
        },
      },
      {
        id: 7,
        inventory_id: 505,
        quantity: 1,
        created_at: '2026-02-20T15:20:10+08:00',
        product: {
          id: 9005,
          name: 'Peanut Butter (340g)',
          description: 'Creamy peanut butter with no added preservatives.',
          price: 190,
          image_url:
            'https://images.unsplash.com/photo-1615486364462-ef6363c1f52c?auto=format&fit=crop&w=256&q=60',
        },
      },
    ],
  },
  {
    id: 104,
    customer_id: 1,
    store_id: 31,
    order_number: 'ORD-2026-00005',
    status: 'completed',
    total_amount: 999,
    pickup_date: '2026-02-10T17:00:00+08:00',
    notes: null,
    created_at: '2026-02-10T09:40:00+08:00',
    updated_at: '2026-02-10T17:15:00+08:00',
    store: {
      id: 31,
      name: 'Daily Essentials',
      logo_url:
        'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=128&q=60',
    },
    items: [
      {
        id: 8,
        inventory_id: 701,
        quantity: 3,
        created_at: '2026-02-10T09:40:10+08:00',
        product: {
          id: 9201,
          name: 'Toilet Paper (12 rolls)',
          description: 'Soft, strong, and reliable everyday essentials.',
          price: 333,
          image_url:
            'https://images.unsplash.com/photo-1583946099379-f9c9cb8bc030?auto=format&fit=crop&w=256&q=60',
        },
      },
    ],
  },
  {
    id: 105,
    customer_id: 1,
    store_id: 21,
    order_number: 'ORD-2026-00003',
    status: 'cancelled',
    total_amount: 420,
    pickup_date: '2026-02-08T14:00:00+08:00',
    notes: 'Wrong item selected.',
    created_at: '2026-02-08T12:10:00+08:00',
    updated_at: '2026-02-08T12:30:00+08:00',
    store: {
      id: 21,
      name: 'Bake & Brew',
      logo_url:
        'https://images.unsplash.com/photo-1521017432531-fbd92d768814?auto=format&fit=crop&w=128&q=60',
    },
    items: [
      {
        id: 9,
        inventory_id: 603,
        quantity: 2,
        created_at: '2026-02-08T12:10:10+08:00',
        product: {
          id: 9103,
          name: 'Chocolate Muffin',
          description: 'Moist chocolate muffin with dark chocolate chips.',
          price: 105,
          image_url:
            'https://images.unsplash.com/photo-1509440159598-0249088772ff?auto=format&fit=crop&w=256&q=60',
        },
      },
      {
        id: 10,
        inventory_id: 604,
        quantity: 2,
        created_at: '2026-02-08T12:10:10+08:00',
        product: {
          id: 9104,
          name: 'Cinnamon Roll',
          description: 'Buttery cinnamon roll topped with cream cheese glaze.',
          price: 105,
          image_url:
            'https://images.unsplash.com/photo-1608198093002-ad4e005484ec?auto=format&fit=crop&w=256&q=60',
        },
      },
    ],
  },
])

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
  if (s === 'confirmed') return 'Accepted'
  return s.charAt(0).toUpperCase() + s.slice(1)
}

const statusClasses = (s: OrderStatus) => {
  // simple badge-like styles using Tailwind + your theme colors
  switch (s) {
    case 'pending':
      return 'bg-muted text-foreground'
    case 'confirmed':
      return 'bg-[#C5A059]/10 text-[#C5A059] border border-[#C5A059]/20'
    case 'ready':
      return 'bg-[#245c4a]/10 text-[#245c4a] border border-[#245c4a]/20'
    case 'completed':
      return 'bg-emerald-500/10 text-emerald-700 border border-emerald-500/20 dark:text-emerald-300'
    case 'cancelled':
      return 'bg-red-500/10 text-red-700 border border-red-500/20 dark:text-red-300'
  }
}

/** Actions (mock for now) */
const cancelOrder = (orderId: number) => {
  const order = orders.value.find((o) => o.id === orderId)
  if (!order) return
  if (order.status !== 'pending') return

  const ok = window.confirm('Cancel this order?')
  if (!ok) return

  orders.value = orders.value.map((o) =>
    o.id === orderId
      ? { ...o, status: 'cancelled', updated_at: new Date().toISOString() }
      : o
  )
}
</script>

<template>
  <Head title="My Orders" />

  <div class="dashboard-wrapper">
    <Header />

    <Sidebar role="customer">
      <CustomerNav />
      <template #icons>
        <CustomerNavIcons />
      </template>
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
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded-md hover:bg-muted transition"
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
          <div v-if="filteredOrders.length === 0" class="rounded-xl border border-border p-8">
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
