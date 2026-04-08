<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import { useSidebar } from '@/composables/useSidebar';
import { ConfirmationModal } from '@/components/ui/modal';

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
  'dashboard-content': true,
  'sidebar-collapsed': isCollapsed.value,
}));

interface InventoryItem {
  id: number;
  product_name: string;
  category: string;
  barcode: string;
  stock_level: number;
  unit_price: number;
  reorder_level: number;
  is_available: boolean;
}

interface Stats {
  total: number;
  outOfStock: number;
  lowStock: number;
  totalValue: number;
}

const props = defineProps<{
  inventoryItems: InventoryItem[];
  stats: Stats;
}>();

const searchQuery = ref('');
const selectedCategory = ref('All');
const selectedStatus = ref('All');
const sortKey = ref<keyof InventoryItem>('product_name');
const sortAsc = ref(true);
const showEditModal = ref(false);
const editTarget = ref<InventoryItem | null>(null);
const showDeleteModal = ref(false);
const pendingDeleteItem = ref<InventoryItem | null>(null);
const deleteProcessing = ref(false);

const editForm = useForm({
  stock_level: 0,
  unit_price: 0,
  reorder_level: 0,
  is_available: true,
});

const categories = computed(() => ['All', ...new Set(props.inventoryItems.map((i) => i.category))]);

const filtered = computed(() => {
  let items = props.inventoryItems;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    items = items.filter(
      (i) =>
        i.product_name.toLowerCase().includes(q) ||
        i.barcode.toLowerCase().includes(q) ||
        i.category.toLowerCase().includes(q)
    );
  }
  if (selectedCategory.value !== 'All') items = items.filter((i) => i.category === selectedCategory.value);
  if (selectedStatus.value === 'Low Stock') items = items.filter((i) => i.stock_level > 0 && i.stock_level <= i.reorder_level);
  else if (selectedStatus.value === 'Out of Stock') items = items.filter((i) => i.stock_level === 0);
  else if (selectedStatus.value === 'Available') items = items.filter((i) => i.is_available);
  else if (selectedStatus.value === 'Hidden') items = items.filter((i) => !i.is_available);

  return [...items].sort((a, b) => {
    const av = a[sortKey.value];
    const bv = b[sortKey.value];
    if (av === bv) return 0;
    return (sortAsc.value ? 1 : -1) * (av < bv ? -1 : 1);
  });
});

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(10);
const itemsPerPageOptions = [5, 10, 20, 50];

// Paginated data
const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filtered.value.slice(start, end);
});

// Total pages
const totalPages = computed(() => Math.ceil(filtered.value.length / itemsPerPage.value));

// Reset to first page when filters change
watch([searchQuery, selectedCategory, selectedStatus, sortKey, sortAsc, itemsPerPage], () => {
  currentPage.value = 1;
});

function setSort(key: keyof InventoryItem) {
  if (sortKey.value === key) sortAsc.value = !sortAsc.value;
  else { sortKey.value = key; sortAsc.value = true; }
}

function stockStatus(item: InventoryItem): 'out' | 'low' | 'ok' {
  if (item.stock_level === 0) return 'out';
  if (item.stock_level <= item.reorder_level) return 'low';
  return 'ok';
}

function openEdit(item: InventoryItem) {
  editTarget.value = item;
  editForm.stock_level   = item.stock_level;
  editForm.unit_price    = item.unit_price;
  editForm.reorder_level = item.reorder_level;
  editForm.is_available  = item.is_available;
  showEditModal.value = true;
}

function saveEdit() {
  if (!editTarget.value) return;
  editForm.put(`/vendor/inventory/${editTarget.value.id}`, {
    onSuccess: () => { showEditModal.value = false; },
  });
}

function toggleAvailability(item: InventoryItem) {
  router.patch(`/vendor/inventory/${item.id}/toggle`);
}

function confirmDeleteRow(item: InventoryItem) {
  pendingDeleteItem.value = item;
  showDeleteModal.value = true;
}

function deleteRow() {
  if (!pendingDeleteItem.value) {
    return;
  }

  deleteProcessing.value = true;

  router.delete(`/vendor/inventory/${pendingDeleteItem.value.id}`, {
    preserveState: true,
    onFinish: () => {
      deleteProcessing.value = false;
      showDeleteModal.value = false;
      pendingDeleteItem.value = null;
    },
  });
}

function cancelDeleteRow() {
  showDeleteModal.value = false;
  pendingDeleteItem.value = null;
}

function formatPrice(v: number | null | undefined) {
  return '₱' + (v ?? 0).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

const deleteInventoryDescription = computed(() => {
  return 'This action is permanent, tracked to your account, and cannot be undone.';
});

const deleteInventoryDetails = computed(() => {
  if (!pendingDeleteItem.value) {
    return 'It will remove the selected inventory item from your catalog and sales listings.';
  }

  const item = pendingDeleteItem.value;
  const totalValue = item.stock_level * item.unit_price;

  return `Delete "${item.product_name}" permanently. Current stock: ${item.stock_level}. Unit price: ${formatPrice(item.unit_price)}. Estimated inventory value: ${formatPrice(totalValue)}.`;
});

function margin(item: InventoryItem) {
  if (item.unit_price <= 0) return 0;
  // margin shown as % of price (no cost_price in schema, show placeholder)
  return 0;
}

function stockBarWidth(item: InventoryItem) {
  return Math.min(100, (item.stock_level / Math.max(item.reorder_level * 3, 1)) * 100) + '%';
}

// Pagination methods
function goToPage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
}

