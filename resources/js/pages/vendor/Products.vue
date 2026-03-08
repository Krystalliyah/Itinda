<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import VendorLayout from '@/layouts/VendorLayout.vue';

type Product = {
  id: number;
  name: string;
  description: string | null;
  price: number;
  stock: number;
  created_at: string;
  image_url?: string | null; // optional if backend sends it
};

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

const props = defineProps<{
  products: {
    data: Product[];
    links?: PaginationLink[]; // Laravel paginator
    meta?: any;
  };
}>();

const showModal = ref(false);
const editingProduct = ref<Product | null>(null);
const search = ref('');

const imagePreview = ref<string | null>(null);

const form = useForm<{
  name: string;
  description: string;
  price: number | string;
  stock: number | string;
  image: File | null;
  _method?: 'put';
}>({
  name: '',
  description: '',
  price: 0,
  stock: 0,
  image: null,
});

const products = computed(() => props.products.data || []);
const paginationLinks = computed(() => props.products.links || []);

const filteredProducts = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!q) return products.value;

  return products.value.filter((p) => {
    return (
      p.name.toLowerCase().includes(q) ||
      (p.description ?? '').toLowerCase().includes(q)
    );
  });
});

const totalProducts = computed(() => products.value.length);
const lowStockCount = computed(() => products.value.filter(p => (p.stock ?? 0) <= 5).length);

function stockBadge(stock: number) {
  if (stock <= 0) return { label: 'Out of stock', cls: 'bg-red-50 text-red-700 border border-red-200' };
  if (stock <= 5) return { label: 'Low stock', cls: 'bg-amber-50 text-amber-700 border border-amber-200' };
  return { label: 'In stock', cls: 'bg-emerald-50 text-emerald-700 border border-emerald-200' };
}

function openCreateModal() {
  editingProduct.value = null;
  form.reset();
  form.clearErrors();
  imagePreview.value = null;
  showModal.value = true;
}

function openEditModal(product: Product) {
  editingProduct.value = product;
  form.clearErrors();
  form.name = product.name;
  form.description = product.description || '';
  form.price = product.price;
  form.stock = product.stock;
  form.image = null;
  imagePreview.value = product.image_url || null;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  form.reset();
  form.clearErrors();
  editingProduct.value = null;
  imagePreview.value = null;
}

function onPickImage(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0] ?? null;
  form.image = file;

  if (file) {
    imagePreview.value = URL.createObjectURL(file);
  } else {
    imagePreview.value = editingProduct.value?.image_url || null;
  }
}

function submit() {
  if (editingProduct.value) {
    // For multipart + PUT, use POST + _method=put
    form.transform((data) => ({ ...data, _method: 'put' as const }));

    form.post(`/vendor/products/${editingProduct.value.id}`, {
      forceFormData: true,
      onSuccess: () => closeModal(),
    });
  } else {
    form.transform((data) => {
      const { _method, ...rest } = data as any;
      return rest;
    });

    form.post('/vendor/products', {
      forceFormData: true,
      onSuccess: () => closeModal(),
    });
  }
}

function deleteProduct(id: number) {
  if (confirm('Are you sure you want to delete this product?')) {
    router.delete(`/vendor/products/${id}`);
  }
}

</script>

