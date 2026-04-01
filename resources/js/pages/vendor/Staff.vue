<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import VendorLayout from '@/layouts/VendorLayout.vue';

type Permission = {
  id: number;
  name: string;
};

type Role = {
  id: number;
  name: string;
};

type Staff = {
  id: number;
  name: string;
  email: string;
  login_id: string;
  roles: Role[];
  permissions: Permission[];
};

const page = usePage();
const tenantId = computed(() => (page.props.auth as any).tenant_id || '');

const props = defineProps<{
  staff: Staff[];
  availablePermissions: Permission[];
  passwordRequirements?: {
    minLength: number;
    requireLetters: boolean;
    requireMixedCase: boolean;
    requireNumbers: boolean;
    requireSymbols: boolean;
  };
}>();

const showAddModal = ref(false);
const showPermissionsModal = ref(false);
const selectedStaff = ref<Staff | null>(null);
const showPasswordRequirements = ref(false);

const addForm = useForm({
  name: '',
  login_id: '',
  email: '',
  password: '',
  password_confirmation: '',
});

// Password validation state
const passwordErrors = computed(() => {
  const errors: string[] = [];
  const password = addForm.password;
  
  if (!password) return errors;
  
  const requirements = props.passwordRequirements || {
    minLength: 8,
    requireLetters: true,
    requireMixedCase: true,
    requireNumbers: true,
    requireSymbols: true,
  };
  
  if (requirements.minLength && password.length < requirements.minLength) {
    errors.push(`At least ${requirements.minLength} characters`);
  }
  
  if (requirements.requireLetters && !/[a-zA-Z]/.test(password)) {
    errors.push('At least one letter');
  }
  
  if (requirements.requireMixedCase && (!/[a-z]/.test(password) || !/[A-Z]/.test(password))) {
    errors.push('Both uppercase and lowercase letters');
  }
  
  if (requirements.requireNumbers && !/\d/.test(password)) {
    errors.push('At least one number');
  }
  
  if (requirements.requireSymbols && !/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    errors.push('At least one special character (!@#$%^&* etc.)');
  }
  
  return errors;
});

const isPasswordValid = computed(() => {
  return passwordErrors.value.length === 0 && addForm.password.length > 0;
});

const doPasswordsMatch = computed(() => {
  return addForm.password === addForm.password_confirmation;
});

const isSubmitDisabled = computed(() => {
  const processing = addForm.processing;
  const hasPasswordAndInvalid = !!(addForm.password && !isPasswordValid.value);
  const hasConfirmationAndMismatch = !!(addForm.password_confirmation && !doPasswordsMatch.value);
  
  return processing || hasPasswordAndInvalid || hasConfirmationAndMismatch;
});

// Auto-generate Login ID based on Name and Tenant ID
watch(() => addForm.name, (newName) => {
  if (newName && !editingLoginId.value) {
    const slug = newName.toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '_')
      .replace(/^-+|-+$/g, '');
    
    if (slug) {
      addForm.login_id = tenantId.value ? `${slug}-${tenantId.value}` : slug;
    } else {
      addForm.login_id = '';
    }
  }
});

const editingLoginId = ref(false);
const handleLoginIdInput = () => {
    editingLoginId.value = true;
};

const permissionsForm = useForm({
  permissions: [] as string[],
});

function openAddModal() {
  addForm.reset();
  editingLoginId.value = false;
  showPasswordRequirements.value = false;
  showAddModal.value = true;
}

function openPermissionsModal(member: Staff) {
  selectedStaff.value = member;
  permissionsForm.permissions = (member.permissions || []).map(p => p.name);
  showPermissionsModal.value = true;
}

function submitAdd() {
  addForm.post('/vendor/staff', {
    onSuccess: () => {
      showAddModal.value = false;
      addForm.reset();
    },
  });
}

function submitPermissions() {
  if (!selectedStaff.value) return;
  permissionsForm.put(`/vendor/staff/${selectedStaff.value.id}/permissions`, {
    onSuccess: () => {
      showPermissionsModal.value = false;
    },
  });
}