function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
}

function getPageNumbers(): (number | string)[] {
  const delta = 2;
  const range: number[] = [];
  const rangeWithDots: (number | string)[] = [];
  let l: number | undefined;

  for (let i = 1; i <= totalPages.value; i++) {
    if (i === 1 || i === totalPages.value || (i >= currentPage.value - delta && i <= currentPage.value + delta)) {
      range.push(i);
    }
  }

  range.forEach((i) => {
    if (l !== undefined) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push('...');
      }
    }
    rangeWithDots.push(i);
    l = i;
  });

  return rangeWithDots;
}
</script>

<template>
  <Head title="Inventory Management" />
  <div class="dashboard-wrapper">
    <Header />
    <Sidebar role="vendor">
      <VendorNav />
    </Sidebar>

    <main :class="contentClass">
      <div class="inv-page">
        <div class="inv-header">
          <div>
            <h1 class="inv-title">Inventory</h1>
            <p class="inv-sub">Monitor stock levels, pricing, reorder points, and store availability</p>
          </div>
        </div>

        <div class="stat-grid">
          <div class="stat-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
            <div class="stat-icon bg-blue-50 text-blue-500 dark:bg-amber-100/15 dark:text-amber-200">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </div>
            <div><p class="stat-label">Tracked Inventory Items</p><p class="stat-value">{{ stats.total }}</p></div>
          </div>
          <div class="stat-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
            <div class="stat-icon bg-red-50 text-red-500 dark:bg-amber-100/15 dark:text-amber-200">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div><p class="stat-label">Out of Stock</p><p class="stat-value stat-value--red">{{ stats.outOfStock }}</p></div>
          </div>
          <div class="stat-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
            <div class="stat-icon bg-amber-50 text-amber-500 dark:bg-amber-100/15 dark:text-amber-200">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div><p class="stat-label">Low Stock</p><p class="stat-value stat-value--amber">{{ stats.lowStock }}</p></div>
          </div>
          <div class="stat-card bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl">
            <div class="stat-icon bg-emerald-50 text-emerald-500 dark:bg-amber-100/15 dark:text-amber-200">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div><p class="stat-label">Inventory Value</p><p class="stat-value">{{ formatPrice(stats.totalValue) }}</p></div>
          </div>
        </div>

        <div class="filter-bar">
          <div class="search-wrap">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="searchQuery" type="text" placeholder="Search by product name, category, or barcode…" class="search-input" />
          </div>
          <div class="filter-row">
            <select v-model="selectedCategory" class="filter-select">
              <option v-for="cat in categories" :key="cat">{{ cat }}</option>
            </select>
            <select v-model="selectedStatus" class="filter-select">
              <option>All</option>
              <option>Available</option>
              <option>Hidden</option>
              <option>Low Stock</option>
              <option>Out of Stock</option>
            </select>
            <span class="result-count">{{ filtered.length }} item{{ filtered.length !== 1 ? 's' : '' }}</span>
          </div>
        </div>

        <div class="table-card desktop-only">
          <div class="table-responsive">
          <table class="inv-table">
            <thead>
              <tr>
                <th @click="setSort('product_name')">Product <span class="sort-arrow" :class="{active:sortKey==='product_name'}">{{ sortKey==='product_name'?(sortAsc?'↑':'↓'):'↕' }}</span></th>
                <th @click="setSort('category')">Category <span class="sort-arrow" :class="{active:sortKey==='category'}">{{ sortKey==='category'?(sortAsc?'↑':'↓'):'↕' }}</span></th>
                <th @click="setSort('stock_level')">Stock <span class="sort-arrow" :class="{active:sortKey==='stock_level'}">{{ sortKey==='stock_level'?(sortAsc?'↑':'↓'):'↕' }}</span></th>
                <th @click="setSort('unit_price')">Unit Price <span class="sort-arrow" :class="{active:sortKey==='unit_price'}">{{ sortKey==='unit_price'?(sortAsc?'↑':'↓'):'↕' }}</span></th>
                <th>Margin</th>
                <th>Availability</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filtered.length === 0">
                <td colspan="7" class="empty-state">
                  <div class="empty-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    <p>No inventory items match your filters</p>
                  </div>
                </td>
              </tr>
              <tr v-for="item in paginatedItems" :key="item.id" :class="{'row-unavailable': !item.is_available}">
                <td>
                  <div class="product-cell">
                    <div class="product-avatar">{{ item.product_name.charAt(0) }}</div>
                    <div>
                      <p class="product-name">{{ item.product_name }}</p>
                      <p class="product-barcode">{{ item.barcode }}</p>
                    </div>
                  </div>
                </td>
                <td>
                  <span
                    class="category-badge bg-[#f0f9f6] text-[#1B4D3E] dark:bg-amber-100/15 dark:text-amber-200 dark:border dark:border-amber-200/20"
                  >
                    {{ item.category }}
                  </span>
                </td>
                <td>
                  <div class="stock-cell">
                    <span class="stock-number" :class="{'stock--out':stockStatus(item)==='out','stock--low':stockStatus(item)==='low','stock--ok':stockStatus(item)==='ok'}">{{ item.stock_level }}</span>
                    <div class="stock-bar-wrap"><div class="stock-bar" :class="{'bar--out':stockStatus(item)==='out','bar--low':stockStatus(item)==='low','bar--ok':stockStatus(item)==='ok'}" :style="{width:stockBarWidth(item)}"></div></div>
                    <span class="reorder-hint">/ {{ item.reorder_level }}</span>
                  </div>
                </td>
                <td>
                  <p class="price-main">{{ formatPrice(item.unit_price) }}</p>
                </td>
                <td>
                  <span
                    class="margin-badge"
                    :class="[
                      margin(item) >= 30 ? 'margin--good dark:bg-emerald-500/15 dark:text-emerald-300' : 'margin--low dark:bg-amber-500/15 dark:text-amber-200',
                    ]"
                  >
                    {{ margin(item) }}%
                  </span>
                </td>
                <td>
                  <button class="toggle-btn" :class="item.is_available?'toggle-btn--on':'toggle-btn--off'" @click="toggleAvailability(item)">
                    <span class="toggle-dot"></span>{{ item.is_available ? 'Active' : 'Hidden' }}
                  </button>
                </td>
                <td>
                  <div class="action-row">
                    <button class="action-btn" title="Edit Inventory" @click="openEdit(item)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="action-btn action-btn--danger" title="Delete Inventory" @click="confirmDeleteRow(item)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination Controls -->
          <div v-if="filtered.length > 0" class="pagination-container">
            <div class="pagination-info">
              Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filtered.length) }} of {{ filtered.length }} items
            </div>
  
          <div class="pagination-controls">
            <div class="items-per-page">
              <span>Show:</span>
                <select v-model="itemsPerPage" class="items-per-page-select">
                  <option v-for="option in itemsPerPageOptions" :key="option" :value="option">
                  {{ option }}
                  </option>
                </select>
              </div>
    
            <div class="pagination-buttons">
              <button 
                @click="prevPage" 
                :disabled="currentPage === 1"
                class="pagination-btn"
                :class="{ 'pagination-btn-disabled': currentPage === 1 }"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
              </button>
      
              <button
                v-for="pageNum in getPageNumbers()"
                :key="pageNum"
                @click="typeof pageNum === 'number' ? goToPage(pageNum) : null"
                class="pagination-page"
                :class="{
                  'pagination-page-active': currentPage === pageNum,
                  'pagination-dots': pageNum === '...'
                }"
                :disabled="pageNum === '...'"
              >
                {{ pageNum }}
              </button>
      
              <button 
                @click="nextPage" 
                :disabled="currentPage === totalPages"
                class="pagination-btn"
                :class="{ 'pagination-btn-disabled': currentPage === totalPages }"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>
          </div>
          </div>
          </div>
        </div>

        <ConfirmationModal
          :open="showDeleteModal"
          title="Delete inventory item permanently?"
          :description="deleteInventoryDescription"
          :details="deleteInventoryDetails"
          confirm-text="Delete from catalog"
          variant="destructive"
          :loading="deleteProcessing"
          @update:open="showDeleteModal = $event"
          @confirm="deleteRow"
          @cancel="cancelDeleteRow"
        />

        <div class="mobile-list mobile-only">
          <div v-if="filtered.length === 0" class="empty-state-mobile">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            <p>No inventory items match your filters</p>
          </div>

          <div v-for="item in paginatedItems" :key="item.id" class="mc" :class="{'mc--unavailable': !item.is_available}">
            <div class="mc-head">
              <div class="product-cell">
                <div class="product-avatar">{{ item.product_name.charAt(0) }}</div>
                <div>
                  <p class="product-name">{{ item.product_name }}</p>
                  <p class="product-barcode">{{ item.barcode }}</p>
                </div>
              </div>
              <span class="margin-badge" :class="margin(item)>=30?'margin--good':'margin--low'">{{ margin(item) }}%</span>
            </div>

            <div class="mc-tags">
              <span
                class="category-badge bg-[#f0f9f6] text-[#1B4D3E] dark:bg-amber-100/15 dark:text-amber-200 dark:border dark:border-amber-200/20"
              >
                {{ item.category }}
              </span>
              <span class="status-pill" :class="{'status-pill--on':item.is_available,'status-pill--off':!item.is_available}">
                {{ item.is_available ? 'Active' : 'Hidden' }}
              </span>
              <span class="stock-pill" :class="{'stock--out':stockStatus(item)==='out','stock--low':stockStatus(item)==='low','stock--ok':stockStatus(item)==='ok'}">
                {{ stockStatus(item) === 'out' ? 'Out of Stock' : stockStatus(item) === 'low' ? 'Low Stock' : 'In Stock' }}
              </span>
            </div>

            <div class="mc-stock-row">
              <div class="mc-stock-labels">
                <span class="mc-label">Stock Level</span>
                <span class="stock-number" :class="{'stock--out':stockStatus(item)==='out','stock--low':stockStatus(item)==='low','stock--ok':stockStatus(item)==='ok'}">
                  {{ item.stock_level }} <span class="reorder-hint">/ {{ item.reorder_level }} min</span>
                </span>
              </div>
              <div class="stock-bar-wrap stock-bar-wrap--full">
                <div class="stock-bar" :class="{'bar--out':stockStatus(item)==='out','bar--low':stockStatus(item)==='low','bar--ok':stockStatus(item)==='ok'}" :style="{width:stockBarWidth(item)}"></div>
              </div>
            </div>

            <div class="mc-prices">
              <div class="mc-price-item">
                <span class="mc-label">Unit Price</span>
                <span class="mc-price-val">{{ formatPrice(item.unit_price) }}</span>
              </div>
            </div>

            <div class="mc-footer">
              <button class="toggle-btn" :class="item.is_available?'toggle-btn--on':'toggle-btn--off'" @click="toggleAvailability(item)">
                <span class="toggle-dot"></span>{{ item.is_available ? 'Active' : 'Hidden' }}
              </button>
              <div class="action-row">
                <button class="action-btn" @click="openEdit(item)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="action-btn action-btn--danger" @click="confirmDeleteRow(item)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </div>
            </div>
          </div>
          <!-- Mobile Pagination -->