<template>
  <Head title="Products" />

  <VendorLayout>
    <div class="p-4 sm:p-6 flex flex-col gap-4">

      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color:#245c4a">
            <span style="color:#C5A059">✦</span> Inventory
          </p>
          <h1 class="text-2xl font-semibold tracking-tight" style="color:#245c4a">Products</h1>
          <p class="text-sm text-muted-foreground mt-1">
            Manage products, pricing, images, and stock.
          </p>
        </div>

        <button
          @click="openCreateModal"
          class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-md text-white transition-opacity hover:opacity-90"
          style="background:#245c4a"
        >
          + Add Product
        </button>
      </div>

      <!-- Stats + Search -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Products (this page)</p>
          <p class="text-3xl font-semibold mt-1" style="color:#245c4a">{{ totalProducts }}</p>
        </div>

        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Low Stock (≤ 5)</p>
          <p class="text-3xl font-semibold mt-1" style="color:#C5A059">{{ lowStockCount }}</p>
          <p class="text-xs text-muted-foreground mt-1">Keep stock updated to avoid missed orders.</p>
        </div>

        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Search</label>
          <input
            v-model="search"
            placeholder="Search name or description..."
            class="mt-2 w-full px-3 py-2 rounded-md border border-border bg-white text-foreground focus:outline-none focus:ring-2"
            style="--tw-ring-color: rgba(36,92,74,.35);"
          />
          <p class="text-xs text-muted-foreground mt-2">
            Showing <span class="font-semibold">{{ filteredProducts.length }}</span> of <span class="font-semibold">{{ totalProducts }}</span>
          </p>
        </div>
      </div>

      <!-- Empty state -->
      <div
        v-if="filteredProducts.length === 0"
        class="bg-white rounded-xl border border-border shadow-sm p-10 text-center"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3"
             style="background:hsl(0 0% 96.1%)">
          <span class="text-xl">📦</span>
        </div>
        <p class="text-sm font-medium text-foreground">No products found</p>
        <p class="text-xs text-muted-foreground mt-1">
          Try a different search or add your first product.
        </p>

        <button
          @click="openCreateModal"
          class="mt-4 inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-md text-white transition-opacity hover:opacity-90"
          style="background:#245c4a"
        >
          + Add Product
        </button>
      </div>

      <template v-else>
        <!-- ✅ Mobile/Tablet: Card View -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 lg:hidden">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="bg-white rounded-xl border border-border shadow-sm p-4 transition-all hover:shadow-md"
          >
            <div class="flex items-start gap-3">
              <div
                class="w-12 h-12 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0"
                style="background:hsl(0 0% 96.1%); border:1px solid hsl(0 0% 92.1%)"
              >
                <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover" alt="" />
                <span v-else class="text-lg">🖼️</span>
              </div>

              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-foreground truncate">{{ product.name }}</p>
                <p class="text-xs text-muted-foreground mt-0.5 truncate">
                  {{ product.description || 'No description' }}
                </p>

                <div class="mt-2 flex items-center gap-2">
                  <span
                    class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="stockBadge(product.stock).cls"
                  >
                    {{ stockBadge(product.stock).label }}
                  </span>
                  <span class="text-xs text-muted-foreground">Stock: <span class="font-semibold text-foreground">{{ product.stock }}</span></span>
                </div>
              </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
              <div>
                <p class="text-xs text-muted-foreground">Price</p>
                <p class="text-sm font-semibold text-foreground">₱{{ Number(product.price).toFixed(2) }}</p>
              </div>
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="openEditModal(product)"
                  class="inline-flex items-center justify-center text-xs font-semibold px-3 py-1.5 rounded-md border border-border bg-white hover:bg-accent"
                  style="color:#245c4a"
                >
                  Edit
                </button>
                <button
                  @click="deleteProduct(product.id)"
                  class="inline-flex items-center justify-center text-xs font-semibold px-3 py-1.5 rounded-md border"
                  style="border-color:#fecdd3;background:#fff1f2;color:#9f1239"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ✅ Desktop: Table View -->
        <div class="hidden lg:block bg-white rounded-xl border border-border shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-border flex items-start justify-between">
            <div>
              <h2 class="text-sm font-semibold" style="color:#245c4a">Product List</h2>
              <p class="text-xs text-muted-foreground mt-0.5">View and manage your products.</p>
            </div>
            <div class="text-xs font-semibold px-2 py-1 rounded" style="background:#f5ead4;color:#7a5800">
              {{ filteredProducts.length }} shown
            </div>
          </div>

          <div class="w-full overflow-x-auto">
            <table class="min-w-[980px] w-full border-collapse">
              <thead>
                <tr style="background:hsl(0 0% 96.1%)">
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Product
                  </th>
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Description
                  </th>
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Price
                  </th>
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Stock
                  </th>
                  <th class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Actions
                  </th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="product in filteredProducts"
                  :key="product.id"
                  class="border-b border-border last:border-0 transition-colors hover:bg-gray-50"
                >
                  <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0"
                        style="background:hsl(0 0% 96.1%); border:1px solid hsl(0 0% 92.1%)"
                      >
                        <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover" alt="" />
                        <span v-else class="text-sm">🖼️</span>
                      </div>

                      <div class="min-w-0">
                        <p class="text-sm font-semibold text-foreground truncate">{{ product.name }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">#{{ product.id }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-5 py-4 text-sm text-slate-600">
                    {{ product.description || '—' }}
                  </td>

                  <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                    ₱{{ Number(product.price).toFixed(2) }}
                  </td>

                  <td class="px-5 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-semibold text-slate-900">{{ product.stock }}</span>
                      <span
                        class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full"
                        :class="stockBadge(product.stock).cls"
                      >
                        {{ stockBadge(product.stock).label }}
                      </span>
                    </div>
                  </td>

                  <td class="px-5 py-4 whitespace-nowrap text-right">
                    <div class="inline-flex items-center gap-2">
                      <button
                        @click="openEditModal(product)"
                        class="text-xs font-semibold px-2 py-1 rounded transition-colors"
                        style="color:#245c4a"
                        @mouseover="($event.currentTarget as HTMLElement).style.background = 'rgba(36,92,74,.10)'"
                        @mouseout="($event.currentTarget as HTMLElement).style.background = 'transparent'"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteProduct(product.id)"
                        class="text-xs font-semibold px-2 py-1 rounded transition-colors"
                        style="color:#9f1239"
                        @mouseover="($event.currentTarget as HTMLElement).style.background = '#fff1f2'"
                        @mouseout="($event.currentTarget as HTMLElement).style.background = 'transparent'"
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="paginationLinks.length" class="px-5 py-4 border-t border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-xs text-muted-foreground">
              Browse more products using pagination.
            </p>

            <nav class="inline-flex items-center gap-1 flex-wrap justify-end">
              <template v-for="(l, idx) in paginationLinks" :key="idx">
                <span
                  v-if="l.url === null"
                  class="text-xs font-semibold px-2 py-1 rounded border border-border"
                  style="color:hsl(0 0% 45.1%); background:hsl(0 0% 96.1%)"
                  v-html="l.label"
                />
                <Link
                  v-else
                  :href="l.url"
                  class="text-xs font-semibold px-2 py-1 rounded border border-border transition-colors"
                  :style="l.active
                    ? 'background:#245c4a;color:white;border-color:#245c4a'
                    : 'background:white;color:#245c4a'"
                  v-html="l.label"
                />
              </template>
            </nav>
          </div>
        </div>

        <!-- Pagination (mobile too) -->
        <div v-if="paginationLinks.length" class="lg:hidden bg-white rounded-xl border border-border shadow-sm p-4">
          <div class="flex items-center justify-between gap-2 flex-wrap">
            <p class="text-xs text-muted-foreground">Pages</p>
            <nav class="inline-flex items-center gap-1 flex-wrap justify-end">
              <template v-for="(l, idx) in paginationLinks" :key="idx">
                <span
                  v-if="l.url === null"
                  class="text-xs font-semibold px-2 py-1 rounded border border-border"
                  style="color:hsl(0 0% 45.1%); background:hsl(0 0% 96.1%)"
                  v-html="l.label"
                />
                <Link
                  v-else
                  :href="l.url"
                  class="text-xs font-semibold px-2 py-1 rounded border border-border transition-colors"
                  :style="l.active
                    ? 'background:#245c4a;color:white;border-color:#245c4a'
                    : 'background:white;color:#245c4a'"
                  v-html="l.label"
                />
              </template>
            </nav>
          </div>
        </div>
      </template>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>

        <div class="relative w-full max-w-lg bg-white rounded-xl border border-border shadow-lg overflow-hidden">
          <div class="px-5 py-4 border-b border-border flex items-start justify-between">
            <div>
              <h2 class="text-sm font-semibold" style="color:#245c4a">
                {{ editingProduct ? 'Edit Product' : 'Add Product' }}
              </h2>
              <p class="text-xs text-muted-foreground mt-0.5">
                {{ editingProduct ? 'Update details and optional thumbnail.' : 'Create a new product with an optional thumbnail.' }}
              </p>
            </div>

            <button type="button" @click="closeModal" class="text-xs font-semibold px-2 py-1 rounded" style="color:#6b7280">
              ✕
            </button>
          </div>

          <form @submit.prevent="submit" class="p-5 space-y-4">
            <!-- Image uploader -->
            <div class="flex items-start gap-4">
              <div
                class="w-20 h-20 rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0"
                style="background:hsl(0 0% 96.1%); border:1px solid hsl(0 0% 92.1%)"
              >
                <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" alt="" />
                <span v-else class="text-lg">🖼️</span>
              </div>

              <div class="flex-1">
                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">
                  Thumbnail (optional)
                </label>
                <input type="file" accept="image/*" @change="onPickImage" class="block w-full text-xs" />
                <p class="text-xs text-muted-foreground mt-1">JPG/PNG recommended (max 2MB).</p>
                <p v-if="form.errors.image" class="text-xs mt-1" style="color:#9f1239">{{ form.errors.image }}</p>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">
                Product Name
              </label>
              <input
                v-model="form.name"
                required
                class="w-full px-3 py-2 rounded-md border border-border bg-white text-foreground focus:outline-none focus:ring-2"
                style="--tw-ring-color: rgba(36,92,74,.35);"
              />
              <p v-if="form.errors.name" class="text-xs mt-1" style="color:#9f1239">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">
                Description
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 rounded-md border border-border bg-white text-foreground focus:outline-none focus:ring-2"
                style="--tw-ring-color: rgba(36,92,74,.35);"
              />
              <p v-if="form.errors.description" class="text-xs mt-1" style="color:#9f1239">{{ form.errors.description }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">
                  Price (₱)
                </label>
                <input
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  required
                  class="w-full px-3 py-2 rounded-md border border-border bg-white text-foreground focus:outline-none focus:ring-2"
                  style="--tw-ring-color: rgba(36,92,74,.35);"
                />
                <p v-if="form.errors.price" class="text-xs mt-1" style="color:#9f1239">{{ form.errors.price }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">
                  Stock
                </label>
                <input
                  v-model="form.stock"
                  type="number"
                  required
                  class="w-full px-3 py-2 rounded-md border border-border bg-white text-foreground focus:outline-none focus:ring-2"
                  style="--tw-ring-color: rgba(36,92,74,.35);"
                />
                <p v-if="form.errors.stock" class="text-xs mt-1" style="color:#9f1239">{{ form.errors.stock }}</p>
              </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
              <button
                type="button"
                @click="closeModal"
                class="inline-flex items-center justify-center text-xs font-semibold px-3 py-2 rounded-md border border-border bg-white text-foreground hover:bg-accent"
              >
                Cancel
              </button>

              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center justify-center text-xs font-semibold px-3 py-2 rounded-md text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                style="background:#245c4a"
              >
                {{ editingProduct ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </VendorLayout>
</template>