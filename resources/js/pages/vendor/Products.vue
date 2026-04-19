<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import VendorLayout from '@/layouts/VendorLayout.vue';
import { ConfirmationModal } from '@/components/ui/modal';
import { Star } from 'lucide-vue-next';

type Product = {
  id: number;
  product_name: string;
  description: string | null;
  category_name: string | null;
  barcode: string | null;
  price: number | null;
  stock: number | null;
  image_url?: string | null;
  is_active: boolean;
  created_at: string;
  total_reviews?: number;
  average_rating?: number;
};

type Category = {
  id: number;
  category_name: string;
  slug: string;
  description: string | null;
  color: string;
  parent_id: number | null;
  children?: Category[];
};

// Extended category type for display with level
type CategoryWithLevel = Category & {
  level?: number;
};

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

const props = defineProps<{
  products: {
    data: Product[];
    links?: PaginationLink[];
    meta?: any;
  };
  categories: Category[];
  totalProducts: number;
  activeProducts: number;
  search?: string;
}>();

// DEBUG: Log categories to see what's being received from the backend
console.log('=== DEBUG: Categories received from backend ===');
console.log('Raw categories:', props.categories);
console.log('Categories count:', props.categories.length);
console.log('First category sample:', props.categories[0]);

const showModal = ref(false);
const showDeleteModal = ref(false);
const pendingDeleteProduct = ref<Product | null>(null);
const deleteProcessing = ref(false);
const editingProduct = ref<Product | null>(null);
const search = ref(props.search || '');
const isSearching = ref(false);
const imagePreview = ref<string | null>(null);
const imageInputKey = ref(0);
let previewObjectUrl: string | null = null;
const categoryOpen = ref(false);
const categoryBtnEl = ref<HTMLElement | null>(null);
const categoryDropdownStyle = ref<Record<string, string>>({});

// Define form early since it's used in computed properties and functions
const form = useForm<{
  product_name: string;
  description: string;
  category_id: number | string;
  barcode: string;
  price: string;
  stock: string;
  image: File | null;
  is_active: boolean;
  _method?: 'put';
}>({
  product_name: '',
  description: '',
  category_id: '',
  barcode: '',
  price: '',
  stock: '0',
  image: null,
  is_active: true,
});

// Watch search and trigger server-side search
watch(search, (newSearch) => {
  // Reset to page 1 when searching
  isSearching.value = true;
  router.get('/vendor/products', { search: newSearch }, { 
    preserveState: true,
    onFinish: () => {
      isSearching.value = false;
    }
  });
}, { debounce: 300 } as any);

// Helper function to build category tree from flat list
function buildCategoryTree(flatCategories: Category[]): Category[] {
  const categoryMap = new Map<number, Category>();
  const roots: Category[] = [];

  // First pass: create map of all categories
  flatCategories.forEach(category => {
    categoryMap.set(category.id, { ...category, children: [] });
  });

  // Second pass: build tree structure
  flatCategories.forEach(category => {
    const node = categoryMap.get(category.id)!;
    if (category.parent_id && categoryMap.has(category.parent_id)) {
      const parent = categoryMap.get(category.parent_id)!;
      if (!parent.children) parent.children = [];
      parent.children.push(node);
    } else {
      roots.push(node);
    }
  });

  console.log('=== Built category tree ===');
  console.log('Root categories:', roots);
  
  return roots;
}

const categoriesTree = computed(() => {
  // Check if categories already have children structure
  if (props.categories.length > 0 && props.categories[0].children !== undefined) {
    console.log('Categories already have nested structure');
    return props.categories;
  }
  // Otherwise, build tree from flat list
  console.log('Building tree from flat categories');
  return buildCategoryTree(props.categories);
});

// Flatten categories for display and lookup with level tracking
const flattenedCategories = computed(() => {
  const flat: CategoryWithLevel[] = [];
  
  function flatten(categories: Category[], level: number = 0) {
    categories.forEach(category => {
      flat.push({ ...category, level });
      if (category.children && category.children.length) {
        flatten(category.children, level + 1);
      }
    });
  }
  
  flatten(categoriesTree.value);
  
  console.log('=== Flattened categories for dropdown ===');
  console.log('All categories with levels:', flat.map(c => ({ 
    id: c.id, 
    name: c.category_name, 
    level: c.level,
    parent_id: c.parent_id 
  })));
  
  return flat;
});

