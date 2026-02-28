<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import VendorLayout from '@/layouts/VendorLayout.vue';

const props = defineProps<{
    products: {
        data: Array<{
            id: number;
            name: string;
            description: string | null;
            price: number;
            stock: number;
            created_at: string;
        }>;
    };
}>();

const showModal = ref(false);
const editingProduct = ref<any>(null);

const form = useForm({
    name: '',
    description: '',
    price: 0,
    stock: 0,
});

function openCreateModal() {
    editingProduct.value = null;
    form.reset();
    showModal.value = true;
}

function openEditModal(product: any) {
    editingProduct.value = product;
    form.name = product.name;
    form.description = product.description || '';
    form.price = product.price;
    form.stock = product.stock;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    editingProduct.value = null;
}

function submit() {
    if (editingProduct.value) {
        form.put(`/vendor/products/${editingProduct.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/vendor/products', {
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
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Products</h1>
                <button 
                    @click="openCreateModal" 
                    class="px-4 py-2 bg-emerald-700 text-white rounded-lg hover:bg-emerald-800"
                >
                    + Add Product
                </button>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="product in products.data" :key="product.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ product.name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ product.description || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱{{ product.price }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ product.stock }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button @click="openEditModal(product)" class="text-blue-600 hover:text-blue-900">
                                    Edit
                                </button>
                                <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No products yet. Click "Add Product" to create one.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h2 class="text-xl font-bold mb-4 text-gray-900">
                        {{ editingProduct ? 'Edit Product' : 'Add Product' }}
                    </h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Product Name
                            </label>
                            <input 
                                id="name" 
                                v-model="form.name" 
                                required 
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea 
                                id="description" 
                                v-model="form.description" 
                                rows="3"
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                Price (₱)
                            </label>
                            <input 
                                id="price" 
                                v-model="form.price" 
                                type="number" 
                                step="0.01" 
                                required 
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>

                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">
                                Stock
                            </label>
                            <input 
                                id="stock" 
                                v-model="form.stock" 
                                type="number" 
                                required 
                                class="w-full px-3 py-2 text-gray-900 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                        </div>

                        <div class="flex justify-end space-x-2 pt-4">
                            <button 
                                type="button" 
                                @click="closeModal" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 disabled:opacity-50"
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
