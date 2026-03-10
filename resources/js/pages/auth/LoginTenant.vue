<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'

defineProps<{ status?: string }>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const isSubmitting = ref(false)

function submit() {
  if (!isSubmitting.value) {
    isSubmitting.value = true
    window.history.replaceState(
      { isLogin: true },
      document.title,
      window.location.href
    )
  }
  
  form.post('/login', {
    preserveScroll: true,
    onFinish: () => {
      form.reset('password')
      isSubmitting.value = false
    },
  })
}

onMounted(() => {
  const formEl = document.querySelector('.it-form') as HTMLFormElement
  if (formEl) {
    formEl.addEventListener('submit', () => {
      if (!isSubmitting.value) {
        isSubmitting.value = true
      }
    })
  }
})
</script>

<template>
  <Head title="Store Login — iTinda" />

  <div class="it-tenant-login-root">
    
    <!-- ── LEFT PANEL (store-focused branding) ─────── -->
    <aside class="it-store-panel">
      <div class="it-panel-grid"></div>
      <div class="it-panel-glow"></div>

      <div class="it-panel-inner">
        
        <!-- Store Logo -->
        <div class="it-store-logo">
          <div class="it-store-logo-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="it-store-info">
            <span class="it-store-name">Your Store</span>
            <span class="it-store-subtitle">Management Portal</span>
          </div>
        </div>

        <!-- Store-focused copy -->
        <div class="it-panel-copy">
          <div class="it-panel-eyebrow">Store Management</div>
          <h2 class="it-panel-heading">
            Your business,<br />
            <span class="it-panel-accent">your control.</span>
          </h2>
          <p class="it-panel-sub">
            Manage your inventory, track orders, connect with customers, 
            and grow your business with our comprehensive store management tools.
          </p>
        </div>

        <!-- Store features highlight -->
        <div class="it-feature-card">
          <div class="it-feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <div class="it-feature-content">
            <h3 class="it-feature-title">Complete Store Analytics</h3>
            <p class="it-feature-desc">
              Track sales, monitor inventory, and understand your customers with detailed insights.
            </p>
          </div>
        </div>

        <!-- Store stats -->
        <div class="it-panel-stats">
          <div class="it-ps-item">
            <span class="it-ps-num">24/7</span>
            <span class="it-ps-label">Store Access</span>
          </div>
          <div class="it-ps-div"></div>
          <div class="it-ps-item">
            <span class="it-ps-num">Real-time</span>
            <span class="it-ps-label">Updates</span>
          </div>
        </div>

      </div>
    </aside>

    <!-- ── RIGHT PANEL (form) ───────────────────────── -->
    <main class="it-form-panel">
      <div class="it-form-inner">

        <!-- Mobile store logo -->
        <div class="it-mobile-store-logo">
          <div class="it-mobile-store-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div>
            <span class="it-mobile-store-name">Store Portal</span>
            <span class="it-mobile-store-sub">iTinda</span>
          </div>
        </div>

        <!-- Form header -->
        <div class="it-form-header">
          <h1 class="it-form-title">Welcome to your store</h1>
          <p class="it-form-desc">Sign in to manage your business and connect with customers.</p>
        </div>

        <!-- Status message -->
        <div v-if="status" class="it-status-msg">
          {{ status }}
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="it-form">
          <!-- Login ID -->
          <div class="it-field">
            <Label for="email" class="it-label">Login ID or Email</Label>
            <div class="it-input-wrap">
              <span class="it-input-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </span>
              <Input
                id="email"
                v-model="form.email"
                type="text"
                required
                autofocus
                :tabindex="1"
                autocomplete="username"
                placeholder="Enter your login ID or email"
                class="it-input"
              />
            </div>
            <InputError :message="form.errors.email" class="it-field-error" />
          </div>

          <!-- Password -->
          <div class="it-field">
            <Label for="password" class="it-label">Password</Label>
            <div class="it-input-wrap">
              <span class="it-input-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </span>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                required
                :tabindex="2"
                autocomplete="current-password"
                placeholder="Enter your password"
                class="it-input"
              />
            </div>
            <InputError :message="form.errors.password" class="it-field-error" />
          </div>

          <!-- Remember me -->
          <div class="it-remember-row">
            <Label for="remember" class="it-remember-label">
              <Checkbox 
                id="remember" 
                v-model:checked="form.remember" 
                :tabindex="3" 
                class="it-checkbox" 
              />
              <span>Keep me signed in</span>
            </Label>
          </div>

          <!-- Submit -->
          <Button
            type="submit"
            :tabindex="4"
            :disabled="form.processing"
            class="it-submit-btn"
          >
            <Spinner v-if="form.processing" class="it-spinner" />
            <span>{{ form.processing ? 'Signing in…' : 'Access Store Dashboard' }}</span>
          </Button>
        </form>

        <p class="it-form-footer">
          &copy; {{ new Date().getFullYear() }} iTinda Store Management. Secure access to your business.
        </p>
      </div>
    </main>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Lato:wght@300;400;700&display=swap');

