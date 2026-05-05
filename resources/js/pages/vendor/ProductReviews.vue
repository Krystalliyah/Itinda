<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { Star, StarOff, ThumbsUp, CheckCircle, XCircle, MessageSquare, Reply, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import VendorLayout from '@/layouts/VendorLayout.vue';

// Define types for review data
interface ReviewUser {
  id: number;
  name: string;
  email: string;
}

interface Review {
  id: number;
  user_id: number;
  rating: number;
  title: string | null;
  comment: string;
  images: string[] | null;
  is_verified_purchase: boolean;
  is_approved: boolean;
  is_featured: boolean;
  helpful_count: number;
  admin_response: string | null;
  admin_response_at: string | null;
  created_at: string;
  updated_at: string;
  user?: ReviewUser;
}

interface ReviewStats {
  total: number;
  average_rating: number;
  rating_distribution: Record<number, number>;
  approved: number;
  pending: number;
}

interface Product {
  id: number;
  name: string;
  price: number;
  image_path: string | null;
}

const props = defineProps<{
  product: Product;
  reviews: {
    data: Review[];
    links: any[];
    meta: any;
  };
  stats: ReviewStats;
}>();

const selectedReview = ref<Review | null>(null);
const showResponseModal = ref(false);
const responseText = ref('');
const submitting = ref(false);
const filterStatus = ref<'all' | 'approved' | 'pending'>('all');
const filterRating = ref<number | null>(null);
const searchQuery = ref('');

// Image lightbox
const lightboxImage = ref<string | null>(null);
const showLightbox = ref(false);

// Open image in lightbox
const openImage = (url: string) => {
  lightboxImage.value = url;
  showLightbox.value = true;
};

// Close lightbox
const closeLightbox = () => {
  showLightbox.value = false;
  lightboxImage.value = null;
};

const filteredReviews = computed(() => {
  let filtered = props.reviews.data;
  
  if (filterStatus.value !== 'all') {
    filtered = filtered.filter((review: Review) => 
      filterStatus.value === 'approved' ? review.is_approved : !review.is_approved
    );
  }
  
  if (filterRating.value) {
    filtered = filtered.filter((review: Review) => review.rating === filterRating.value);
  }
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter((review: Review) => 
      review.comment?.toLowerCase().includes(query) ||
      review.title?.toLowerCase().includes(query) ||
      review.user?.name?.toLowerCase().includes(query)
    );
  }
  
  return filtered;
});

const formatDate = (date: string | null) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const approveReview = (review: Review) => {
  if (confirm('Approve this review?')) {
    router.post(`/vendor/products/${props.product.id}/reviews/${review.id}/approve`, {}, {
      preserveScroll: true,
    });
  }
};

const rejectReview = (review: Review) => {
  if (confirm('Reject this review? It will be permanently deleted.')) {
    router.delete(`/vendor/products/${props.product.id}/reviews/${review.id}`, {
      preserveScroll: true,
    });
  }
};

const toggleFeature = (review: Review) => {
  router.post(`/vendor/products/${props.product.id}/reviews/${review.id}/feature`, {}, {
    preserveScroll: true,
  });
};

const openResponseModal = (review: Review) => {
  selectedReview.value = review;
  responseText.value = review.admin_response || '';
  showResponseModal.value = true;
};

const submitResponse = () => {
  if (!selectedReview.value) return;
  submitting.value = true;
  
  router.post(`/vendor/products/${props.product.id}/reviews/${selectedReview.value.id}/respond`, {
    admin_response: responseText.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showResponseModal.value = false;
      responseText.value = '';
      selectedReview.value = null;
      submitting.value = false;
    },
    onError: () => {
      submitting.value = false;
    },
  });
};
</script>

