<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ChevronLeft, ShoppingCart, Store, Star, Package } from 'lucide-vue-next';

const props = defineProps<{
  storeId: string;
  productId: number;
}>();

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
  'dashboard-content': true,
  'sidebar-collapsed': isCollapsed.value,
}));

const product = ref<any>(null);
const loading = ref(true);
const error = ref<string | null>(null);

const quantity = ref(1);

const maxQty = computed(() =>
  product.value?.stock_level > 0 ? product.value.stock_level : Infinity
);

const increment = () => {
  if (quantity.value < maxQty.value) quantity.value++;
};
const decrement = () => {
  if (quantity.value > 1) quantity.value--;
};
const setQty = (val: number) => {
  quantity.value = Math.min(Math.max(1, val), maxQty.value === Infinity ? val : maxQty.value);
};

const fetchProduct = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await fetch(`/customer/products-data/${props.storeId}/${props.productId}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();
    product.value = data.data;
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load product';
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProduct);

// Toast
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
const showToast = (message: string, type: 'success' | 'error' = 'success') => {
  if (toastTimer) clearTimeout(toastTimer);
  toast.value = { message, type };
  toastTimer = setTimeout(() => { toast.value = null; }, 3000);
};

const addingToCart = ref(false);
const addToCart = async () => {
  if (!product.value) return;
  addingToCart.value = true;
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
        store_id: product.value.store.id,
        product_id: product.value.id,
        quantity: quantity.value,
      }),
    });
    if (!response.ok) throw new Error('Failed to add to cart');
    showToast(`${quantity.value}× "${product.value.product_name}" added to cart!`);
    quantity.value = 1;
  } catch {
    showToast('Failed to add to cart. Please try again.', 'error');
  } finally {
    addingToCart.value = false;
  }
};
</script>

<template>
  <Head :title="product?.product_name ?? 'Product Details'" />

  <div class="dashboard-wrapper">
    <Header />
    <Sidebar role="customer">
      <CustomerNav />
      <template #icons>
        <CustomerNavIcons />
      </template>
    </Sidebar>

    <main :class="contentClass">
      <div class="p-6 max-w-5xl mx-auto space-y-6">

        <!-- Back -->
        <Link href="/customer/products" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-[#245c4a] transition">
          <ChevronLeft class="w-4 h-4" />
          Back to Products
        </Link>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-24 text-muted-foreground">
          Loading product...
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-24 text-red-600">
          {{ error }}
        </div>

        <!-- Product Layout -->
        <div v-else-if="product" class="grid grid-cols-1 md:grid-cols-2 gap-8">

          <!-- Left: Image -->
          <div class="rounded-2xl overflow-hidden border border-border bg-gray-50 dark:bg-muted aspect-square">
            <img
              :src="product.image_url"
              :alt="product.product_name"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Right: Details -->
          <div class="flex flex-col gap-4">

            <!-- Category + Store -->
            <div class="flex items-center gap-2 flex-wrap">
              <Badge v-if="product.category_name" variant="secondary">
                {{ product.category_name }}
              </Badge>
              <Link
                :href="`/customer/stores/${product.store.id}`"
                class="flex items-center gap-1.5 text-xs text-muted-foreground hover:text-[#245c4a] transition"
              >
                <img :src="product.store.logo" class="h-5 w-5 rounded-full object-cover" />
                {{ product.store.name }}
              </Link>
            </div>

            <!-- Name -->
            <h1 class="text-2xl font-semibold text-foreground leading-snug">
              {{ product.product_name }}
            </h1>

            <!-- Rating + Sold -->
            <div class="flex items-center gap-4 text-sm text-muted-foreground">
              <span class="flex items-center gap-1">
                <Star class="w-4 h-4 fill-yellow-400 text-yellow-400" />
                {{ product.rating }}
              </span>
              <span class="flex items-center gap-1">
                <Package class="w-4 h-4" />
                {{ product.sold_count }} sold
              </span>
            </div>

            <!-- Price -->
            <div class="text-3xl font-bold text-[#245c4a]">
              ${{ product.unit_price.toFixed(2) }}
            </div>

            <!-- Stock status -->
            <div class="text-sm">
              <span v-if="!product.is_available" class="text-red-500 font-medium">Out of stock</span>
              <span v-else-if="product.stock_level <= 5" class="text-orange-500 font-medium">
                Only {{ product.stock_level }} left in stock
              </span>
              <span v-else class="text-green-600 font-medium">In stock</span>
            </div>

            <!-- Description -->
            <p v-if="product.description" class="text-sm text-muted-foreground leading-relaxed border-t border-border pt-4">
              {{ product.description }}
            </p>

            <!-- Quantity + Add to Cart -->
            <div class="border-t border-border pt-4 space-y-3">
              <p class="text-sm font-medium text-foreground">Quantity</p>

              <div class="flex items-center gap-3">
                <button
                  class="h-9 w-9 rounded-md border border-border flex items-center justify-center text-lg hover:bg-muted transition disabled:opacity-40"
                  :disabled="quantity <= 1"
                  @click="decrement"
                  aria-label="Decrease quantity"
                >−</button>

                <input
                  type="number"
                  :value="quantity"
                  :min="1"
                  :max="product.stock_level > 0 ? product.stock_level : undefined"
                  class="w-14 h-9 text-center text-sm border border-border rounded-md bg-background focus:outline-none focus:ring-1 focus:ring-[#245c4a] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                  @change="setQty(Number(($event.target as HTMLInputElement).value))"
                  @keydown.up.prevent="increment"
                  @keydown.down.prevent="decrement"
                  aria-label="Quantity"
                />

                <button
                  class="h-9 w-9 rounded-md border border-border flex items-center justify-center text-lg hover:bg-muted transition disabled:opacity-40"
                  :disabled="product.stock_level > 0 && quantity >= product.stock_level"
                  @click="increment"
                  aria-label="Increase quantity"
                >+</button>

                <span v-if="product.stock_level > 0" class="text-xs text-muted-foreground">
                  / {{ product.stock_level }} available
                </span>
              </div>

              <Button
                size="lg"
                class="w-full bg-[#245c4a] hover:bg-[#1B4D3E] text-white gap-2"
                :disabled="!product.is_available || addingToCart"
                @click="addToCart"
              >
                <ShoppingCart class="w-4 h-4" />
                {{ addingToCart ? 'Adding...' : product.is_available ? 'Add to Cart' : 'Out of Stock' }}
              </Button>
            </div>

          </div>
        </div>

      </div>

      <!-- Floating cart button -->
      <Link href="/customer/cart" class="fixed bottom-6 right-6 z-50">
        <div class="h-14 w-14 rounded-full bg-[#245c4a] hover:bg-[#1B4D3E] text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-1">
          <ShoppingCart class="h-5 w-5" />
        </div>
      </Link>
    </main>
  </div>

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
        class="fixed bottom-24 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3 px-5 py-3 rounded-2xl shadow-xl text-sm font-medium"
        :class="toast.type === 'success' ? 'bg-[#245c4a] text-white' : 'bg-red-600 text-white'"
      >
        <span class="text-lg">{{ toast.type === 'success' ? '🛒' : '⚠️' }}</span>
        {{ toast.message }}
        <button class="ml-2 opacity-70 hover:opacity-100 transition-opacity" @click="toast = null">✕</button>
      </div>
    </Transition>
  </Teleport>
</template>
