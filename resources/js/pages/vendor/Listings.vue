<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import VendorLayout from '@/layouts/VendorLayout.vue';
import { ConfirmationModal } from '@/components/ui/modal';

type Listing = {
  id: number;
  name: string;
  description: string | null;
  slug: string;
  cover_image_path?: string | null;
  is_active: boolean;
  products_count: number;
  created_at: string;
};

type PaginationLink = {
  url: string | null;
  label: string;
  active: boolean;
};

const props = defineProps<{
  listings: {
    data: Listing[];
    links?: PaginationLink[];
  };
}>();

const showModal = ref(false);
const showDeleteModal = ref(false);
const editingListing = ref<Listing | null>(null);
const pendingDeleteListing = ref<Listing | null>(null);
const deleteProcessing = ref(false);

const form = useForm({
  name: '',
  description: '',
  is_active: true,
});

const listings = computed(() => props.listings.data || []);
const paginationLinks = computed(() => props.listings.links || []);

function openCreateModal() {
  editingListing.value = null;
  form.reset();
  form.clearErrors();
  form.is_active = true;
  showModal.value = true;
}

function openEditModal(listing: Listing) {
  editingListing.value = listing;
  form.clearErrors();
  form.name = listing.name;
  form.description = listing.description || '';
  form.is_active = listing.is_active;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingListing.value = null;
  form.reset();
  form.clearErrors();
}

function submit() {
  if (editingListing.value) {
    form.put(`/vendor/listings/${editingListing.value.id}`, {
      preserveState: true,
      onSuccess: () => closeModal(),
    });
  } else {
    form.post('/vendor/listings', {
      preserveState: true,
      onSuccess: () => closeModal(),
    });
  }
}

function confirmDelete(listing: Listing) {
  pendingDeleteListing.value = listing;
  showDeleteModal.value = true;
}

function deleteListing() {
  if (!pendingDeleteListing.value) return;

  deleteProcessing.value = true;

  form.delete(`/vendor/listings/${pendingDeleteListing.value.id}`, {
    preserveState: true,
    onFinish: () => {
      deleteProcessing.value = false;
      showDeleteModal.value = false;
      pendingDeleteListing.value = null;
    },
  });
}
</script>

<template>
  <Head title="Listings" />

  <VendorLayout>
    <div class="p-4 sm:p-6 flex flex-col gap-4">
      <div class="flex flex-col gap-3">
        <Link
          href="/vendor/products"
          class="inline-flex items-center gap-2 w-fit px-3 py-2 rounded-xl border border-border bg-white text-sm font-medium hover:bg-accent transition-colors"
          style="color:#245c4a"
        >
          <span>←</span>
          <span>Back to Products</span>
        </Link>

        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color:#245c4a">
              <span style="color:#C5A059">✦</span> Catalog
            </p>
            <h1 class="text-2xl font-semibold tracking-tight" style="color:#245c4a">Listings</h1>
            <p class="text-sm text-muted-foreground mt-1">
              Group related products under one or more storefront listing collections.
            </p>
          </div>

          <button
            @click="openCreateModal"
            class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl text-white transition-opacity hover:opacity-90"
            style="background:#245c4a"
          >
            + Add Listing
          </button>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-border shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-border flex items-start justify-between">
          <div>
            <h2 class="text-sm font-semibold" style="color:#245c4a">Listing Catalog</h2>
            <p class="text-xs text-muted-foreground mt-0.5">
              Create and manage vendor-defined product groups.
            </p>
          </div>

          <div class="text-xs font-semibold px-2 py-1 rounded" style="background:#f5ead4;color:#7a5800">
            {{ listings.length }} shown
          </div>
        </div>

        <div class="w-full overflow-x-auto">
          <table class="min-w-[760px] w-full border-collapse">
            <thead>
              <tr style="background:hsl(0 0% 96.1%)">
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                  Listing
                </th>
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">
                  Products
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
                v-for="listing in listings"
                :key="listing.id"
                class="border-b border-border last:border-0 hover:bg-[#f0f5f2]"
              >
                <td class="px-5 py-4">
                  <p class="text-sm font-semibold text-foreground">{{ listing.name }}</p>
                  <p class="text-xs text-muted-foreground mt-0.5">
                    {{ listing.description || 'No description' }}
                  </p>
                </td>

                <td class="px-5 py-4 text-sm text-slate-600">
                  {{ listing.products_count }}
                </td>

                <td class="px-5 py-4">
                  <span
                    class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="listing.is_active
                      ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                      : 'bg-slate-100 text-slate-600 border border-slate-200'"
                  >
                    {{ listing.is_active ? 'Active' : 'Archived' }}
                  </span>
                </td>

                <td class="px-5 py-4 text-right">
                  <div class="inline-flex items-center gap-2">
                    <button
                      @click="openEditModal(listing)"
                      class="text-xs font-semibold px-2 py-1 rounded transition-colors hover:bg-slate-100"
                      style="color:#245c4a"
                    >
                      Edit
                    </button>

                    <button
                      @click="confirmDelete(listing)"
                      class="text-xs font-semibold px-2 py-1 rounded transition-colors hover:bg-rose-50 text-rose-600"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="listings.length === 0">
                <td colspan="4" class="px-5 py-10 text-center text-sm text-muted-foreground">
                  No listings found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="paginationLinks.length" class="px-5 py-4 border-t border-border flex flex-wrap gap-2">
          <a
            v-for="link in paginationLinks"
            :key="link.label"
            :href="link.url || '#'"
            v-html="link.label"
            class="px-3 py-1.5 rounded-lg text-xs border"
            :class="link.active
              ? 'bg-[#245c4a] text-white border-[#245c4a]'
              : 'bg-white text-slate-600 border-border'"
          />
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl border border-border">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
          <div>
            <h2 class="text-base font-semibold" style="color:#245c4a">
              {{ editingListing ? 'Edit Listing' : 'Create Listing' }}
            </h2>
            <p class="text-xs text-muted-foreground mt-0.5">
              Vendor-defined product grouping.
            </p>
          </div>

          <button @click="closeModal" class="text-slate-500 hover:text-slate-700 text-sm">✕</button>
        </div>

        <form @submit.prevent="submit" class="px-5 py-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
              Listing Name
            </label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-3 py-2 rounded-xl border border-border bg-input text-foreground focus:outline-none focus:ring-2 text-sm"
              style="--tw-ring-color: rgba(36,92,74,.35);"
            />
            <p v-if="form.errors.name" class="text-xs mt-1 text-rose-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
              Description
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 rounded-xl border border-border bg-input text-foreground focus:outline-none focus:ring-2 text-sm"
              style="--tw-ring-color: rgba(36,92,74,.35);"
            />
            <p v-if="form.errors.description" class="text-xs mt-1 text-rose-600">{{ form.errors.description }}</p>
          </div>

          <div class="flex items-center gap-2">
            <input v-model="form.is_active" type="checkbox" id="is_active" />
            <label for="is_active" class="text-sm text-slate-700">Active listing</label>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" @click="closeModal" class="px-4 py-2 rounded-xl border border-border text-sm">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 rounded-xl text-sm text-white" style="background:#245c4a">
              {{ editingListing ? 'Save Changes' : 'Create Listing' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <ConfirmationModal
      :open="showDeleteModal"
      title="Delete listing?"
      description="This will remove the listing group. Products attached to it will only be detached from this listing and will not be deleted."
      confirm-text="Delete"
      cancel-text="Cancel"
      variant="destructive"
      :loading="deleteProcessing"
      @update:open="showDeleteModal = $event"
      @confirm="deleteListing"
    />
  </VendorLayout>
</template>