/* ── Variables (Store-focused green theme) ─────────────────────── */
.it-tenant-login-root {
    --store-primary:   #1B4D3E;  /* Emerald green */
    --store-secondary: #245c4a;  /* Lighter emerald */
    --store-accent:    #C5A059;  /* Gold accent */
    --store-accent-light: #d9b87a;
    --store-accent-pale:  #f5ead4;
    --smoke:           #F5F5F5;
    --white:           #ffffff;
    --text-dark:       #0f2820;
    --text-body:       #3a4a44;
    --text-muted:      #7a8f88;
    --border:          rgba(27, 77, 62, 0.14);

    display: flex;
    min-height: 100vh;
    font-family: 'Lato', sans-serif;
    background: var(--smoke);
}

.it-tenant-login-root * { box-sizing: border-box; margin: 0; padding: 0; }
.it-tenant-login-root a { text-decoration: none; color: inherit; }

/* ════════════════════════════════════════════════════════════
   LEFT — STORE PANEL
   ════════════════════════════════════════════════════════════ */
.it-store-panel {
    width: 44%;
    flex-shrink: 0;
    background: var(--store-primary);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: stretch;
}

.it-panel-grid {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(197,160,89,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(197,160,89,0.05) 1px, transparent 1px);
    background-size: 52px 52px;
    pointer-events: none;
}

.it-panel-glow {
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 70% 50% at 80% 15%, rgba(197,160,89,0.13) 0%, transparent 60%),
        radial-gradient(ellipse 50% 60% at 10% 88%, rgba(197,160,89,0.07) 0%, transparent 55%);
    pointer-events: none;
}

.it-panel-inner {
    position: relative; z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 48px 52px;
    width: 100%;
    animation: itFadeUp 0.6s ease both;
}

/* Store Logo */
.it-store-logo {
    display: flex;
    align-items: center;
    gap: 14px;
}

.it-store-logo-icon {
    width: 42px; height: 42px;
    background: var(--store-accent);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.it-store-logo-icon svg { 
    width: 22px; height: 22px; 
    color: var(--store-primary); 
}

.it-store-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.it-store-name {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.3rem;
    color: var(--white);
}

.it-store-subtitle {
    font-family: 'Lato', sans-serif;
    font-weight: 600;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.65);
    letter-spacing: 0.5px;
}

/* Panel copy */
.it-panel-copy { 
    margin: auto 0; 
    padding: 40px 0 32px; 
}

.it-panel-eyebrow {
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.72rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(197, 160, 89, 0.7);
    margin-bottom: 14px;
}

.it-panel-heading {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: clamp(1.65rem, 2.4vw, 2.2rem);
    line-height: 1.22;
    color: var(--white);
    margin-bottom: 16px;
}

.it-panel-accent { color: var(--store-accent); }

.it-panel-sub {
    font-size: 0.93rem;
    line-height: 1.72;
    color: rgba(255, 255, 255, 0.58);
    max-width: 320px;
}

/* ── Feature card ──────────────────────────────────────────── */
.it-feature-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(197, 160, 89, 0.2);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 28px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.it-feature-icon {
    width: 40px; height: 40px;
    background: rgba(197, 160, 89, 0.22);
    border: 1px solid rgba(197, 160, 89, 0.35);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.it-feature-icon svg { 
    width: 18px; height: 18px; 
    color: var(--store-accent); 
}

.it-feature-content {
    flex: 1;
}

.it-feature-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--white);
    margin-bottom: 6px;
}

.it-feature-desc {
    font-size: 0.85rem;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.65);
}

/* ── Stats strip ───────────────────────────────────────────── */
.it-panel-stats {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 18px 22px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(197, 160, 89, 0.15);
    border-radius: 12px;
}

.it-ps-item { display: flex; flex-direction: column; gap: 2px; }

.it-ps-num {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.1rem;
    color: var(--store-accent);
}

.it-ps-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.45);
}

.it-ps-div {
    width: 1px; height: 28px;
    background: rgba(255, 255, 255, 0.12);
}

/* ════════════════════════════════════════════════════════════
   RIGHT — FORM PANEL
   ════════════════════════════════════════════════════════════ */
.it-form-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 40px;
    background: var(--smoke);
}

.it-form-inner {
    width: 100%;
    max-width: 420px;
    display: flex;
    flex-direction: column;
    animation: itFadeUp 0.6s 0.12s ease both;
}

