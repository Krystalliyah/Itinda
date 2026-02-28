<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import PlaceholderPage from '@/components/PlaceholderPage.vue';
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
const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value
}));

// Mock Products
const products = ref([
  {
    id: 1,
    product_name: 'Organic Fuji Apples (1kg Pack)',
    description: 'Premium imported apples.',
    category_id: 1,
    image_url: 'https://picsum.photos/400?1',
    unit_price: 3.99,
    stock_level: 20,
    sold_count: 120,
    rating: 4.5,
    is_available: true,
    is_active: true,
    store: {
      id: 1,
      name: 'Emerald Fresh Market',
      logo: 'https://ui-avatars.com/api/?name=Emerald+Fresh&background=245c4a&color=fff'
    }
  },
  {
    id: 2,
    product_name: 'Whole Fresh Milk 1L',
    description: 'Fresh dairy milk.',
    category_id: 2,
    image_url: 'https://picsum.photos/400?2',
    unit_price: 2.49,
    stock_level: 3,
    sold_count: 45,
    rating: 4.2,
    is_available: false,
    is_active: true,
    store: {
      id: 2,
      name: 'Golden Harvest Grocery',
      logo: 'https://ui-avatars.com/api/?name=Golden+Harvest&background=245c4a&color=fff'
    }
  },
])

const searchProduct = ref('')
const selectedCategory = ref<'all' | number>('all')
const sortBy = ref<'name' | 'price_low' | 'price_high'>('name')

const filteredProducts = computed(() => {
  let result = products.value.filter(product => {

    const matchesSearch =
      product.product_name
        .toLowerCase()
        .includes(searchProduct.value.toLowerCase())

    const matchesCategory =
      selectedCategory.value === 'all'
        ? true
        : product.category_id === selectedCategory.value

    return (
      matchesSearch &&
      matchesCategory &&
      product.is_active
    )
  })

  // Sorting
  if (sortBy.value === 'price_low') {
    result.sort((a, b) => a.unit_price - b.unit_price)
  } else if (sortBy.value === 'price_high') {
    result.sort((a, b) => b.unit_price - a.unit_price)
  } else {
    result.sort((a, b) =>
      a.product_name.localeCompare(b.product_name)
    )
  }

  return result
})

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
                            <DropdownMenuItem @click="selectedCategory = 1">
                            Fruits
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="selectedCategory = 2">
                            Dairy
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="selectedCategory = 3">
                            Snacks
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
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
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
                    class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded-md"
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
                    :disabled="!product.is_available"
                    class="w-full mt-2 mb-4 bg-[#245c4a] hover:bg-[#1B4D3E] text-white"
                    >
                    Add to Cart
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
            href="/customer/orders"
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
</template>
