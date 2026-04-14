<script setup lang="ts">
import { Head, usePage, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
import { useSidebar } from '@/composables/useSidebar';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { 
  ChevronLeft, 
  ShoppingCart, 
  Store, 
  Star, 
  StarHalf,
  StarOff,
  Package, 
  ThumbsUp,
  CheckCircle,
  Image as ImageIcon,
  X,
  Loader2,
  Ruler,
  Scale,
  Droplet,
  Flame,
  Leaf,
  Shield,
  Truck,
  RefreshCw,
  Clock,
  Tag,
  Info
} from 'lucide-vue-next';

const props = defineProps<{
  storeId: string;
  productId: number;
}>();

const { isCollapsed } = useSidebar();
const contentClass = computed(() => ({
  'dashboard-content': true,
  'sidebar-collapsed': isCollapsed.value,
}));

// Track referrer from query parameter
const referrer = ref<'products' | 'store'>('products');

const backLink = computed(() => {
  if (referrer.value === 'store') {
    return `/customer/stores/${props.storeId}`;
  }
  return '/customer/products';
});
const backLabel = computed(() => {
  if (referrer.value === 'store') {
    return 'Back to Store';
  }
  return 'Back to Products';
});

const product = ref<any>(null);
const loading = ref(true);
const error = ref<string | null>(null);

// Mock product specifications data
const productSpecs = ref({
  weight: '500g',
  dimensions: '25cm x 15cm x 10cm',
  material: 'Premium Quality',
  origin: 'Imported',
  brand: 'Premium Brand',
  ingredients: 'All-natural ingredients, no preservatives',
  care_instructions: 'Store in cool dry place. Avoid direct sunlight.'
});

// Helper function to safely get rating distribution
const getRatingDistribution = (star: number) => {
  const key = star as 1 | 2 | 3 | 4 | 5;
  return reviewStats.value.rating_distribution?.[key] || 0;
};

// Helper function to safely get rating percentage
const getRatingPercentage = (star: number) => {
  const key = star as 1 | 2 | 3 | 4 | 5;
  return reviewStats.value.rating_percentages?.[key] || 0;
};

// Reviews state
const reviews = ref<any[]>([]);
const reviewStats = ref({
  average_rating: 0,
  total_reviews: 0,
  rating_distribution: { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 },
  rating_percentages: { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 }
});
const loadingReviews = ref(false);
const reviewFilter = ref<number | null>(null);
const reviewSort = ref('newest');
const reviewPage = ref(1);
const hasMoreReviews = ref(false);

// Review form
const showReviewModal = ref(false);
const submittingReview = ref(false);
const reviewForm = ref({
  rating: 0,
  title: '',
  comment: '',
  images: [] as File[]
});
const imagePreviews = ref<string[]>([]);

// Helpfulness voting
const votingReview = ref<number | null>(null);

// Quantity
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

// Fetch product
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

// Fetch reviews
const fetchReviews = async (append = false) => {
  try {
    loadingReviews.value = true;
    
    const params = new URLSearchParams({
      page: reviewPage.value.toString(),
      per_page: '10',
      sort: reviewSort.value,
    });
    
    if (reviewFilter.value) {
      params.append('rating', reviewFilter.value.toString());
    }
    
    const response = await fetch(`/customer/products/${props.storeId}/${props.productId}/reviews?${params}`);
    const data = await response.json();
    
    console.log('Fetched reviews:', data);
    
    if (data.success && data.data) {
      if (append) {
        reviews.value = [...reviews.value, ...data.data.data];
      } else {
        reviews.value = data.data.data;
      }
      // FIX #2: Use last_page instead of next_page_url
      hasMoreReviews.value = data.data.current_page < data.data.last_page;
    }
  } catch (error) {
    console.error('Error fetching reviews:', error);
  } finally {
    loadingReviews.value = false;
  }
};

// Fetch review stats
const fetchReviewStats = async () => {
  try {
    const response = await fetch(`/customer/products/${props.storeId}/${props.productId}/reviews/stats`);
    const data = await response.json();
    reviewStats.value = data.data;
  } catch (error) {
    console.error('Error fetching review stats:', error);
  }
};

// Load more reviews
const loadMoreReviews = () => {
  if (hasMoreReviews.value && !loadingReviews.value) {
    reviewPage.value++;
    fetchReviews(true);
  }
};

// Handle rating click in form
const setRating = (rating: number) => {
  reviewForm.value.rating = rating;
};

// Handle image upload
const handleImageUpload = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files) {
    const files = Array.from(input.files);
    reviewForm.value.images = [...reviewForm.value.images, ...files];
    
    // Create previews
    files.forEach(file => {
      const reader = new FileReader();
      reader.onload = (e) => {
        imagePreviews.value.push(e.target?.result as string);
      };
      reader.readAsDataURL(file);
    });
  }
};