/* Mobile store logo */
.it-mobile-store-logo {
    display: none;
    align-items: center;
    gap: 12px;
    margin-bottom: 32px;
}

.it-mobile-store-icon {
    width: 36px; height: 36px;
    background: var(--store-primary);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.it-mobile-store-icon svg { 
    width: 18px; height: 18px; 
    color: var(--white); 
}

.it-mobile-store-name {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.2rem;
    color: var(--store-primary);
    display: block;
}

.it-mobile-store-sub {
    font-family: 'Lato', sans-serif;
    font-weight: 600;
    font-size: 0.75rem;
    color: var(--text-muted);
    display: block;
}

/* Form header */
.it-form-header { margin-bottom: 32px; }

.it-form-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.85rem;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.it-form-desc {
    font-size: 0.93rem;
    color: var(--text-muted);
    line-height: 1.5;
}

/* Status */
.it-status-msg {
    background: rgba(27, 77, 62, 0.07);
    border: 1px solid rgba(27, 77, 62, 0.18);
    color: var(--store-primary);
    font-size: 0.875rem;
    font-weight: 600;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}

/* Form */
.it-form { display: flex; flex-direction: column; gap: 20px; }

.it-field { display: flex; flex-direction: column; gap: 7px; }

.it-label {
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--text-dark);
}

/* Input with icon */
.it-input-wrap { position: relative; }

.it-input-icon {
    position: absolute;
    left: 13px; top: 50%;
    transform: translateY(-50%);
    display: flex; align-items: center; justify-content: center;
    pointer-events: none; z-index: 1;
}

.it-input-icon svg { width: 16px; height: 16px; color: var(--text-muted); }

.it-input-wrap :deep(input) {
    width: 100%;
    padding: 11px 14px 11px 40px;
    font-family: 'Lato', sans-serif;
    font-size: 0.93rem;
    color: var(--text-dark);
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 8px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    appearance: none;
}

.it-input-wrap :deep(input::placeholder) { 
    color: rgba(122, 143, 136, 0.55); 
}

.it-input-wrap :deep(input:focus) {
    border-color: var(--store-primary);
    box-shadow: 0 0 0 3px rgba(27, 77, 62, 0.1);
}

.it-field-error { 
    font-size: 0.8rem; 
    color: #e53e3e; 
    margin-top: 2px; 
}

/* Remember */
.it-remember-row { margin-top: -2px; }

.it-remember-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 0.88rem;
    color: var(--text-body);
    user-select: none;
}

.it-remember-label :deep([data-slot="checkbox"]),
.it-remember-label :deep([role="checkbox"]) {
    width: 18px; height: 18px;
    border: 2px solid var(--border);
    border-radius: 4px;
    background: var(--white);
    transition: border-color 0.2s, background 0.2s;
    flex-shrink: 0;
}

.it-remember-label :deep([data-slot="checkbox"][data-state="checked"]),
.it-remember-label :deep([role="checkbox"][aria-checked="true"]) {
    background: var(--store-primary);
    border-color: var(--store-primary);
}

/* Submit button */
.it-submit-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 0;
    background: var(--store-primary) !important;
    color: var(--white) !important;
    font-family: 'Montserrat', sans-serif !important;
    font-weight: 700 !important;
    font-size: 0.95rem !important;
    border-radius: 8px !important;
    border: none !important;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 18px rgba(27, 77, 62, 0.22);
    margin-top: 4px;
}

.it-submit-btn:hover:not(:disabled) {
    background: var(--store-secondary) !important;
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(27, 77, 62, 0.28);
}

.it-submit-btn:disabled { 
    opacity: 0.6; 
    cursor: not-allowed; 
}

.it-spinner { width: 16px; height: 16px; }

/* Footer */
.it-form-footer {
    text-align: center;
    font-size: 0.77rem;
    color: rgba(122, 143, 136, 0.5);
    margin-top: 44px;
}

/* ════════════════════════════════════════════════════════════
   KEYFRAMES
   ════════════════════════════════════════════════════════════ */
@keyframes itFadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE — tablet ≤ 900px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .it-store-panel { width: 40%; }
    .it-panel-inner { padding: 40px 32px; }
    .it-panel-sub   { display: none; }
    .it-feature-card { display: none; }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE — mobile ≤ 640px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    .it-tenant-login-root { flex-direction: column; }
    .it-store-panel { display: none; }
    .it-form-panel { 
        padding: 32px 24px 48px; 
        align-items: flex-start; 
    }
    .it-form-inner { max-width: 100%; }
    .it-mobile-store-logo { display: flex; }
    .it-form-title { font-size: 1.6rem; }
}
</style>