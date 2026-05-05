<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import {
    Shield,
    UserPlus,
    X,
    CheckCircle2,
    AlertCircle,
    UserCircle2,
    Mail as MailIcon,
    Fingerprint,
    KeyRound
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
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
import ConfirmationModal from '@/components/ui/modal/ConfirmationModal.vue';
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
const showDeleteModal = ref(false);
const selectedStaff = ref<Staff | null>(null);
const staffToDelete = ref<Staff | null>(null);
const showPasswordRequirements = ref(false);
const deleteProcessing = ref(false);

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

function deleteStaff(staff: Staff) {
  staffToDelete.value = staff;
  showDeleteModal.value = true;
}

function confirmDeleteStaff(reason?: string) {
  if (!staffToDelete.value || !reason) return;

  deleteProcessing.value = true;
  router.delete(`/vendor/staff/${staffToDelete.value.id}`, {
    data: { reason },
    onSuccess: () => {
      showDeleteModal.value = false;
      staffToDelete.value = null;
      deleteProcessing.value = false;
    },
    onError: () => {
      deleteProcessing.value = false;
    },
  });
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
              <tr v-for="member in staff" :key="member.id" class="border-b border-border last:border-0 transition-colors duration-200 hover:bg-[#f0f5f2] dark:hover:bg-[#1a2e42]">
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
                    <button @click="deleteStaff(member)" class="text-xs font-semibold px-2 py-1 rounded hover:bg-rose-50 text-rose-600">
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
              <button @click="deleteStaff(member)" class="flex-1 text-xs font-semibold px-3 py-2 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 transition-colors">
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
      <Dialog :open="showAddModal" @update:open="showAddModal = $event">
        <DialogContent class="sm:max-w-[480px] p-0 overflow-hidden border-none shadow-2xl rounded-3xl bg-white dark:bg-slate-900">
          <DialogHeader class="px-6 pt-8 pb-6 bg-gradient-to-br from-[#1B4D3E] to-[#245C4A] relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-[#C5A059]/20 rounded-full blur-xl"></div>
            
            <div class="relative z-10 flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20">
                <UserPlus class="w-6 h-6 text-white" />
              </div>
              <div>
                <DialogTitle class="text-xl font-bold text-white tracking-tight">New Staff Member</DialogTitle>
                <DialogDescription class="text-emerald-100/80 text-xs mt-0.5 font-medium uppercase tracking-wider">
                  Create a secure dashboard access
                </DialogDescription>
              </div>
            </div>
          </DialogHeader>

          <div class="px-6 py-6 space-y-5">
            <form id="staff-form" @submit.prevent="submitAdd" class="space-y-4">
              <!-- Full Name -->
              <div class="space-y-2">
                <Label for="staff-name" class="text-[11px] font-bold uppercase tracking-widest text-[#245C4A]/70 dark:text-emerald-400">Full Name</Label>
                <div class="relative">
                  <UserCircle2 class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <Input
                    id="staff-name"
                    v-model="addForm.name"
                    placeholder="e.g. Maria Santos"
                    class="pl-10 h-11 bg-slate-50 border-slate-200 focus:bg-white transition-all rounded-xl"
                    required
                  />
                </div>
                <div v-if="addForm.errors.name" class="text-[10px] font-semibold text-rose-500 mt-1 flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> {{ addForm.errors.name }}
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Login ID -->
                <div class="space-y-2">
                  <Label for="staff-login" class="text-[11px] font-bold uppercase tracking-widest text-[#245C4A]/70 dark:text-emerald-400">Login ID</Label>
                  <div class="relative">
                    <Fingerprint class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <Input
                      id="staff-login"
                      v-model="addForm.login_id"
                      @input="handleLoginIdInput"
                      placeholder="Login ID"
                      class="pl-10 h-11 bg-slate-50 border-slate-200 focus:bg-white transition-all rounded-xl"
                      required
                    />
                  </div>
                  <div v-if="addForm.errors.login_id" class="text-[10px] font-semibold text-rose-500 mt-1 flex items-center gap-1">
                    <AlertCircle class="w-3 h-3" /> {{ addForm.errors.login_id }}
                  </div>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                  <Label for="staff-email" class="text-[11px] font-bold uppercase tracking-widest text-[#245C4A]/70 dark:text-emerald-400">Email Address</Label>
                  <div class="relative">
                    <MailIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <Input
                      id="staff-email"
                      v-model="addForm.email"
                      type="email"
                      placeholder="staff@store.com"
                      class="pl-10 h-11 bg-slate-50 border-slate-200 focus:bg-white transition-all rounded-xl"
                      required
                    />
                  </div>
                  <div v-if="addForm.errors.email" class="text-[10px] font-semibold text-rose-500 mt-1 flex items-center gap-1">
                    <AlertCircle class="w-3 h-3" /> {{ addForm.errors.email }}
                  </div>
                </div>
              </div>

              <!-- Password -->
              <div class="space-y-2">
                <Label for="staff-password" class="text-[11px] font-bold uppercase tracking-widest text-[#245C4A]/70 dark:text-emerald-400">Security Password</Label>
                <div class="relative">
                  <KeyRound class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <Input
                    id="staff-password"
                    v-model="addForm.password"
                    type="password"
                    placeholder="••••••••"
                    @focus="showPasswordRequirements = true"
                    @blur="showPasswordRequirements = false"
                    class="pl-10 h-11 bg-slate-50 border-slate-200 focus:bg-white transition-all rounded-xl"
                    :class="{ 'border-emerald-500 ring-emerald-500/20': isPasswordValid && addForm.password }"
                    required
                  />
                  <div v-if="addForm.password" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <CheckCircle2 v-if="isPasswordValid" class="w-4 h-4 text-emerald-500" />
                    <AlertCircle v-else class="w-4 h-4 text-amber-500" />
                  </div>
                </div>

                <!-- Password Requirements Summary -->
                <div v-if="addForm.password && !isPasswordValid" class="p-3 bg-amber-50 dark:bg-amber-950/20 rounded-xl border border-amber-100 dark:border-amber-900/30">
                  <p class="text-[10px] font-bold text-amber-800 dark:text-amber-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <AlertCircle class="w-3 h-3" /> Requirements
                  </p>
                  <div class="flex flex-wrap gap-x-3 gap-y-1.5">
                    <span v-for="error in passwordErrors" :key="error" class="text-[10px] font-medium text-amber-700/80 dark:text-amber-500 flex items-center gap-1">
                      <div class="w-1 h-1 rounded-full bg-amber-400"></div> {{ error }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Confirm Password -->
              <div class="space-y-2">
                <Label for="staff-confirm" class="text-[11px] font-bold uppercase tracking-widest text-[#245C4A]/70 dark:text-emerald-400">Confirm Security Password</Label>
                <Input
                  id="staff-confirm"
                  v-model="addForm.password_confirmation"
                  type="password"
                  placeholder="Re-enter password"
                  class="h-11 bg-slate-50 border-slate-200 focus:bg-white transition-all rounded-xl"
                  :class="{ 'border-rose-500 bg-rose-50': addForm.password_confirmation && !doPasswordsMatch }"
                  required
                />
                <div v-if="addForm.password_confirmation && !doPasswordsMatch" class="text-[10px] font-semibold text-rose-500 mt-1 flex items-center gap-1">
                  <AlertCircle class="w-3 h-3" /> Passwords do not match
                </div>
              </div>
            </form>
          </div>

          <DialogFooter class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex flex-row items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="showAddModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-800 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              form="staff-form"
              :disabled="isSubmitDisabled"
              class="h-11 px-6 rounded-xl bg-[#245C4A] text-white text-xs font-bold uppercase tracking-widest shadow-lg shadow-emerald-900/20 hover:shadow-emerald-900/30 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:hover:scale-100 disabled:pointer-events-none flex items-center gap-2"
            >
              <template v-if="addForm.processing">
                <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Creating...
              </template>
              <template v-else>
                Create Account
              </template>
            </button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Permissions Modal -->
      <Dialog :open="showPermissionsModal" @update:open="showPermissionsModal = $event">
        <DialogContent class="sm:max-w-[560px] p-0 overflow-hidden border-none shadow-2xl rounded-3xl bg-white dark:bg-slate-900">
          <DialogHeader class="px-6 pt-8 pb-6 bg-gradient-to-br from-[#1B4D3E] to-[#245C4A] relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20">
                <Shield class="w-6 h-6 text-white" />
              </div>
              <div>
                <DialogTitle class="text-xl font-bold text-white tracking-tight">Access Controls</DialogTitle>
                <DialogDescription class="text-emerald-100/80 text-xs mt-0.5 font-medium uppercase tracking-wider">
                  Managing access for {{ selectedStaff?.name }}
                </DialogDescription>
              </div>
            </div>
          </DialogHeader>

          <div class="px-6 py-6">
            <form @submit.prevent="submitPermissions" class="space-y-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pr-1 max-h-[40vh] overflow-y-auto custom-scrollbar">
                <div
                  v-for="permission in availablePermissions"
                  :key="permission.id"
                  @click="!permissionsForm.processing && (permissionsForm.permissions.includes(permission.name) ? permissionsForm.permissions = permissionsForm.permissions.filter(p => p !== permission.name) : permissionsForm.permissions.push(permission.name))"
                  class="group relative flex flex-col p-4 rounded-2xl border-2 transition-all select-none"
                  :class="[
                    permissionsForm.processing ? 'cursor-not-allowed opacity-60' : 'cursor-pointer',
                    permissionsForm.permissions.includes(permission.name)
                      ? 'bg-emerald-50/50 border-[#245C4A] dark:bg-emerald-500/5 dark:border-emerald-500'
                      : 'bg-slate-50/50 border-slate-100 hover:border-slate-200 dark:bg-slate-800/50 dark:border-slate-800'
                  ]"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-widest" :class="permissionsForm.permissions.includes(permission.name) ? 'text-[#245C4A] dark:text-emerald-400' : 'text-slate-500'">
                      {{ getPermissionLabel(permission.name) }}
                    </span>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                      :class="permissionsForm.permissions.includes(permission.name)
                        ? 'bg-[#245C4A] border-[#245C4A] dark:bg-emerald-500 dark:border-emerald-500'
                        : 'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-700'">
                      <CheckCircle2 v-if="permissionsForm.permissions.includes(permission.name)" class="w-3.5 h-3.5 text-white" />
                    </div>
                  </div>
                  <p class="text-[10px] leading-relaxed text-slate-500 dark:text-slate-400 pr-2">
                    {{ getPermissionDescription(permission.name) }}
                  </p>
                </div>
              </div>

              <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 flex items-start gap-3">
                <AlertCircle class="w-4 h-4 text-[#C5A059] shrink-0 mt-0.5" />
                <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                  Permissions granted here will take effect immediately upon the staff member's next page load or action.
                </p>
              </div>
            </form>
          </div>

          <DialogFooter class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex flex-row items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="showPermissionsModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-800 transition-colors"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="submitPermissions"
              :disabled="permissionsForm.processing"
              class="h-11 px-6 rounded-xl bg-[#245C4A] text-white text-xs font-bold uppercase tracking-widest shadow-lg shadow-emerald-900/20 hover:shadow-emerald-900/30 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:hover:scale-100 disabled:pointer-events-none flex items-center gap-2"
            >
              <template v-if="permissionsForm.processing">
                <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Updating...
              </template>
              <template v-else>
                Save Changes
              </template>
            </button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>

    <ConfirmationModal
      v-model:open="showDeleteModal"
      title="Deactivate Staff Account"
      description="Are you sure you want to deactivate this staff account?"
      :details="`This will remove ${staffToDelete?.name}'s access to the vendor dashboard and move their account to inactive status. They will no longer be able to log in or perform any actions.`"
      confirm-text="Deactivate Account"
      cancel-text="Cancel"
      variant="destructive"
      :loading="deleteProcessing"
      :show-reason-input="true"
      reason-label="Reason for deactivation"
      reason-placeholder="Please provide a reason for deactivating this staff account..."
      @confirm="confirmDeleteStaff"
      @cancel="staffToDelete = null"
    />
  </VendorLayout>
</template>