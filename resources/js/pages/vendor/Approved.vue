<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';

interface Props {
  subdomain: string;
  tenant: {
    id: string;
    name: string;
  };
  defaultCredentials: {
    login_id: string;
    password: string;
  };
}

const props = defineProps<Props>();

const fullUrl = computed(() => `http://${props.subdomain}`);
</script>

<template>
  <Head title="Store Approved" />

  <div class="dashboard-wrapper">
    <Header />
    <Sidebar role="vendor">
      <VendorNav />
    </Sidebar>

    <main class="dashboard-content">
      <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-8">
          <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
              <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Congratulations! Your Store is Approved</h1>
            <p class="text-gray-600">Your store {{ tenant.name }} has been approved and is now live.</p>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-blue-900 mb-4">Access Your Store Dashboard</h2>
            <p class="text-blue-800 mb-4">
              You can now manage your store through your dedicated subdomain. Use the following default credentials to log in:
            </p>

            <div class="bg-white rounded border p-4 mb-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Subdomain</label>
                  <a :href="fullUrl" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                    {{ subdomain }}
                  </a>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Login ID</label>
                  <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ defaultCredentials.login_id }}</code>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Default Password</label>
                  <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ defaultCredentials.password }}</code>
                </div>
              </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
              <div class="flex items-start">
                <svg class="h-5 w-5 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                  <h3 class="text-sm font-medium text-yellow-800">Important Security Notice</h3>
                  <p class="text-sm text-yellow-700 mt-1">
                    Please change the default password immediately after your first login for security reasons.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center">
            <a :href="fullUrl" target="_self" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
              Open Your Store Dashboard
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>