<template>
  <Head :title="`Reviews for ${product.name}`" />

  <VendorLayout>
    <div class="p-4 sm:p-6 flex flex-col gap-4">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color:#245c4a">
            <span style="color:#C5A059">✦</span> Customer Feedback
          </p>
          <h1 class="text-xl sm:text-2xl font-semibold tracking-tight" style="color:#245c4a">
            Product Reviews
          </h1>
          <p class="text-xs sm:text-sm text-muted-foreground mt-1">
            Manage reviews for {{ product.name }}
          </p>
        </div>
        
        <Link href="/vendor/products" class="inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700">
          ← Back to Products
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Reviews</p>
          <p class="text-2xl font-semibold mt-1" style="color:#245c4a">{{ stats.total }}</p>
        </div>
        
        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Average Rating</p>
          <div class="flex items-center gap-2 mt-1">
            <p class="text-2xl font-semibold" style="color:#C5A059">{{ stats.average_rating }}</p>
            <div class="flex">
              <Star v-for="i in 5" :key="i" 
                :class="i <= Math.round(stats.average_rating) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                class="w-4 h-4" />
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Approved</p>
          <p class="text-2xl font-semibold mt-1" style="color:#245c4a">{{ stats.approved }}</p>
        </div>
        
        <div class="bg-white rounded-xl border border-border shadow-sm p-4">
          <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Pending</p>
          <p class="text-2xl font-semibold mt-1" style="color:#C5A059">{{ stats.pending }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl border border-border shadow-sm p-4">
        <div class="flex flex-wrap gap-3 items-center justify-between">
          <div class="flex flex-wrap gap-2">
            <button
              @click="filterStatus = 'all'"
              :class="filterStatus === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="px-3 py-1.5 text-xs rounded-lg transition-colors"
            >
              All ({{ stats.total }})
            </button>
            <button
              @click="filterStatus = 'approved'"
              :class="filterStatus === 'approved' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="px-3 py-1.5 text-xs rounded-lg transition-colors"
            >
              Approved ({{ stats.approved }})
            </button>
            <button
              @click="filterStatus = 'pending'"
              :class="filterStatus === 'pending' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="px-3 py-1.5 text-xs rounded-lg transition-colors"
            >
              Pending ({{ stats.pending }})
            </button>
          </div>
          
          <div class="flex gap-2">
            <select v-model="filterRating" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200">
              <option :value="null">All Ratings</option>
              <option v-for="i in 5" :key="i" :value="i">{{ i }} ★</option>
            </select>
            
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search reviews..."
              class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 w-48"
            />
          </div>
        </div>
      </div>

      <!-- Reviews List -->
      <div class="space-y-3">
        <div v-if="filteredReviews.length === 0" class="bg-white rounded-xl border border-border shadow-sm p-10 text-center">
          <p class="text-muted-foreground">No reviews found</p>
        </div>
        
        <div v-for="review in filteredReviews" :key="review.id" 
          class="bg-white rounded-xl border border-border shadow-sm overflow-hidden">
          <div class="p-5">
            <div class="flex items-start justify-between flex-wrap gap-3 mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                  <span class="text-emerald-700 font-semibold">
                    {{ review.user?.name?.charAt(0).toUpperCase() || '?' }}
                  </span>
                </div>
                <div>
                  <p class="font-medium">{{ review.user?.name || 'Anonymous' }}</p>
                  <p class="text-xs text-muted-foreground">{{ formatDate(review.created_at) }}</p>
                </div>
              </div>
              
              <div class="flex items-center gap-2">
                <div class="flex">
                  <Star v-for="i in 5" :key="i" 
                    :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                    class="w-4 h-4" />
                </div>
                
                <span v-if="review.is_verified_purchase" 
                  class="inline-flex items-center gap-1 text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                  <CheckCircle class="w-3 h-3" />
                  Verified Purchase
                </span>
              </div>
            </div>
            
            <h4 class="font-semibold text-foreground mb-2">{{ review.title || 'No title' }}</h4>
            <p class="text-sm text-muted-foreground mb-3">{{ review.comment }}</p>
            
            <div v-if="review.images && review.images.length" class="flex gap-2 mb-3">
              <div v-for="(img, idx) in review.images" :key="idx" 
                class="w-16 h-16 rounded-lg overflow-hidden border border-border cursor-pointer hover:opacity-80 transition-opacity"
                @click="openImage(img)">
                <img :src="img" class="w-full h-full object-cover" />
              </div>
            </div>
            
            <div class="flex items-center gap-1 text-xs text-muted-foreground mb-3">
              <ThumbsUp class="w-3 h-3" />
              <span>{{ review.helpful_count }} found this helpful</span>
            </div>
            
            <div v-if="review.admin_response" class="mt-3 p-3 bg-gray-50 rounded-lg border-l-4 border-emerald-500">
              <div class="flex items-center gap-2 mb-1">
                <Reply class="w-3 h-3 text-emerald-600" />
                <span class="text-xs font-semibold text-emerald-600">Store Response</span>
                <span class="text-xs text-muted-foreground">{{ formatDate(review.admin_response_at) }}</span>
              </div>
              <p class="text-sm">{{ review.admin_response }}</p>
            </div>
            
            <div class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-border">
              <button
                v-if="!review.is_approved"
                @click="approveReview(review)"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100"
              >
                <CheckCircle class="w-3 h-3" />
                Approve
              </button>
              
              <button
                v-if="review.is_approved"
                @click="rejectReview(review)"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg bg-red-50 text-red-700 hover:bg-red-100"
              >
                <XCircle class="w-3 h-3" />
                Reject
              </button>
              
              <button
                @click="toggleFeature(review)"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg border border-gray-200 hover:bg-gray-50"
              >
                <Star class="w-3 h-3" :class="review.is_featured ? 'text-yellow-500 fill-yellow-500' : ''" />
                {{ review.is_featured ? 'Unfeature' : 'Feature' }}
              </button>
              
              <button
                @click="openResponseModal(review)"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg border border-gray-200 hover:bg-gray-50"
              >
                <MessageSquare class="w-3 h-3" />
                {{ review.admin_response ? 'Edit Response' : 'Respond' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Response Modal -->
    <Teleport to="body">
      <div v-if="showResponseModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="background:rgba(0,0,0,0.5)">
        <div class="bg-white rounded-xl max-w-lg w-full">
          <div class="px-5 py-4 border-b border-border flex justify-between items-center">
            <h3 class="text-sm font-semibold" style="color:#245c4a">Respond to Review</h3>
            <button @click="showResponseModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
          </div>
          
          <div class="p-5">
            <p class="text-sm text-muted-foreground mb-3">
              Review from <strong>{{ selectedReview?.user?.name }}</strong>
            </p>
            <p class="text-sm bg-gray-50 p-3 rounded-lg mb-4 italic">
              "{{ selectedReview?.comment }}"
            </p>
            
            <textarea
              v-model="responseText"
              rows="4"
              class="w-full px-3 py-2 rounded-xl border border-border resize-none"
              placeholder="Write your response to this customer..."
            ></textarea>
            
            <div class="flex justify-end gap-2 mt-4">
              <button @click="showResponseModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl border border-border">
                Cancel
              </button>
              <button 
                @click="submitResponse" 
                :disabled="submitting"
                class="px-4 py-2 text-xs font-semibold rounded-xl text-white" 
                style="background:#245c4a"
              >
                {{ submitting ? 'Saving...' : 'Post Response' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Image Lightbox Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showLightbox"
          class="fixed inset-0 z-[10000] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
          @click="closeLightbox"
        >
          <div class="relative w-full max-w-2xl max-h-[70vh] flex items-center justify-center" @click.stop>
            <!-- Image Container -->
            <div class="relative w-full h-full flex items-center justify-center bg-black/40 rounded-xl overflow-hidden">
              <img
                :src="lightboxImage"
                alt="Full size image"
                class="w-full h-full object-contain"
              />
            </div>

            <!-- Close button - Top Right -->
            <button
              @click="closeLightbox"
              class="absolute -top-12 right-0 p-2 rounded-full bg-emerald-600/80 hover:bg-emerald-600 text-white transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 focus:ring-offset-black"
              aria-label="Close image viewer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </VendorLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>