<div v-if="filtered.length > 0" class="mobile-pagination">
  <button 
    @click="prevPage" 
    :disabled="currentPage === 1"
    class="mobile-pagination-btn"
  >
    Previous
  </button>
  <span class="mobile-page-info">Page {{ currentPage }} of {{ totalPages }}</span>
  <button 
    @click="nextPage" 
    :disabled="currentPage === totalPages"
    class="mobile-pagination-btn"
  >
    Next
  </button>
</div>
        </div>
      </div>
    </main>
  </div>

  <Teleport to="body">
    <div v-if="showEditModal" class="modal-backdrop" @click.self="showEditModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">Edit Inventory</h2>
          <button class="modal-close" @click="showEditModal = false">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <p class="modal-product-name">{{ editTarget?.product_name }}</p>
        <div class="modal-grid">
          <label class="field-label">Stock Level<input type="number" v-model.number="editForm.stock_level" class="field-input" min="0" /></label>
          <label class="field-label">Reorder Level<input type="number" v-model.number="editForm.reorder_level" class="field-input" min="0" /></label>
          <label class="field-label">Unit Price (₱)<input type="number" v-model.number="editForm.unit_price" class="field-input" min="0" step="0.01" /></label>
        </div>
        <label class="field-label availability-toggle">
          <span>Available for sale</span>
          <input type="checkbox" v-model="editForm.is_available" class="checkbox-input" />
        </label>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showEditModal = false">Cancel</button>
          <button class="btn-primary" :disabled="editForm.processing" @click="saveEdit">
            {{ editForm.processing ? 'Saving…' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.inv-page {
  padding: 28px 32px;
  max-width: 1400px;
  font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}

.inv-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 24px; }
.inv-title  { font-size: 1.6rem; font-weight: 600; color: #245c4a; margin: 0 0 4px; line-height: 1.2; }
.inv-sub    { font-size: 0.875rem; color: #737373; margin: 0; }

.btn-primary {
  display: inline-flex; align-items: center; gap: 7px; flex-shrink: 0;
  background: #1B4D3E; color: #fff; border: none;
  padding: 9px 18px; border-radius: 8px;
  font-size: 0.875rem; font-weight: 500; cursor: pointer;
  transition: background 0.18s; font-family: inherit;
}
.btn-primary:hover { background: #245c4a; }

.btn-ghost {
  background: transparent; border: 1px solid #e5e5e5; color: #737373;
  padding: 9px 18px; border-radius: 8px;
  font-size: 0.875rem; font-weight: 500; cursor: pointer;
  transition: border-color 0.18s, color 0.18s; font-family: inherit;
}
.btn-ghost:hover { border-color: #c5a059; color: #1B4D3E; }

.stat-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 20px; }
.stat-card { border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
.stat-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.stat-label { font-size:0.75rem; color:#737373; margin:0 0 2px; font-weight:500; }
.stat-value { font-size:1.3rem; font-weight:700; color:#171717; margin:0; }
.stat-value--red   { color:#ef4444; }
.stat-value--amber { color:#d97706; }

.filter-bar  { display:flex; flex-direction:column; gap:10px; margin-bottom:16px; }
.search-wrap { position:relative; }
.search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#737373; pointer-events:none; }
.search-input {
  width:100%; padding:9px 12px 9px 36px;
  border:1px solid #e5e5e5; border-radius:8px;
  font-size:0.875rem; color:#171717; background:#fff;
  outline:none; transition:border-color 0.18s; font-family:inherit; box-sizing:border-box;
}
.search-input:focus { border-color:#245c4a; }
.search-input::placeholder { color:#a3a3a3; }
.filter-row  { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.filter-select {
  flex:1; min-width:110px; padding:9px 12px;
  border:1px solid #e5e5e5; border-radius:8px;
  font-size:0.875rem; color:#171717; background:#fff;
  outline:none; cursor:pointer; font-family:inherit; transition:border-color 0.18s;
}
.filter-select:focus { border-color:#245c4a; }
.result-count { font-size:0.8rem; color:#737373; white-space:nowrap; margin-left:auto; }

.desktop-only { display:block; }
.mobile-only  { display:none; }

.inv-page { container-type: inline-size; container-name: inv; }

.table-card { border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.06); }
.inv-table  { width:100%; border-collapse:collapse; }
.inv-table thead tr { background:#f8f8f8; border-bottom:1px solid #EDEDED; }
.inv-table th { padding:12px 16px; font-size:0.78rem; font-weight:600; color:#737373; text-align:left; white-space:nowrap; cursor:pointer; user-select:none; transition:color 0.15s; }
.inv-table th:hover { color:#245c4a; }
.inv-table th:last-child { cursor:default; }
.sort-arrow { margin-left:4px; font-size:0.7rem; opacity:0.4; }
.sort-arrow.active { opacity:1; color:#245c4a; }
.inv-table tbody tr { border-bottom:1px solid #f0f0f0; transition:background 0.12s; }
.inv-table tbody tr:last-child { border-bottom:none; }
.inv-table tbody tr:hover { background:#fafafa; }
.inv-table tbody tr.row-unavailable { opacity:0.55; }
.inv-table td { padding:14px 16px; font-size:0.875rem; color:#171717; vertical-align:middle; }

.product-cell { display:flex; align-items:center; gap:10px; }
.product-avatar { width:36px; height:36px; border-radius:8px; flex-shrink:0; background:linear-gradient(135deg,#245c4a,#1B4D3E); color:#C5A059; font-weight:700; font-size:0.9rem; display:flex; align-items:center; justify-content:center; }
.product-name    { font-weight:500; margin:0 0 2px; color:#171717; font-size:0.875rem; }
.product-barcode { font-size:0.72rem; color:#a3a3a3; margin:0; font-family:monospace; }

.category-badge { background:#f0f9f6; color:#1B4D3E; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:500; border:1px solid rgba(27,77,62,0.12); white-space:nowrap; }

.stock-cell { display:flex; align-items:center; gap:8px; }
.stock-number { font-weight:700; font-size:0.9rem; min-width:20px; }
.stock--out { color:#ef4444; }
.stock--low { color:#d97706; }
.stock--ok  { color:#059669; }
.stock-bar-wrap { width:56px; height:5px; background:#f0f0f0; border-radius:3px; overflow:hidden; }
.stock-bar-wrap--full { width:100%; height:6px; }
.stock-bar { height:100%; border-radius:3px; transition:width 0.3s; }
.bar--out { background:#ef4444; }
.bar--low { background:#f59e0b; }
.bar--ok  { background:#10b981; }
.reorder-hint { font-size:0.7rem; color:#a3a3a3; white-space:nowrap; }

.price-main { margin:0 0 2px; font-weight:600; }
.price-cost { margin:0; font-size:0.75rem; color:#a3a3a3; }

.margin-badge { padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; flex-shrink:0; }
.margin--good { background:#ecfdf5; color:#059669; }
.margin--low  { background:#fef9ec; color:#b45309; }

.toggle-btn { display:inline-flex; align-items:center; gap:6px; padding:4px 10px 4px 6px; border-radius:20px; border:none; font-size:0.75rem; font-weight:600; cursor:pointer; transition:opacity 0.15s; font-family:inherit; }
.toggle-btn:hover { opacity:0.8; }
.toggle-btn--on  { background:#10b981; color:white; }
.toggle-btn--off { background:#6b7280; color:white; }
.toggle-dot { width:10px; height:10px; border-radius:50%; background:currentColor; flex-shrink:0; }

/* Dark mode overrides for toggle buttons */
.dark .toggle-btn--on  { background:#10b981 !important; color:white !important; }
.dark .toggle-btn--off { background:#6b7280 !important; color:white !important; }

/* ─── Dark mode — all surfaces, text, borders ─── */
/* Page shell */
.dark .inv-page { background: var(--background); }

/* Header */
.dark .inv-title { color: var(--foreground); }
.dark .inv-sub   { color: var(--muted-foreground); }

/* Buttons */
.dark .btn-ghost { border-color: var(--border); color: var(--muted-foreground); background: transparent; }
.dark .btn-ghost:hover { border-color: var(--brand-gold); color: var(--foreground); }

/* Stat cards */
.dark .stat-card  { background: var(--card); border: 1px solid var(--border); box-shadow: none; }
.dark .stat-label { color: var(--muted-foreground); }
.dark .stat-value { color: var(--foreground); }
.dark .stat-value--red   { color: #f87171; }
.dark .stat-value--amber { color: #fbbf24; }

/* Filter bar */
.dark .search-input  { background: var(--input); border-color: var(--border); color: var(--foreground); }
.dark .search-input::placeholder { color: var(--muted-foreground); }
.dark .search-input:focus { border-color: var(--ring); }
.dark .search-icon   { color: var(--muted-foreground); }
.dark .filter-select { background: var(--input); border-color: var(--border); color: var(--foreground); }
.dark .filter-select:focus { border-color: var(--ring); }
.dark .result-count  { color: var(--muted-foreground); }

/* Desktop table */
.dark .table-card { background: var(--card); box-shadow: none; }
.dark .inv-table thead tr { background: var(--secondary); border-bottom-color: var(--border); }
.dark .inv-table th { color: var(--muted-foreground); }
.dark .inv-table th:hover { color: var(--foreground); }
.dark .sort-arrow.active { color: var(--brand-gold); }
.dark .inv-table tbody tr { border-bottom-color: var(--border); }
.dark .inv-table tbody tr:hover { background: var(--accent); }
.dark .inv-table td { color: var(--foreground); }

/* Product cell */
.dark .product-name    { color: var(--foreground); }
.dark .product-barcode { color: var(--muted-foreground); }

/* Category badge */
.dark .category-badge { background: rgba(27,77,62,0.25); color: #6ee7b7; border-color: rgba(27,77,62,0.4); }

/* Stock bar track */
.dark .stock-bar-wrap { background: var(--secondary); }
.dark .reorder-hint   { color: var(--muted-foreground); }

/* Price */
.dark .price-cost { color: var(--muted-foreground); }

/* Margin badges */
.dark .margin--good { background: rgba(6,95,70,0.3);   color: #6ee7b7; }
.dark .margin--low  { background: rgba(120,53,15,0.3);  color: #fde68a; }

/* Status pills */
.dark .status-pill--on  { background: rgba(6,95,70,0.35);  color: #6ee7b7; }
.dark .status-pill--off { background: var(--secondary);     color: var(--muted-foreground); }

/* Stock pills */
.dark .stock-pill.stock--out { background: rgba(127,29,29,0.35); color: #fca5a5; }
.dark .stock-pill.stock--low { background: rgba(120,53,15,0.35); color: #fde68a; }
.dark .stock-pill.stock--ok  { background: rgba(6,95,70,0.35);   color: #6ee7b7; }

/* Mobile cards */
.dark .mc { background: var(--card); border-color: var(--border); box-shadow: none; }
.dark .mc-prices { background: var(--secondary); }
.dark .mc-price-val { color: var(--foreground); }
.dark .mc-label { color: var(--muted-foreground); }

/* Action buttons */
.dark .action-btn { background: var(--secondary); border-color: var(--border); color: var(--muted-foreground); }
.dark .action-btn:hover { border-color: var(--brand-green); color: var(--foreground); background: var(--accent); }
.dark .action-btn--danger:hover { border-color: #ef4444; color: #f87171; background: rgba(127,29,29,0.2); }

/* Modal */
.dark .modal { background: var(--card); }
.dark .modal-title { color: var(--foreground); }
.dark .modal-product-name { color: var(--muted-foreground); }
.dark .modal-close { color: var(--muted-foreground); }
.dark .modal-close:hover { color: #f87171; }
.dark .field-label { color: var(--foreground); }
.dark .field-input { background: var(--input); border-color: var(--border); color: var(--foreground); }
.dark .field-input:focus { border-color: var(--ring); }
.dark .availability-toggle { background: var(--secondary); color: var(--foreground); }

/* Empty states */
.dark .empty-inner { color: var(--muted-foreground); }
.dark .empty-state-mobile { color: var(--muted-foreground); }

.action-row { display:flex; gap:4px; }
.action-btn { width:30px; height:30px; border-radius:6px; border:1px solid #e5e5e5; background:#fff; color:#737373; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:border-color 0.15s,color 0.15s,background 0.15s; }
.action-btn:hover { border-color:#245c4a; color:#245c4a; background:#f0f9f6; }
.action-btn--danger:hover { border-color:#ef4444; color:#ef4444; background:#fef2f2; }

.empty-state { text-align:center; padding:60px 0 !important; }
.empty-inner { display:flex; flex-direction:column; align-items:center; gap:12px; color:#a3a3a3; }
.empty-inner p { margin:0; font-size:0.9rem; }

.mobile-list { display:flex; flex-direction:column; gap:12px; }

.empty-state-mobile { display:flex; flex-direction:column; align-items:center; gap:12px; color:#a3a3a3; padding:48px 0; text-align:center; }
.empty-state-mobile p { margin:0; font-size:0.9rem; }

.mc { background:#fff; border:1px solid #EDEDED; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,.05); }
.mc--unavailable { opacity:0.6; }

.mc-head { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:10px; }
.mc-tags { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:12px; }

.status-pill { padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.status-pill--on  { background:#ecfdf5; color:#059669; }
.status-pill--off { background:#f5f5f5; color:#a3a3a3; }

.stock-pill { padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.stock-pill.stock--out { background:#fef2f2; color:#ef4444; }
.stock-pill.stock--low { background:#fffbeb; color:#d97706; }
.stock-pill.stock--ok  { background:#ecfdf5; color:#059669; }

.mc-stock-row { margin-bottom:12px; }
.mc-stock-labels { display:flex; align-items:baseline; justify-content:space-between; margin-bottom:6px; }
.mc-label { font-size:0.7rem; font-weight:600; color:#a3a3a3; text-transform:uppercase; letter-spacing:0.04em; }

.mc-prices { display:grid; grid-template-columns:1fr 1fr; gap:10px; background:#f8f8f8; border-radius:8px; padding:10px 12px; margin-bottom:14px; }
.mc-price-item { display:flex; flex-direction:column; gap:2px; }
.mc-price-val { font-size:0.9rem; font-weight:600; color:#171717; }

.mc-footer { display:flex; align-items:center; justify-content:space-between; }

.modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:flex-end; justify-content:center; z-index:1000; backdrop-filter:blur(2px); padding:0 16px; }
.modal { background:#fff; border-radius:16px 16px 0 0; padding:24px 20px; width:100%; max-width:calc(100vw - 32px); box-shadow:0 -8px 40px rgba(0,0,0,0.18); animation:slideUp 0.25s ease; max-height:92vh; overflow-y:auto; margin:0 auto; }
@keyframes slideUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
.modal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:4px; }
.modal-title  { font-size:1.1rem; font-weight:700; color:#245c4a; margin:0; }
.modal-close  { background:none; border:none; color:#a3a3a3; cursor:pointer; padding:4px; border-radius:6px; transition:color 0.15s; display:flex; }
.modal-close:hover { color:#ef4444; }
.modal-product-name { font-size:0.85rem; color:#737373; margin:0 0 20px; }
.modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px; }
.field-label { display:flex; flex-direction:column; gap:6px; font-size:0.8rem; font-weight:600; color:#555; }
.field-input { padding:10px 12px; border:1px solid #e5e5e5; border-radius:8px; font-size:0.875rem; color:#171717; outline:none; font-family:inherit; transition:border-color 0.18s; }
.field-input:focus { border-color:#245c4a; }
.availability-toggle { flex-direction:row; align-items:center; justify-content:space-between; background:#f8f8f8; padding:12px 14px; border-radius:8px; margin-bottom:20px; font-size:0.85rem; }
.checkbox-input { width:18px; height:18px; accent-color:#245c4a; cursor:pointer; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; }

@media (min-width: 769px) {
  .filter-bar  { flex-direction:row; align-items:center; }
  .search-wrap { flex:1; }
  .filter-row  { flex-wrap:nowrap; }
  .modal-backdrop { align-items:center; padding:0; }
  .modal { border-radius:14px; max-width:480px; padding:28px; margin:0; }
}

@media (max-width: 1200px) {
  .stat-grid { grid-template-columns:repeat(2,1fr); }
}

@media (max-width: 768px) {
  .modal-grid { grid-template-columns:1fr; }
  .modal-footer { flex-direction:column-reverse; }
  .modal-footer .btn-primary,
  .modal-footer .btn-ghost { width:100%; justify-content:center; }
}

/* Mobile styles - up to 768px */
@media (max-width: 768px) {
  .inv-page { 
    padding: 16px; 
  }
  
  .inv-title { 
    font-size: 1.25rem; 
  }
  
  .inv-sub { 
    font-size: 0.75rem; 
  }
  
  .stat-grid { 
    grid-template-columns: repeat(2, 1fr); 
    gap: 10px; 
    margin-bottom: 16px; 
  }
  
  .stat-card { 
    padding: 12px; 
    gap: 10px; 
  }
  
  .stat-icon { 
    width: 36px; 
    height: 36px; 
  }
  
  .stat-value { 
    font-size: 1rem; 
  }
  
  .stat-label { 
    font-size: 0.65rem; 
  }
  
  .filter-bar { 
    flex-direction: column; 
    gap: 10px; 
  }
  
  .filter-row { 
    width: 100%;
    gap: 8px; 
  }
  
  .filter-select { 
    flex: 1; 
    min-width: 0; 
  }
  
  .result-count { 
    margin-left: 0; 
  }
  
  .desktop-only { 
    display: none; 
  }
  
  .mobile-only { 
    display: block; 
  }
  
  .pagination-controls {
    flex-direction: column;
  }
  
  .pagination-buttons {
    flex-wrap: wrap;
    justify-content: center;
  }
}

/* Tablet styles - 769px to 1023px */
@media (min-width: 769px) and (max-width: 1023px) {
  .inv-page {
    padding: 20px 24px;
  }
  
  .stat-grid {
    gap: 12px;
  }
  
  .filter-bar {
    flex-direction: row;
    align-items: center;
    gap: 12px;
  }
  
  .search-wrap {
    flex: 1;
  }
  
  .filter-row {
    flex-wrap: nowrap;
  }
  
  .desktop-only {
    display: block;
  }
  
  .mobile-only {
    display: none;
  }
}

/* Desktop styles - 1024px and up */
@media (min-width: 1024px) {
  .inv-page {
    padding: 28px 32px;
  }
  
  .filter-bar {
    flex-direction: row;
    align-items: center;
    gap: 16px;
  }
  
  .search-wrap {
    flex: 1;
  }
  
  .filter-row {
    flex-wrap: nowrap;
  }
  
  .desktop-only {
    display: block;
  }
  
  .mobile-only {
    display: none;
  }
}

/* Small mobile devices - up to 480px */
@media (max-width: 480px) {
  .stat-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }
  
  .stat-card {
    padding: 10px;
  }
  
  .stat-icon {
    width: 32px;
    height: 32px;
  }
  
  .stat-value {
    font-size: 0.9rem;
  }
  
  .mc-prices {
    grid-template-columns: 1fr;
  }
  
  .mc-footer {
    flex-direction: column;
    align-items: stretch;
  }
  
  .mc-footer .toggle-btn {
    width: 100%;
    justify-content: center;
  }
  
  .mc-footer .action-row {
    justify-content: center;
  }
  
  .mobile-pagination {
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .pagination-page {
    min-width: 1.5rem;
    height: 1.5rem;
    font-size: 0.65rem;
  }
}

/* Ensure table doesn't get cut off */
.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.inv-table {
  min-width: 800px;
}

/* Fix for filter selects on mobile */
@media (max-width: 640px) {
  .filter-select {
    font-size: 0.75rem;
    padding: 8px 10px;
  }
  
  .search-input {
    font-size: 0.813rem;
    padding: 8px 12px 8px 34px;
  }
  
  .search-icon {
    width: 14px;
    height: 14px;
    left: 10px;
  }
}

/* Pagination Styles */
.pagination-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e5e5;
  background: #fafafa;
}

.dark .pagination-container {
  border-top-color: #334155;
  background: #1e293b;
}

.pagination-info {
  font-size: 0.75rem;
  color: #737373;
  text-align: center;
}

.dark .pagination-info {
  color: #94a3b8;
}

.pagination-controls {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  align-items: center;
  justify-content: space-between;
}

@media (min-width: 640px) {
  .pagination-controls {
    flex-direction: row;
  }
}

.items-per-page {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #737373;
}

.dark .items-per-page {
  color: #94a3b8;
}

.items-per-page-select {
  padding: 0.375rem 0.5rem;
  border: 1px solid #e5e5e5;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  background: white;
  cursor: pointer;
  outline: none;
}

.dark .items-per-page-select {
  border-color: #475569;
  background: #0f172a;
  color: #f1f5f9;
}

.pagination-buttons {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  flex-wrap: wrap;
  justify-content: center;
}

.pagination-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border: 1px solid #e5e5e5;
  background: white;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.15s;
  color: #737373;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #245c4a;
  color: #245c4a;
  background: #f0f9f6;
}

.pagination-btn-disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.dark .pagination-btn {
  border-color: #475569;
  background: #1e293b;
  color: #94a3b8;
}

.dark .pagination-btn:hover:not(:disabled) {
  border-color: #fcd34d;
  color: #fcd34d;
  background: rgba(245, 158, 11, 0.1);
}

.pagination-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 2rem;
  height: 2rem;
  padding: 0 0.5rem;
  border: 1px solid #e5e5e5;
  background: white;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.15s;
  font-size: 0.75rem;
  font-weight: 500;
  color: #737373;
}

.pagination-page:hover:not(.pagination-dots) {
  border-color: #245c4a;
  color: #245c4a;
  background: #f0f9f6;
}

.pagination-page-active {
  background: #245c4a;
  border-color: #245c4a;
  color: white;
}

.pagination-page-active:hover {
  background: #1B4D3E;
  color: white;
}

.pagination-dots {
  border: none;
  background: transparent;
  cursor: default;
  pointer-events: none;
}

.dark .pagination-page {
  border-color: #475569;
  background: #1e293b;
  color: #94a3b8;
}

.dark .pagination-page:hover:not(.pagination-dots) {
  border-color: #fcd34d;
  color: #fcd34d;
  background: rgba(245, 158, 11, 0.1);
}

.dark .pagination-page-active {
  background: #fcd34d;
  border-color: #fcd34d;
  color: #0f172a;
}

.dark .pagination-page-active:hover {
  background: #fbbf24;
  color: #0f172a;
}

/* Mobile Pagination */
.mobile-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 1rem 0;
  margin-top: 0.5rem;
  border-top: 1px solid #e5e5e5;
}

.dark .mobile-pagination {
  border-top-color: #334155;
}

.mobile-pagination-btn {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #e5e5e5;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  color: #737373;
}

.mobile-pagination-btn:hover:not(:disabled) {
  border-color: #245c4a;
  color: #245c4a;
  background: #f0f9f6;
}

.mobile-pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.dark .mobile-pagination-btn {
  background: #1e293b;
  border-color: #475569;
  color: #94a3b8;
}

.dark .mobile-pagination-btn:hover:not(:disabled) {
  border-color: #fcd34d;
  color: #fcd34d;
  background: rgba(245, 158, 11, 0.1);
}

.mobile-page-info {
  font-size: 0.75rem;
  color: #737373;
}

.dark .mobile-page-info {
  color: #94a3b8;
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
</style>