const selectedCategoryLabel = computed(() => {
  if (!form.category_id) return null;
  const category = flattenedCategories.value.find((c) => c.id === form.category_id);
  return category?.category_name ?? null;
});

function formatPrice(value: number | string | null | undefined) {
  const amount = Number(value ?? 0);
  return '₱' + amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const deleteProductDescription = computed(() => {
  if (!pendingDeleteProduct.value) {
    return 'Deleting this product will remove it from your catalog and inventory. This action cannot be undone.';
  }

  const item = pendingDeleteProduct.value;
  const stock = item.stock ?? 0;
  const price = Number(item.price ?? 0);
  const total = stock * price;

  return `Delete "${item.product_name}" permanently? It has ${stock} in stock at ${formatPrice(price)} each, totaling ${formatPrice(total)}. This action is tracked to your account and cannot be undone.`;
});

const deleteProductDetails = computed(() => {
  return 'This will permanently remove the product from your catalog, inventory, and all associated sales data. Any existing orders may be affected.';
});

function openCategoryDropdown() {
  if (categoryBtnEl.value) {
    const rect = categoryBtnEl.value.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const dropdownH = Math.min(220, flattenedCategories.value.length * 36 + 8);
    const showAbove = spaceBelow < dropdownH + 8;
    categoryDropdownStyle.value = {
      position: 'fixed',
      left: rect.left + 'px',
      width: rect.width + 'px',
      ...(showAbove
        ? { bottom: (window.innerHeight - rect.top) + 'px' }
        : { top: rect.bottom + 4 + 'px' }),
    };
  }
  categoryOpen.value = !categoryOpen.value;
}

function selectCategory(id: number) {
  form.category_id = id;
  categoryOpen.value = false;
  console.log('Selected category ID:', id);
}

const products = computed(() => props.products.data || []);
const paginationLinks = computed(() => props.products.links || []);
const categories = computed(() => props.categories);

function productStatusBadge(isActive: boolean) {
  return isActive
    ? {
        label: 'Active',
        cls: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
      }
    : {
        label: 'Archived',
        cls: 'bg-slate-100 text-slate-600 border border-slate-200',
      };
}

function revokePreview() {
  if (previewObjectUrl) {
    URL.revokeObjectURL(previewObjectUrl);
    previewObjectUrl = null;
  }
}

function openCreateModal() {
  editingProduct.value = null;
  form.reset();
  form.clearErrors();
  form.is_active = true;
  revokePreview();
  imagePreview.value = null;
  imageInputKey.value++;
  showModal.value = true;
}

function openEditModal(product: Product) {
  editingProduct.value = product;
  form.clearErrors();
  form.product_name = product.product_name;
  form.description = product.description || '';
  form.category_id =
    flattenedCategories.value.find((c) => c.category_name === product.category_name)?.id ?? '';
  form.barcode = product.barcode || '';
  form.price = product.price != null ? String(product.price) : '';
  form.stock = product.stock != null ? String(product.stock) : '0';
  form.image = null;
  form.is_active = product.is_active;
  revokePreview();
  imagePreview.value = product.image_url || null;
  imageInputKey.value++;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  form.reset();
  form.clearErrors();
  editingProduct.value = null;
  revokePreview();
  imagePreview.value = null;
}

function onPickImage(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0] ?? null;
  form.image = file;

  console.log('onPickImage:', { file, fileName: file?.name, fileSize: file?.size });

  revokePreview();
  if (file) {
    previewObjectUrl = URL.createObjectURL(file);
    imagePreview.value = previewObjectUrl;
  } else {
    imagePreview.value = editingProduct.value?.image_url || null;
  }
}