// Remove image
const removeImage = (index: number) => {
  reviewForm.value.images.splice(index, 1);
  imagePreviews.value.splice(index, 1);
};

// Image lightbox
const lightboxImage = ref<string | undefined>(undefined);
const showLightbox = ref(false);

const openImage = (url: string) => {
  lightboxImage.value = url;
  showLightbox.value = true;
};

const closeLightbox = () => {
  showLightbox.value = false;
  lightboxImage.value = undefined;
};

// Submit review
const submitReview = async () => {
  if (reviewForm.value.rating === 0) {
    showToast('Please select a rating', 'error');
    return;
  }
  
  if (!reviewForm.value.comment.trim()) {
    showToast('Please write a review comment', 'error');
    return;
  }
  
  submittingReview.value = true;
  
  const formData = new FormData();
  formData.append('rating', reviewForm.value.rating.toString());
  formData.append('title', reviewForm.value.title);
  formData.append('comment', reviewForm.value.comment);
  
  reviewForm.value.images.forEach((image, index) => {
    formData.append(`images[${index}]`, image);
  });
  
  try {
    const response = await fetch(`/customer/products/${props.storeId}/${props.productId}/reviews`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: formData,
    });
    
    const data = await response.json();
    
    if (response.ok && data.success) {
      showToast('Review submitted successfully!', 'success');
      showReviewModal.value = false;
      resetReviewForm();
      
      reviewPage.value = 1;
      await new Promise(resolve => setTimeout(resolve, 300));
      await fetchReviews();
      await fetchReviewStats();
    } else {
      const errorMessage = data.message || 'Failed to submit review';
      showToast(errorMessage, 'error');
    }
  } catch (error) {
    console.error('Error submitting review:', error);
    showToast('Network error. Please try again.', 'error');
  } finally {
    submittingReview.value = false;
  }
};

// Mark review as helpful
const markHelpful = async (reviewId: number) => {
  votingReview.value = reviewId;
  
  try {
    const response = await fetch(`/customer/reviews/${props.storeId}/${reviewId}/helpful`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
      },
      body: JSON.stringify({ helpful: true }),
    });
    
    if (!response.ok) {
      const text = await response.text();
      console.error('Response error:', text);
      showToast('Failed to update vote', 'error');
      return;
    }
    
    const data = await response.json();
    
    if (data.success) {
      const review = reviews.value.find(r => r.id === reviewId);
      if (review) {
        review.helpful_count = data.helpful_count;
        review.user_has_voted = data.action === 'voted';
      }
      const message = data.action === 'voted' ? 'Thanks for your feedback!' : 'You removed your vote';
      showToast(message, 'success');
    } else {
      showToast(data.message || 'Failed to update vote', 'error');
    }
  } catch (error) {
    console.error('Error marking helpful:', error);
    showToast('Network error. Please try again.', 'error');
  } finally {
    votingReview.value = null;
  }
};

// Reset review form
const resetReviewForm = () => {
  reviewForm.value = {
    rating: 0,
    title: '',
    comment: '',
    images: []
  };
  imagePreviews.value = [];
};

// Toast
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
const showToast = (message: string, type: 'success' | 'error' = 'success') => {
  if (toastTimer) clearTimeout(toastTimer);
  toast.value = { message, type };
  toastTimer = setTimeout(() => { toast.value = null; }, 3000);
};

// Add to cart
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

// Format date
const formatDate = (date: string | null) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Check if user is logged in
const isLoggedIn = computed(() => {
  return usePage().props.auth.user !== null;
});