function deleteStaff(id: number) {
  if (confirm('Are you sure you want to remove this staff member?')) {
    router.delete(`/vendor/staff/${id}`);
  }
}

// Permission labels and descriptions
const getPermissionLabel = (name: string) => {
  const labels: Record<string, string> = {
    'manage-inventory': 'Inventory Manager',
    'manage-products': 'Product Catalog',
    'manage-orders': 'Order fulfillment',
    'edit-prices': 'Price Editor',
    'manage-staff': 'Staff Admin',
    'manage-store-settings': 'Store Settings',
    'view-analytics': 'Analytics Viewer',
    'view-expenses': 'Expense Tracker'
  };
  return labels[name] || name.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const getPermissionDescription = (name: string) => {
  const descriptions: Record<string, string> = {
    'manage-inventory': 'Can track stock levels and movement.',
    'manage-products': 'Can add, edit, or remove store products.',
    'manage-orders': 'Can view and process customer orders.',
    'edit-prices': 'Can change product prices and discounts.',
    'manage-staff': 'Can manage other staff accounts.',
    'manage-store-settings': 'Can update store address and hours.',
    'view-analytics': 'Can see sales trends and top products.',
    'view-expenses': 'Can record and view store costs.'
  };
  return descriptions[name] || 'Grants access to this module.';
};

function hasBaseRole(staff: Staff) {
    return staff.roles.some(r => r.name === 'vendor');
}
</script>

<template>
  <Head title="Staff Management" />

  <VendorLayout>
    <div class="p-4 sm:p-6 flex flex-col gap-4">
      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
          <p class="text-xs font-semibold uppercase tracking-widest mb-1" style="color:#245c4a">
            <span style="color:#C5A059">✦</span> Security
          </p>
          <h1 class="text-xl sm:text-2xl font-semibold tracking-tight" style="color:#245c4a">Staff Management</h1>
          <p class="text-xs sm:text-sm text-muted-foreground mt-1">
            Create staff accounts and customize what they can access.
          </p>
        </div>

        <button
          @click="openAddModal"
          class="w-full sm:w-auto inline-flex items-center justify-center text-xs font-semibold px-4 py-2.5 rounded-xl text-white transition-opacity hover:opacity-90"
          style="background:#245c4a"
        >
          + Add Staff
        </button>
      </div>

      <!-- Staff List -->
      <div class="bg-white rounded-xl border border-border shadow-sm overflow-hidden">
        <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-border">
          <h2 class="text-sm font-semibold" style="color:#245c4a">Team Members</h2>
        </div>

        <!-- Desktop Table -->
        <div class="hidden sm:block w-full overflow-x-auto">
          <table class="min-w-full border-collapse">
            <thead>
              <tr style="background:hsl(0 0% 96.1%)">
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">Staff</th>
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">Login ID / Email</th>
                <th class="text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">Current Access</th>
                <th class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground px-5 py-3 border-b border-border">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="member in staff" :key="member.id" class="border-b border-border last:border-0 hover:bg-[#F9FBF9] transition-colors">
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs" style="background:#245c4a">
                      {{ member.name.charAt(0).toUpperCase() }}
                    </div>
                    <span class="text-sm font-medium text-foreground">{{ member.name }}</span>
                  </div>
                 </td>
                <td class="px-5 py-4">
                  <div class="text-xs font-semibold text-slate-700">{{ member.login_id }}</div>
                  <div class="text-xs text-muted-foreground">{{ member.email }}</div>
                 </td>
                <td class="px-5 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="perm in member.permissions" :key="perm.id" class="text-[10px] px-1.5 py-0.5 rounded border bg-emerald-50 text-emerald-700 border-emerald-100">
                      {{ getPermissionLabel(perm.name) }}
                    </span>
                    <span v-if="!member.permissions || member.permissions.length === 0" class="text-xs text-muted-foreground italic">No specific permissions</span>
                  </div>
                 </td>
                <td class="px-5 py-4 text-right">
                  <div v-if="!hasBaseRole(member)" class="inline-flex items-center gap-2">
                    <button @click="openPermissionsModal(member)" class="text-xs font-semibold px-2 py-1 rounded hover:bg-emerald-50" style="color:#245c4a">
                      Permissions
                    </button>
                    <button @click="deleteStaff(member.id)" class="text-xs font-semibold px-2 py-1 rounded hover:bg-rose-50 text-rose-600">
                      Remove
                    </button>
                  </div>
                  <span v-else class="text-xs font-bold text-[#C5A059] uppercase tracking-widest mr-2">Owner</span>
                 </td>
              </tr>
              <tr v-if="!staff || staff.length === 0">
                <td colspan="4" class="px-5 py-10 text-center text-muted-foreground italic text-sm">
                  No staff accounts found. Click '+ Add Staff' to create one.
                 </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Card list -->
        <div class="sm:hidden divide-y divide-border">
          <div v-for="member in staff" :key="member.id" class="p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0" style="background:#245c4a">
                  {{ member.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="text-sm font-semibold text-foreground">{{ member.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ member.login_id }}</p>
                  <p class="text-xs text-muted-foreground">{{ member.email }}</p>
                </div>
              </div>
              <span v-if="hasBaseRole(member)" class="text-[10px] font-bold text-[#C5A059] uppercase tracking-widest shrink-0 mt-1">Owner</span>
            </div>
            <div class="flex flex-wrap gap-1 mb-3">
              <span v-for="perm in member.permissions" :key="perm.id" class="text-[10px] px-1.5 py-0.5 rounded border bg-emerald-50 text-emerald-700 border-emerald-100">
                {{ getPermissionLabel(perm.name) }}
              </span>
              <span v-if="!member.permissions || member.permissions.length === 0" class="text-xs text-muted-foreground italic">No permissions set</span>
            </div>
            <div v-if="!hasBaseRole(member)" class="flex gap-2">
              <button @click="openPermissionsModal(member)" class="flex-1 text-xs font-semibold px-3 py-2 rounded-lg border border-emerald-200 hover:bg-emerald-50 transition-colors" style="color:#245c4a">
                Edit Permissions
              </button>
              <button @click="deleteStaff(member.id)" class="flex-1 text-xs font-semibold px-3 py-2 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 transition-colors">
                Remove
              </button>
            </div>
          </div>
          <div v-if="!staff || staff.length === 0" class="p-8 text-center text-muted-foreground italic text-sm">
            No staff accounts found. Click '+ Add Staff' to create one.
          </div>
        </div>
      </div>

      <!-- Add Staff Modal -->
      <Teleport to="body">
        <template v-if="showAddModal">
          <div class="fixed inset-0 z-50" style="background:rgba(0,0,0,0.5);backdrop-filter:blur(3px)" @click="showAddModal = false"></div>
          <div class="fixed inset-0 z-50 flex items-start justify-center px-4 sm:px-6 pt-12 sm:pt-16 pb-6 overflow-y-auto">
            <div class="relative w-full max-w-[calc(100vw-2rem)] sm:max-w-md mx-auto bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">
              <div class="px-5 py-4 border-b border-border flex items-start justify-between flex-shrink-0" style="background:#245c4a">
                <h3 class="text-sm font-semibold text-white">New Staff Account</h3>
                <button @click="showAddModal = false" class="ml-4 flex-shrink-0 w-7 h-7 rounded-xl flex items-center justify-center transition-colors hover:bg-white/25 text-white leading-none text-base">✕</button>
              </div>

              <div class="overflow-y-auto flex-1 px-5 py-5">
                <form id="staff-form" @submit.prevent="submitAdd" class="flex flex-col gap-4">
                  <!-- Full Name -->
                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Full Name <span style="color:#9f1239">*</span></label>
                    <input
                      v-model="addForm.name"
                      type="text"
                      placeholder="e.g. Maria Santos"
                      class="w-full px-3 py-2 rounded-xl border border-border bg-white text-sm focus:outline-none focus:ring-2 transition-colors"
                      style="--tw-ring-color: rgba(36,92,74,.35); color: #111827;"
                      required
                    />
                    <div v-if="addForm.errors.name" class="text-xs text-rose-500 mt-1">{{ addForm.errors.name }}</div>
                  </div>

                  <!-- Login ID -->
                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Login ID <span style="color:#9f1239">*</span></label>
                    <input
                      v-model="addForm.login_id"
                      @input="handleLoginIdInput"
                      type="text"
                      placeholder="Unique login identifier"
                      class="w-full px-3 py-2 rounded-xl border border-border bg-white text-sm focus:outline-none focus:ring-2 transition-colors"
                      style="--tw-ring-color: rgba(36,92,74,.35); color: #111827;"
                      required
                    />
                    <div v-if="addForm.errors.login_id" class="text-xs text-rose-500 mt-1">{{ addForm.errors.login_id }}</div>
                  </div>

                  <!-- Email Address -->
                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">Email Address <span style="color:#9f1239">*</span></label>
                    <input
                      v-model="addForm.email"
                      type="email"
                      inputmode="email"
                      placeholder="staff@yourstore.com"
                      class="w-full px-3 py-2 rounded-xl border border-border bg-white text-sm focus:outline-none focus:ring-2 transition-colors"
                      style="--tw-ring-color: rgba(36,92,74,.35); color: #111827;"
                      required
                    />
                    <div v-if="addForm.errors.email" class="text-xs text-rose-500 mt-1">{{ addForm.errors.email }}</div>
                  </div>

                  <!-- Password Field with Requirements -->
                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                      Password <span style="color:#9f1239">*</span>
                    </label>
                    <div class="relative">
                      <input
                        v-model="addForm.password"
                        type="password"
                        placeholder="••••••••"
                        @focus="showPasswordRequirements = true"
                        @blur="showPasswordRequirements = false"
                        class="w-full px-3 py-2 rounded-xl border border-border bg-white text-sm focus:outline-none focus:ring-2 transition-colors"
                        :class="{
                          'border-red-300': addForm.password && !isPasswordValid,
                          'border-green-300': addForm.password && isPasswordValid
                        }"
                        style="--tw-ring-color: rgba(36,92,74,.35); color: #111827;"
                        required
                      />
                      <!-- Password strength indicator -->
                      <div v-if="addForm.password" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg v-if="isPasswordValid" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </div>
                    </div>
                    
                    <!-- Password Requirements Popup -->
                    <div v-if="showPasswordRequirements && addForm.password" 
                         class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs">
                      <p class="font-semibold text-gray-700 mb-2">Password must contain:</p>
                      <ul class="space-y-1">
                        <li v-for="error in passwordErrors" :key="error" class="text-red-600 flex items-center gap-2">
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                          {{ error }}
                        </li>
                        <li v-if="passwordErrors.length === 0 && addForm.password" class="text-green-600 flex items-center gap-2">
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          Password meets all requirements
                        </li>
                      </ul>
                    </div>
                    <div v-if="addForm.errors.password" class="text-xs text-rose-500 mt-1">{{ addForm.errors.password }}</div>
                  </div>

                  <!-- Confirm Password -->
                  <div>
                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1.5">
                      Confirm Password <span style="color:#9f1239">*</span>
                    </label>
                    <input
                      v-model="addForm.password_confirmation"
                      type="password"
                      placeholder="Confirm password"
                      class="w-full px-3 py-2 rounded-xl border border-border bg-white text-sm focus:outline-none focus:ring-2 transition-colors"
                      :class="{
                        'border-red-300': addForm.password_confirmation && !doPasswordsMatch,
                        'border-green-300': addForm.password_confirmation && doPasswordsMatch
                      }"
                      style="--tw-ring-color: rgba(36,92,74,.35); color: #111827;"
                      required
                    />
                    <div v-if="addForm.password_confirmation && !doPasswordsMatch" class="text-xs text-rose-500 mt-1">
                      Passwords do not match
                    </div>
                  </div>

                  <!-- Password Requirements Summary -->
                  <div v-if="addForm.password && !isPasswordValid" class="text-xs text-amber-600 bg-amber-50 p-2 rounded-lg">
                    <span class="font-semibold">⚠️ Password does not meet requirements</span>
                    <ul class="mt-1 ml-4 list-disc">
                      <li v-for="error in passwordErrors" :key="error">{{ error }}</li>
                    </ul>
                  </div>
                </form>
              </div>

              <div class="px-5 py-4 border-t border-border flex items-center justify-end gap-2 flex-shrink-0 bg-white rounded-b-2xl">
                <button type="button" @click="showAddModal = false" class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl border border-border bg-white hover:bg-[#F9FBF9] transition-colors">
                  Cancel
                </button>
                <button type="submit" form="staff-form" :disabled="isSubmitDisabled" class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl text-white transition-opacity hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed" style="background:#245c4a">
                  <svg v-if="addForm.processing" class="animate-spin -ml-0.5 mr-1.5 w-3 h-3 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ addForm.processing ? 'Creating...' : 'Create Staff Account' }}
                </button>
              </div>
            </div>
          </div>
        </template>
      </Teleport>

      <!-- Permissions Modal -->
      <Teleport to="body">
        <div v-if="showPermissionsModal" class="fixed inset-0 z-50 flex items-start justify-center px-4 sm:px-6 pt-12 sm:pt-16 pb-6 overflow-y-auto" style="background:rgba(0,0,0,0.45);backdrop-filter:blur(2px)">
          <div class="absolute inset-0" @click="showPermissionsModal = false"></div>
          <div class="relative w-full max-w-[calc(100vw-2rem)] sm:max-w-lg mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-5 py-4 border-b border-emerald-50 flex justify-between items-center shrink-0" style="background:#245c4a">
              <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-widest">Access Controls</h3>
                <p class="text-[10px] text-emerald-100 font-medium uppercase tracking-wider mt-0.5">{{ selectedStaff?.name }}</p>
              </div>
              <button @click="showPermissionsModal = false" class="text-emerald-100 hover:text-white transition-colors text-lg leading-none">✕</button>
            </div>
            <form @submit.prevent="submitPermissions" class="flex flex-col flex-1 overflow-hidden">
              <div class="overflow-y-auto flex-1 px-5 py-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pr-1">
                  <div v-for="permission in availablePermissions" :key="permission.id"
                       @click="permissionsForm.permissions.includes(permission.name) ? permissionsForm.permissions = permissionsForm.permissions.filter(p => p !== permission.name) : permissionsForm.permissions.push(permission.name)"
                       class="flex items-start gap-3 p-3 rounded-xl border border-emerald-50 hover:border-[#245c4a] hover:bg-emerald-50/50 transition-all cursor-pointer group"
                       :class="permissionsForm.permissions.includes(permission.name) ? 'bg-emerald-50/80 border-[#245c4a]' : 'bg-white'">
                    <div class="mt-0.5 relative flex items-center">
                      <input type="checkbox" :id="'perm-'+permission.id" :value="permission.name" v-model="permissionsForm.permissions" class="h-4 w-4 rounded border-emerald-200 text-[#245c4a] focus:ring-[#245c4a]/20" @click.stop />
                    </div>
                    <label :for="'perm-'+permission.id" class="text-xs font-bold text-slate-700 cursor-pointer flex-1 group-hover:text-[#245c4a] transition-colors" @click.stop>
                      {{ getPermissionLabel(permission.name) }}
                      <p class="text-[10px] text-slate-500 font-normal mt-0.5 leading-relaxed">{{ getPermissionDescription(permission.name) }}</p>
                    </label>
                  </div>
                </div>
              </div>
              <div class="px-5 py-4 border-t border-border flex items-center justify-end gap-2 flex-shrink-0 bg-white rounded-b-2xl">
                <button type="button" @click="showPermissionsModal = false" class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl border border-border bg-white hover:bg-[#F9FBF9] transition-colors">Cancel</button>
                <button type="submit" class="inline-flex items-center justify-center text-xs font-semibold px-4 py-2 rounded-xl text-white transition-opacity hover:opacity-90 disabled:opacity-50" style="background:#245c4a" :disabled="permissionsForm.processing">
                  <svg v-if="permissionsForm.processing" class="animate-spin -ml-0.5 mr-1.5 w-3 h-3 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ permissionsForm.processing ? 'Saving...' : 'Update Permissions' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </div>
  </VendorLayout>
</template>