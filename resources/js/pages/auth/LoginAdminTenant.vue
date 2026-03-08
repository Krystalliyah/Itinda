<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthBase from '@/layouts/AuthLayout.vue'

defineProps<{ status?: string }>()

const form = useForm({
  login_id: '',
  password: '',
  remember: false,
})

function submit() {
  form.post('/login', {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Tenant Admin Login" />

  <AuthBase>
    <div class="it-form-header">
      <h1 class="it-form-title">Store Administrator Login</h1>
      <p class="it-form-desc">Use your admin credentials to enter the vendor dashboard.</p>
    </div>

    <div v-if="status" class="it-status-msg">{{ status }}</div>

    <form @submit.prevent="submit" class="it-form">
      <div class="it-field">
        <Label for="login_id" class="it-label">Login ID</Label>
        <Input id="login_id" v-model="form.login_id" type="text" required autofocus />
        <InputError :message="form.errors.login_id" class="it-field-error" />
      </div>

      <div class="it-field">
        <Label for="password" class="it-label">Password</Label>
        <Input id="password" v-model="form.password" type="password" required />
        <InputError :message="form.errors.password" class="it-field-error" />
      </div>

      <div class="it-field">
        <button type="submit" class="it-btn" :disabled="form.processing">
          {{ form.processing ? 'Logging in…' : 'Log in' }}
        </button>
      </div>
    </form>
  </AuthBase>
</template>