onMounted(() => {
  // Check if 'from=store' is in the URL query parameters
  const url = new URL(window.location.href);
  if (url.searchParams.get('from') === 'store') {
    referrer.value = 'store';
  }

  fetchProduct();
  fetchReviews();
  fetchReviewStats();

  // Add keyboard support for closing lightbox
  const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && showLightbox.value) {
      closeLightbox();
    }
  };

  window.addEventListener('keydown', handleKeyDown);

  // Cleanup
  return () => {
    window.removeEventListener('keydown', handleKeyDown);
  };
});
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
        <Link :href="backLink" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-[#245c4a] transition">
          <ChevronLeft class="w-4 h-4" />
          {{ backLabel }}
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
              <div class="flex items-center gap-1">
                <div class="flex">
                  <Star v-for="i in 5" :key="i" 
                    :class="i <= Math.round(reviewStats.average_rating) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                    class="w-4 h-4" />
                </div>
                <span>{{ reviewStats.average_rating }}</span>
                <span>({{ reviewStats.total_reviews }} reviews)</span>
              </div>
              <span class="flex items-center gap-1">
                <Package class="w-4 h-4" />
                {{ product.sold_count }} sold
              </span>
            </div>

            <!-- Price -->
            <div class="text-3xl font-bold text-[#245c4a]">
              ₱{{ product.unit_price.toFixed(2) }}
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

        <!-- Product Specifications Section -->
        <div v-if="product" class="mt-8 pt-8 border-t border-border">
          <div class="flex items-center gap-2 mb-4">
            <Info class="w-5 h-5 text-[#245c4a]" />
            <h2 class="text-xl font-semibold text-foreground">Product Specifications</h2>
          </div>

          <div class="bg-white rounded-xl border border-border shadow-sm overflow-hidden">
            <div class="divide-y divide-border">
              <div class="flex px-4 py-3">
                <span class="text-sm text-muted-foreground w-32">Weight</span>
                <span class="text-sm font-medium text-foreground">{{ productSpecs.weight }}</span>
              </div>
              <div class="flex px-4 py-3">
                <span class="text-sm text-muted-foreground w-32">Dimensions</span>
                <span class="text-sm font-medium text-foreground">{{ productSpecs.dimensions }}</span>
              </div>
              <div class="flex px-4 py-3">
                <span class="text-sm text-muted-foreground w-32">Material</span>
                <span class="text-sm font-medium text-foreground">{{ productSpecs.material }}</span>
              </div>
              <div class="flex px-4 py-3">
                <span class="text-sm text-muted-foreground w-32">Origin</span>
                <span class="text-sm font-medium text-foreground">{{ productSpecs.origin }}</span>
              </div>
              <div class="flex px-4 py-3">
                <span class="text-sm text-muted-foreground w-32">Brand</span>
                <span class="text-sm font-medium text-foreground">{{ productSpecs.brand }}</span>
              </div>
              <div class="flex px-4 py-3">
                <span class="text-sm text-muted-foreground w-32">Ingredients</span>
                <span class="text-sm font-medium text-foreground flex-1">{{ productSpecs.ingredients }}</span>
              </div>
              <div class="flex px-4 py-3">
                <span class="text-sm text-muted-foreground w-32">Care Instructions</span>
                <span class="text-sm font-medium text-foreground flex-1">{{ productSpecs.care_instructions }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reviews Section -->
        <div v-if="product" class="mt-8 pt-8 border-t border-border">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-foreground">Customer Reviews</h2>
            <Button 
              @click="showReviewModal = true"
              :disabled="!isLoggedIn"
              class="bg-[#245c4a] hover:bg-[#1B4D3E] text-white"
            >
              Write a Review
            </Button>
          </div>
          
          <!-- Rating Summary Bar -->
          <div class="bg-white rounded-xl border border-border shadow-sm p-4 mb-4">
            <div class="flex flex-col md:flex-row gap-6">
              <div class="text-center md:text-left">
                <div class="text-4xl font-bold text-foreground">{{ reviewStats.average_rating }}</div>
                <div class="flex justify-center md:justify-start mt-1">
                  <Star v-for="i in 5" :key="i" 
                    :class="i <= Math.round(reviewStats.average_rating) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                    class="w-4 h-4" />
                </div>
                <div class="text-sm text-muted-foreground mt-1">{{ reviewStats.total_reviews }} reviews</div>
              </div>
              
              <div class="flex-1 space-y-2">
                <div v-for="star in [5,4,3,2,1]" :key="star" class="flex items-center gap-2">
                  <span class="text-sm w-8">{{ star }}★</span>
                  <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div 
                      class="h-full bg-yellow-400 rounded-full"
                      :style="{ width: `${getRatingPercentage(star)}%` }"
                    ></div>
                  </div>
                  <span class="text-xs text-muted-foreground w-12">
                    {{ getRatingDistribution(star) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Review Filters -->
          <div class="flex flex-wrap gap-3 mb-4">
            <button
              @click="reviewFilter = null; reviewPage = 1; fetchReviews()"
              :class="reviewFilter === null ? 'bg-[#245c4a] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="px-3 py-1.5 text-sm rounded-lg transition-colors"
            >
              All
            </button>
            <button
              v-for="star in [5,4,3,2,1]"
              :key="star"
              @click="reviewFilter = star; reviewPage = 1; fetchReviews()"
              :class="reviewFilter === star ? 'bg-[#245c4a] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="px-3 py-1.5 text-sm rounded-lg transition-colors flex items-center gap-1"
            >
              {{ star }} <Star class="w-3 h-3" />
            </button>
            
            <select v-model="reviewSort" @change="reviewPage = 1; fetchReviews()" class="ml-auto px-3 py-1.5 text-sm rounded-lg border border-gray-200">
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="highest">Highest Rating</option>
              <option value="lowest">Lowest Rating</option>
              <option value="helpful">Most Helpful</option>
            </select>
          </div>
          
          <!-- Reviews List -->
          <div class="space-y-4">
            <div v-if="loadingReviews && reviews.length === 0" class="text-center py-8">
              <Loader2 class="w-8 h-8 animate-spin text-[#245c4a] mx-auto" />
              <p class="text-muted-foreground mt-2">Loading reviews...</p>
            </div>
            
            <div v-else-if="reviews.length === 0" class="bg-white rounded-xl border border-border shadow-sm p-8 text-center">
              <p v-if="reviewFilter === null && reviewStats.total_reviews === 0" class="text-muted-foreground">No reviews yet. Be the first to review this product!</p>
              <p v-else class="text-muted-foreground">No reviews with this rating yet.</p>
            </div>
            
            <div v-for="review in reviews" :key="review.id" class="bg-white rounded-xl border border-border shadow-sm p-4">
              <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <span class="text-emerald-700 font-semibold">
                      {{ review.user?.name?.charAt(0).toUpperCase() || '?' }}
                    </span>
                  </div>
                  <div>
                    <p class="font-medium">{{ review.user?.name || 'Anonymous' }}</p>
                    <div class="flex items-center gap-1 mt-1">
                      <Star v-for="i in 5" :key="i" 
                        :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                        class="w-4 h-4" />
                      <span class="text-xs text-muted-foreground ml-2">{{ formatDate(review.created_at) }}</span>
                    </div>
                  </div>
                </div>
                
                <span v-if="review.is_verified_purchase" 
                  class="inline-flex items-center gap-1 text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                  <CheckCircle class="w-3 h-3" />
                  Verified Purchase
                </span>
              </div>
              
              <h4 class="font-semibold mt-3">{{ review.title || 'Customer Review' }}</h4>
              <p class="text-sm text-muted-foreground mt-2">{{ review.comment }}</p>
              
              <!-- Review Images -->
              <div v-if="review.images && review.images.length" class="flex gap-2 mt-3">
                <div v-for="(img, idx) in review.images" :key="idx" 
                  class="w-16 h-16 rounded-lg overflow-hidden border border-border cursor-pointer"
                  @click="openImage(img)">
                  <img :src="img" class="w-full h-full object-cover" />
                </div>
              </div>
              
              <!-- Helpful Button -->
              <div class="flex items-center gap-4 mt-3 pt-3 border-t border-border">
                <button 
                  @click="markHelpful(review.id)"
                  :disabled="votingReview === review.id"
                  class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-emerald-600 transition-colors"
                >
                  <ThumbsUp class="w-3 h-3" />
                  <span>{{ review.helpful_count }} found this helpful</span>
                </button>
              </div>
              
              <!-- Admin Response -->
              <div v-if="review.admin_response" class="mt-3 p-3 bg-gray-50 rounded-lg border-l-4 border-emerald-500">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-semibold text-emerald-600">Store Response</span>
                  <span class="text-xs text-muted-foreground">{{ formatDate(review.admin_response_at) }}</span>
                </div>
                <p class="text-sm">{{ review.admin_response }}</p>
              </div>
            </div>
            
            <!-- Load More Button -->
            <div v-if="hasMoreReviews" class="text-center pt-4">
              <Button 
                @click="loadMoreReviews" 
                :disabled="loadingReviews"
                variant="outline"
              >
                {{ loadingReviews ? 'Loading...' : 'Load More Reviews' }}
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

  <!-- Write Review Modal -->
  <Teleport to="body">
    <Dialog v-model:open="showReviewModal">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Write a Review</DialogTitle>
          <DialogDescription>
            Share your experience with this product
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4 py-4">
          <!-- Rating Stars -->
          <div>
            <label class="block text-sm font-medium mb-2">Your Rating *</label>
            <div class="flex gap-1">
              <button
                v-for="i in 5"
                :key="i"
                @click="setRating(i)"
                class="focus:outline-none"
              >
                <Star 
                  :class="i <= reviewForm.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'"
                  class="w-8 h-8 hover:scale-110 transition-transform"
                />
              </button>
            </div>
          </div>
          
          <!-- Review Title -->
          <div>
            <label class="block text-sm font-medium mb-2">Review Title (Optional)</label>
            <Input 
              v-model="reviewForm.title" 
              placeholder="Summarize your experience"
            />
          </div>
          
          <!-- Review Comment -->
          <div>
            <label class="block text-sm font-medium mb-2">Your Review *</label>
            <textarea 
              v-model="reviewForm.comment" 
              placeholder="What did you like or dislike about this product?"
              rows="4"
              class="w-full px-3 py-2 rounded-xl border border-border resize-none"
            ></textarea>
          </div>
          
          <!-- Image Upload -->
          <div>
            <label class="block text-sm font-medium mb-2">Add Photos (Optional)</label>
            <div class="flex items-center gap-2">
              <label class="cursor-pointer">
                <div class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center hover:border-emerald-500 transition-colors">
                  <ImageIcon class="w-6 h-6 text-gray-400" />
                  <span class="text-xs text-gray-400 mt-1">Add</span>
                </div>
                <input type="file" accept="image/*" multiple class="hidden" @change="handleImageUpload" />
              </label>
              
              <!-- Image Previews -->
              <div v-for="(preview, idx) in imagePreviews" :key="idx" class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-200">
                <img :src="preview" class="w-full h-full object-cover" />
                <button 
                  @click="removeImage(idx)"
                  class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-0.5 hover:bg-red-600"
                >
                  <X class="w-3 h-3" />
                </button>
              </div>
            </div>
            <p class="text-xs text-muted-foreground mt-2">Upload up to 5 images (JPG, PNG, max 2MB each)</p>
          </div>
        </div>
        
        <div class="flex justify-end gap-2">
          <Button variant="outline" @click="showReviewModal = false">Cancel</Button>
          <Button 
            @click="submitReview" 
            :disabled="submittingReview"
            class="bg-[#245c4a] hover:bg-[#1B4D3E] text-white"
          >
            {{ submittingReview ? 'Submitting...' : 'Submit Review' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </Teleport>

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

  <!-- Image Lightbox Modal -->
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="showLightbox"
        class="fixed inset-0 z-[10000] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click="closeLightbox"
      >
        <div class="relative w-full max-w-2xl max-h-[70vh] flex items-center justify-center" @click.stop>
          <div class="relative w-full h-full flex items-center justify-center bg-black/40 rounded-xl overflow-hidden">
            <img
              :src="lightboxImage"
              alt="Full size image"
              class="w-full h-full object-contain"
            />
          </div>

          <button
            @click="closeLightbox"
            class="absolute -top-12 right-0 p-2 rounded-full bg-[#245c4a]/80 hover:bg-[#245c4a] text-white transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#245c4a] focus:ring-offset-2 focus:ring-offset-black"
            aria-label="Close image viewer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
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