<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LayoutGrid, AlertCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ConfirmationModal } from '@/components/ui/modal';
import VendorLayout from '@/layouts/VendorLayout.vue';

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

    <Dialog :open="showModal" @update:open="showModal = $event">
      <DialogContent class="sm:max-w-[480px] p-0 overflow-hidden border-none shadow-2xl rounded-3xl bg-white dark:bg-slate-900">
        <DialogHeader class="px-6 pt-8 pb-6 bg-gradient-to-br from-[#1B4D3E] to-[#245C4A] relative overflow-hidden">
          <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
          <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-[#C5A059]/20 rounded-full blur-xl"></div>
          
          <div class="relative z-10 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20">
              <LayoutGrid class="w-6 h-6 text-white" />
            </div>
            <div>
              <DialogTitle class="text-xl font-bold text-white tracking-tight">
                {{ editingListing ? 'Edit Listing' : 'Create Listing' }}
              </DialogTitle>
              <DialogDescription class="text-emerald-100/80 text-xs mt-0.5 font-medium uppercase tracking-wider">
                {{ editingListing ? 'Update listing collection details' : 'Define a new product collection' }}
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div class="px-6 py-6">
          <form id="listing-form" @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
              <Label for="listing-name" class="text-[11px] font-bold uppercase tracking-widest text-[#245C4A]/70">Listing Name</Label>
              <Input
                id="listing-name"
                v-model="form.name"
                placeholder="e.g. Summer Collection"
                class="h-11 bg-slate-50 border-slate-200 focus:bg-white transition-all rounded-xl"
                required
              />
              <p v-if="form.errors.name" class="text-[10px] font-semibold text-rose-500 mt-1 flex items-center gap-1">
                <AlertCircle class="w-3 h-3" /> {{ form.errors.name }}
              </p>
            </div>

            <div class="space-y-2">
              <Label for="listing-desc" class="text-[11px] font-bold uppercase tracking-widest text-[#245C4A]/70">Description</Label>
              <textarea
                id="listing-desc"
                v-model="form.description"
                placeholder="Briefly describe this collection..."
                rows="4"
                class="flex min-h-[80px] w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 focus:bg-white transition-all resize-none"
              ></textarea>
              <p v-if="form.errors.description" class="text-[10px] font-semibold text-rose-500 mt-1 flex items-center gap-1">
                <AlertCircle class="w-3 h-3" /> {{ form.errors.description }}
              </p>
            </div>

            <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 transition-all hover:bg-emerald-50/30 hover:border-emerald-100">
              <Checkbox id="is_active" v-model:checked="form.is_active" />
              <Label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">Set as active collection</Label>
            </div>
          </form>
        </div>

        <DialogFooter class="px-6 py-4 bg-slate-50 flex flex-row items-center justify-end gap-3 border-t border-slate-100">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-800 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            form="listing-form"
            class="h-11 px-6 rounded-xl bg-[#245C4A] text-white text-xs font-bold uppercase tracking-widest shadow-lg shadow-emerald-900/20 hover:shadow-emerald-900/30 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 flex items-center gap-2"
          >
            <template v-if="form.processing">
              <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
              Processing...
            </template>
            <template v-else>
              {{ editingListing ? 'Save Changes' : 'Create Listing' }}
            </template>
          </button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

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