function submit() {
  console.log('submit:', {
    editing: !!editingProduct.value,
    image: form.image,
    imageName: form.image?.name,
    imageSize: form.image?.size,
    data: {
      product_name: form.product_name,
      description: form.description,
      category_id: form.category_id,
      barcode: form.barcode,
      price: form.price,
      stock: form.stock,
      is_active: form.is_active,
    },
  });

  if (editingProduct.value) {
    form.put(`/vendor/products/${editingProduct.value.id}`, {
      forceFormData: true,
      preserveState: true,
      onError: (errors) => console.log('upload errors', errors),
      onSuccess: () => closeModal(),
    });
  } else {
    form.post('/vendor/products', {
      forceFormData: true,
      preserveState: true,
      onError: (errors) => console.log('upload errors', errors),
      onSuccess: () => closeModal(),
    });
  }
}

function confirmDeleteProduct(product: Product) {
  pendingDeleteProduct.value = product;
  showDeleteModal.value = true;
}

function deleteProduct() {
  if (!pendingDeleteProduct.value) {
    return;
  }

  deleteProcessing.value = true;

  router.delete(`/vendor/products/${pendingDeleteProduct.value.id}`, {
    preserveState: true,
    onFinish: () => {
      deleteProcessing.value = false;
      showDeleteModal.value = false;
      pendingDeleteProduct.value = null;
    },
  });
}

function cancelDeleteProduct() {
  showDeleteModal.value = false;
  pendingDeleteProduct.value = null;
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
            <span style="color:#C5A059">✦</span> Catalog
          </p>
          <h1 class="text-2xl font-semibold tracking-tight" style="color:#245c4a">Products</h1>
          <p class="text-sm text-muted-foreground mt-1">
            Manage product names, descriptions, categories, barcodes, and images.
          </p>
        </div>

        <button
          @click="openCreateModal"
          class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl text-white transition-opacity hover:opacity-90"
          style="background:#245c4a"
        >
          + Add Product
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            Total Products
          </p>
          <p class="text-3xl font-semibold mt-1" style="color:#245c4a">{{ totalProducts }}</p>
        </div>

        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            Active Products
          </p>
          <p class="text-3xl font-semibold mt-1" style="color:#C5A059">
            {{ activeProducts }}
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            Active products can be used in store inventory.
          </p>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="bg-white rounded-xl border border-border shadow-sm px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex-1 min-w-0 relative">
          <input
            v-model="search"
            placeholder="Search name, description, category, or barcode..."
            class="w-full px-4 py-2.5 rounded-xl border border-border bg-gray-50/50 text-foreground focus:outline-none focus:ring-2 focus:bg-white transition-colors text-sm"
            style="--tw-ring-color: rgba(36,92,74,.35);"
          />
          <!-- Cute loading spinner -->
          <div v-if="isSearching" class="absolute right-3 top-1/2 -translate-y-1/2">
            <div class="w-5 h-5 rounded-full border-2 border-[#245C4A]/20 border-t-[#245C4A] animate-spin"></div>
          </div>
        </div>
        <div class="sm:text-right flex-shrink-0">
          <p class="text-xs text-muted-foreground bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
            Showing <span class="font-semibold">{{ products.length }}</span> of
            <span class="font-semibold">{{ totalProducts }}</span>
          </p>
        </div>
      </div>

      <!-- No Products State -->
      <div
        v-if="products.length === 0"
        class="bg-white rounded-xl border border-border shadow-sm p-10 text-center"
      >
        <div
          class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3"
          style="background:hsl(0 0% 96.1%)"
        >
          <span class="text-xl">📦</span>
        </div>
        <p class="text-sm font-medium text-foreground">No products found</p>
        <p class="text-xs text-muted-foreground mt-1">
          Try a different search or add your first product.
        </p>

        <button
          @click="openCreateModal"
          class="mt-4 inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl text-white transition-opacity hover:opacity-90"
          style="background:#245c4a"
        >
          + Add Product
        </button>
      </div>

      <template v-else>
        <!-- Mobile view -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 lg:hidden relative">
          <!-- Loading overlay -->
          <div v-if="isSearching" class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center rounded-xl col-span-full">
            <div class="flex flex-col items-center gap-3">
              <div class="w-12 h-12 rounded-full border-3 border-[#245C4A]/20 border-t-[#245C4A] animate-spin"></div>
              <p class="text-sm font-medium text-[#245C4A]">Searching...</p>
            </div>
          </div>

          <div
            v-for="product in products"
            :key="product.id"
            class="bg-white rounded-xl border border-border shadow-sm p-4 transition-all hover:shadow-md"
          >
            <div class="flex items-start gap-3">
              <div
                class="w-12 h-12 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0"
                style="background:hsl(0 0% 96.1%); border:1px solid hsl(0 0% 92.1%)"
              >
                <img
                  v-if="product.image_url"
                  :src="product.image_url"
                  class="w-full h-full object-cover"
                  alt=""
                />
                <span v-else class="text-lg">🖼️</span>
              </div>

              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-foreground truncate">
                  {{ product.product_name }}
                </p>
                <p class="text-xs text-muted-foreground mt-0.5 truncate">
                  {{ product.description || 'No description' }}
                </p>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <span
                    class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="productStatusBadge(product.is_active).cls"
                  >
                    {{ productStatusBadge(product.is_active).label }}
                  </span>
                  <span class="text-xs text-muted-foreground">
                    {{ product.category_name || 'Uncategorized' }}
                  </span>
                </div>

                <div class="mt-2 flex items-center gap-2">
                  <div class="flex items-center gap-0.5">
                    <Star v-for="i in 5" :key="i" 
                      :class="i <= (product.average_rating || 0) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                      class="w-3 h-3" />
                  </div>
                  <Link
                    :href="`/vendor/products/${product.id}/reviews`"
                    class="text-xs text-emerald-600 hover:text-emerald-800"
                  >
                    {{ product.total_reviews || 0 }} reviews
                  </Link>
                </div>

                <p class="text-xs text-muted-foreground mt-2">
                  Barcode: <span class="font-semibold text-foreground">{{ product.barcode || '—' }}</span>
                </p>
              </div>
            </div>

            <div class="mt-3 flex items-center justify-end gap-2">
              <button
                @click="openEditModal(product)"
                class="inline-flex items-center justify-center text-xs font-semibold px-3 py-1.5 rounded-xl border border-border bg-white hover:bg-accent"
                style="color:#245c4a"
              >
                Edit
              </button>
              <button
                @click="confirmDeleteProduct(product)"
                class="inline-flex items-center justify-center text-xs font-semibold px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors"
              >
                Delete
              </button>
            </div>
          </div>
        </div>

        <!-- Desktop table view -->
        <div class="hidden lg:block bg-white rounded-xl border border-border shadow-sm overflow-hidden relative">
          <!-- Loading overlay -->
          <div v-if="isSearching" class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center rounded-xl">
            <div class="flex flex-col items-center gap-3">
              <div class="w-12 h-12 rounded-full border-3 border-[#245C4A]/20 border-t-[#245C4A] animate-spin"></div>
              <p class="text-sm font-medium text-[#245C4A]">Searching...</p>
            </div>
          </div>

          <div class="px-5 py-4 border-b border-border flex items-start justify-between">
            <div>
              <h2 class="text-sm font-semibold" style="color:#245c4a">Product Catalog</h2>
              <p class="text-xs text-muted-foreground mt-0.5">
                View and manage your product master records.
              </p>
            </div>
            <div class="text-xs font-semibold px-2 py-1 rounded" style="background:#f5ead4;color:#7a5800">
              {{ products.length }} shown
            </div>  
          </div>

          <div class="w-full overflow-x-auto">
            <table class="min-w-[800px] w-full border-collapse">
              <thead>
                <tr style="background:hsl(0 0% 96.1%)">
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Product
                  </th>
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Category
                  </th>
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Barcode
                  </th>
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Reviews
                  </th>
                  <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Status
                  </th>
                  <th class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                    Actions
                  </th>
                 </tr>
              </thead>

              <tbody>
                <tr
                  v-for="product in products"
                  :key="product.id"
                  class="border-b border-border last:border-0 transition-colors duration-200 hover:bg-[#f0f5f2] dark:hover:bg-[#1a2e42]"
                >
                  <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0"
                        style="background:hsl(0 0% 96.1%); border:1px solid hsl(0 0% 92.1%)"
                      >
                        <img
                          v-if="product.image_url"
                          :src="product.image_url"
                          class="w-full h-full object-cover"
                          alt=""
                        />
                        <span v-else class="text-sm">🖼️</span>
                      </div>

                      <div class="min-w-0">
                        <p class="text-sm font-semibold text-foreground truncate">
                          {{ product.product_name }}
                        </p>
                        <p class="text-xs text-muted-foreground mt-0.5 truncate">
                          {{ product.description || 'No description' }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <td class="px-5 py-4 text-sm text-slate-600">
                    {{ product.category_name || '—' }}
                  </td>

                  <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                    {{ product.barcode || '—' }}
                  </td>

                  <td class="px-5 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <div class="flex items-center gap-0.5">
                        <Star v-for="i in 5" :key="i" 
                          :class="i <= (product.average_rating || 0) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                          class="w-3 h-3" />
                      </div>
                      <Link
                        :href="`/vendor/products/${product.id}/reviews`"
                        class="text-xs text-emerald-600 hover:text-emerald-800"
                      >
                        {{ product.total_reviews || 0 }} reviews
                      </Link>
                    </div>
                  </td>

                  <td class="px-5 py-4 whitespace-nowrap">
                    <span
                      class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full"
                      :class="productStatusBadge(product.is_active).cls"
                    >
                      {{ productStatusBadge(product.is_active).label }}
                    </span>
                  </td>

                  <td class="px-5 py-4 whitespace-nowrap text-right">
                    <div class="inline-flex items-center gap-2">
                      <button
                        @click="openEditModal(product)"
                        class="text-xs font-semibold px-2 py-1 rounded transition-colors"
                        style="color:#245c4a"
                      >
                        Edit
                      </button>
                      <button
                        @click="confirmDeleteProduct(product)"
                        class="text-xs font-semibold px-2 py-1 rounded transition-colors text-rose-600 hover:bg-rose-50"
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
          <div
            v-if="paginationLinks.length"
            class="px-5 py-4 border-t border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
          >
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

        <!-- Mobile pagination -->
        <div
          v-if="paginationLinks.length"
          class="lg:hidden bg-white rounded-xl border border-border shadow-sm p-4"
        >
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

      <ConfirmationModal
        :open="showDeleteModal"
        title="Delete product permanently?"
        :description="deleteProductDescription"
        :details="deleteProductDetails"
        confirm-text="Delete product"
        variant="destructive"
        :loading="deleteProcessing"
        @update:open="showDeleteModal = $event"
        @confirm="deleteProduct"
        @cancel="cancelDeleteProduct"
      />

      <!-- MODAL -->
      <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center px-4 sm:px-6 pt-12 sm:pt-16 pb-6 overflow-y-auto">
          <!-- Backdrop -->
          <div class="absolute inset-0 bg-black/50" @click="closeModal" />

          <!-- Modal panel -->
          <div
            class="relative w-full max-w-[calc(100vw-2rem)] sm:max-w-lg mx-auto bg-card rounded-2xl shadow-2xl flex flex-col max-h-[90vh] border border-border"
          >
            <!-- Header -->
            <div class="px-5 py-4 border-b border-border flex items-start justify-between flex-shrink-0">
              <div>
                <h2 class="text-sm font-semibold" style="color:#245c4a">
                  {{ editingProduct ? 'Edit Product' : 'Add Product' }}
                </h2>
                <p class="text-xs text-muted-foreground mt-0.5">
                  {{ editingProduct
                    ? 'Update product catalog details.'
                    : 'Create a new product catalog entry.' }}
                </p>
              </div>

              <button
                type="button"
                @click="closeModal"
                class="ml-4 flex-shrink-0 w-7 h-7 rounded-xl flex items-center justify-center transition-colors hover:bg-accent text-foreground"
              >
                ✕
              </button>
            </div>

            <!-- Scrollable body -->
            <div class="overflow-y-auto flex-1 px-5 py-5">
              <form id="product-form" @submit.prevent="submit" class="flex flex-col gap-4">
                <!-- Thumbnail -->
                <div class="flex items-start gap-4 p-3 rounded-lg bg-muted">
                  <div
                    class="w-16 h-16 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0 bg-secondary border border-border"
                  >
                    <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" alt="" />
                    <span v-else class="text-xl">🖼️</span>
                  </div>

                  <div class="flex-1 min-w-0">
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                      Thumbnail <span class="font-normal normal-case">(optional)</span>
                    </label>
                    <input
                      type="file"
                      name="image"
                      accept="image/*"
                      :key="imageInputKey"
                      @change="onPickImage"
                      class="block w-full text-xs text-foreground file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border file:border-border file:text-xs file:font-semibold file:bg-secondary file:text-foreground hover:file:bg-accent"
                    />
                    <p class="text-xs text-muted-foreground mt-1">JPG/PNG recommended (max 2MB).</p>
                    <p v-if="form.errors.image" class="text-xs mt-1" style="color:#9f1239">
                      {{ form.errors.image }}
                    </p>
                  </div>
                </div>

                <!-- Product Name -->
                <div>
                  <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                    Product Name <span style="color:#9f1239">*</span>
                  </label>
                  <input
                    v-model="form.product_name"
                    required
                    placeholder="e.g. Chocolate Bar"
                    class="w-full px-3 py-2 rounded-xl border border-border bg-input text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 text-sm"
                    style="--tw-ring-color: rgba(36,92,74,.35);"
                  />
                  <p v-if="form.errors.product_name" class="text-xs mt-1" style="color:#9f1239">
                    {{ form.errors.product_name }}
                  </p>
                </div>

                <!-- Category + Barcode row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                      Category <span style="color:#9f1239">*</span>
                    </label>
                    <div class="relative">
                      <button
                        type="button"
                        ref="categoryBtnEl"
                        @click="openCategoryDropdown"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl border bg-input text-foreground text-sm focus:outline-none focus:ring-2 transition-colors"
                        :class="form.errors.category_id ? 'border-red-300' : 'border-border'"
                        style="--tw-ring-color: rgba(36,92,74,.35);"
                      >
                        <span :class="selectedCategoryLabel ? 'text-foreground' : 'text-muted-foreground'">
                          {{ selectedCategoryLabel ?? 'Select category' }}
                        </span>
                        <svg
                          class="w-4 h-4 text-muted-foreground transition-transform flex-shrink-0"
                          :class="categoryOpen ? 'rotate-180' : ''"
                          fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                      </button>

                      <!-- Dropdown list -->
                      <Teleport to="body">
                        <div
                          v-if="categoryOpen"
                          class="fixed z-[9999] bg-card rounded-lg border border-border shadow-lg py-1 overflow-y-auto"
                          :style="{ ...categoryDropdownStyle, maxHeight: '220px' }"
                        >
                          <div v-for="category in flattenedCategories" :key="category.id">
                            <button
                              @click="selectCategory(category.id)"
                              class="w-full text-left px-3 py-2 text-sm transition-colors hover:bg-accent text-foreground"
                              :style="{ paddingLeft: (category.level || 0) * 20 + 12 + 'px' }"
                            >
                              <span class="flex items-center gap-2">
                                <svg
                                  v-if="form.category_id === category.id"
                                  class="w-3.5 h-3.5 flex-shrink-0"
                                  fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"
                                  style="color:#245c4a"
                                >
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span v-else class="w-3.5 flex-shrink-0" />
                                <span :class="{ 'font-semibold': (category.level || 0) === 0 }">
                                  {{ category.category_name }}
                                </span>
                              </span>
                            </button>
                          </div>
                          <p v-if="flattenedCategories.length === 0" class="px-3 py-2 text-xs text-muted-foreground">
                            No categories available.
                          </p>
                        </div>
                      </Teleport>

                      <!-- Click-outside overlay -->
                      <div
                        v-if="categoryOpen"
                        class="fixed inset-0 z-[9998]"
                        @click="categoryOpen = false"
                      />
                    </div>
                    <p v-if="form.errors.category_id" class="text-xs mt-1" style="color:#9f1239">
                      {{ form.errors.category_id }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                      Barcode
                    </label>
                    <input
                      v-model="form.barcode"
                      placeholder="e.g. 4901234567890"
                      class="w-full px-3 py-2 rounded-xl border border-border bg-input text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 text-sm"
                      style="--tw-ring-color: rgba(36,92,74,.35);"
                    />
                    <p v-if="form.errors.barcode" class="text-xs mt-1" style="color:#9f1239">
                      {{ form.errors.barcode }}
                    </p>
                  </div>
                </div>

                <!-- Price + Stock row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                      Price <span style="color:#9f1239">*</span>
                    </label>
                    <div class="relative">
                      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">₱</span>
                      <input
                        v-model="form.price"
                        type="number"
                        min="0"
                        step="0.01"
                        required
                        placeholder="0.00"
                        class="w-full pl-7 pr-3 py-2 rounded-xl border border-border bg-input text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 text-sm"
                        style="--tw-ring-color: rgba(36,92,74,.35);"
                      />
                    </div>
                    <p v-if="form.errors.price" class="text-xs mt-1" style="color:#9f1239">
                      {{ form.errors.price }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                      Stock <span style="color:#9f1239">*</span>
                    </label>
                    <input
                      v-model="form.stock"
                      type="number"
                      min="0"
                      step="1"
                      required
                      placeholder="0"
                      class="w-full px-3 py-2 rounded-xl border border-border bg-input text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 text-sm"
                      style="--tw-ring-color: rgba(36,92,74,.35);"
                    />
                    <p v-if="form.errors.stock" class="text-xs mt-1" style="color:#9f1239">
                      {{ form.errors.stock }}
                    </p>
                  </div>
                </div>

                <!-- Description -->
                <div>
                  <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                    Description
                  </label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Brief product description (optional)"
                    class="w-full px-3 py-2 rounded-xl border border-border bg-input text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 text-sm resize-none"
                    style="--tw-ring-color: rgba(36,92,74,.35);"
                  />
                  <p v-if="form.errors.description" class="text-xs mt-1" style="color:#9f1239">
                    {{ form.errors.description }}
                  </p>
                </div>

                <!-- Active toggle -->
                <div
                  class="flex items-center justify-between px-3 py-2.5 rounded-lg border border-border bg-muted"
                >
                  <div>
                    <p class="text-sm font-medium text-foreground">Active product</p>
                    <p class="text-xs text-muted-foreground mt-0.5">
                      Active products appear in store inventory.
                    </p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0">
                    <input v-model="form.is_active" type="checkbox" class="sr-only peer" />
                    <div
                      class="w-10 h-5 rounded-full transition-colors peer-checked:bg-emerald-600 bg-gray-200 peer-focus:ring-2 peer-focus:ring-emerald-300"
                    ></div>
                    <div
                      class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"
                    ></div>
                  </label>
                </div>
              </form>
            </div>

            <!-- Footer actions -->
            <div class="px-5 py-4 border-t border-border flex items-center justify-end gap-2 flex-shrink-0 bg-card rounded-b-xl">
              <button
                type="button"
                @click="closeModal"
                class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl border border-border bg-secondary text-foreground hover:bg-accent transition-colors"
              >
                Cancel
              </button>

              <button
                type="submit"
                form="product-form"
                :disabled="form.processing"
                class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                style="background:#245c4a"
              >
                <svg v-if="form.processing" class="animate-spin -ml-0.5 mr-1.5 w-3 h-3 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                {{ editingProduct ? 'Update' : 'Create' }}
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </VendorLayout>
</template>

<style scoped>
@media (max-width: 1024px) {
  .overflow-x-auto {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}
</style>