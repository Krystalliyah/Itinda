<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
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

function submit() {
  form.post('/login', {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Store Login — iTinda" />

  <div class="it-root">

    <!-- ── LEFT PANEL ───────────────────────────────────── -->
    <aside class="it-left">
      <div class="it-dots"></div>
      <div class="it-orb it-orb-1"></div>
      <div class="it-orb it-orb-2"></div>
      <div class="it-orb it-orb-3"></div>

      <div class="it-inner">

        <!-- Brand -->
        <div class="it-brand">
          <div class="it-brand-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="it-brand-text">
            <span class="it-brand-name">iTinda</span>
            <span class="it-brand-sub">Store Management</span>
          </div>
        </div>

        <!-- Copy -->
        <div class="it-copy">
          <div class="it-eyebrow">
            <span class="it-eyebrow-pip"></span>
            Store Portal
          </div>
          <h2 class="it-heading">
            Your business,<br>
            <span class="it-heading-gold">your control.</span>
          </h2>
          <p class="it-sub">
            Manage inventory, track orders, and connect
            with customers — all from one place.
          </p>
        </div>

        <!-- Pills -->
        <div class="it-pills">
          <div class="it-pill">
            <span class="it-pill-dot"></span>Real-time updates
          </div>
          <div class="it-pill">
            <span class="it-pill-dot"></span>24/7 access
          </div>
        </div>

        <!-- Bottom card -->
        <div class="it-feat">
          <div class="it-feat-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <div>
            <div class="it-feat-title">Complete store analytics</div>
            <div class="it-feat-desc">Sales, inventory, and customer insights in one dashboard.</div>
          </div>
        </div>

      </div>
    </aside>

    <!-- ── RIGHT PANEL ──────────────────────────────────── -->
    <main class="it-right">

      <!-- Decorative circles (white panel) -->
      <div class="it-deco-ring it-deco-ring-1"></div>
      <div class="it-deco-ring it-deco-ring-2"></div>
      <div class="it-deco-ring it-deco-ring-3"></div>

      <div class="it-form-wrap">

        <!-- Mobile brand -->
        <div class="it-mobile-brand">
          <div class="it-brand-icon it-brand-icon--dark">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div>
            <span class="it-mobile-name">iTinda</span>
            <span class="it-mobile-sub">Store Management</span>
          </div>
        </div>

        <!-- Header -->
        <div class="it-fhead">
          <h1 class="it-ftitle">Welcome back</h1>
          <p class="it-fdesc">Sign in to manage your store and connect with your customers.</p>
        </div>

        <!-- Status -->
        <div v-if="status" class="it-status">{{ status }}</div>

        <!-- Divider -->
        <div class="it-divider">
          <span class="it-divider-line"></span>
          <span class="it-divider-text">Store credentials</span>
          <span class="it-divider-line"></span>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="it-form">

          <div class="it-field">
            <Label for="email" class="it-label">Login ID or email</Label>
            <div class="it-iw">
              <span class="it-iico">
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
            <InputError :message="form.errors.email" class="it-err" />
          </div>

          <div class="it-field">
            <Label for="password" class="it-label">Password</Label>
            <div class="it-iw">
              <span class="it-iico">
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
            <InputError :message="form.errors.password" class="it-err" />
          </div>

          <div class="it-remember">
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

          <Button
            type="submit"
            :tabindex="4"
            :disabled="form.processing"
            class="it-btn"
          >
            <Spinner v-if="form.processing" class="it-spinner" />
            <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" class="it-btn-ico">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>{{ form.processing ? 'Signing in...' : 'Access Store ' }}</span>
          </Button>

        </form>

        <!-- Admin note -->
        <div class="it-note">
          <svg class="it-note-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="it-note-text">
            <strong>No account yet?</strong> Store accounts are set up by your administrator.
            Contact your admin or vendor manager for access.
          </p>
        </div>

        <p class="it-footer">&copy; {{ new Date().getFullYear() }} iTinda Store Management.</p>

      </div>
    </main>

  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=DM+Sans:wght@400;500;600&display=swap');

/* ── Variables ───────────────────────────────────────────── */
.it-root {
  --g:        #1B4D3E;
  --g2:       #163d32;
  --gold:     #C5A059;
  --gold-bg:  rgba(197,160,89,0.15);
  --gold-bd:  rgba(197,160,89,0.25);
  --smoke:    #F7F6F3;
  --white:    #ffffff;
  --dark:     #0f2820;
  --body:     #3d524b;
  --muted:    #8a9e97;
  --border:   rgba(27,77,62,0.13);

  display: flex;
  min-height: 100vh;
  font-family: 'DM Sans', sans-serif;
  background: var(--smoke);
}
.it-root * { box-sizing: border-box; margin: 0; padding: 0; }

/* ── LEFT ────────────────────────────────────────────────── */
.it-left {
  width: 42%;
  flex-shrink: 0;
  background: var(--g);
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: stretch;
}

.it-dots {
  position: absolute; inset: 0;
  background-image: radial-gradient(rgba(197,160,89,0.12) 1.2px, transparent 1.2px);
  background-size: 26px 26px;
  pointer-events: none;
}

.it-orb {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
}
.it-orb-1 { width: 380px; height: 380px; background: rgba(197,160,89,0.07); top: -110px; right: -120px; }
.it-orb-2 { width: 260px; height: 260px; background: rgba(197,160,89,0.05); bottom: 20px; left: -90px; }
.it-orb-3 { width: 140px; height: 140px; background: rgba(255,255,255,0.03); top: 48%; right: 10px; }

.it-inner {
  position: relative; z-index: 2;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 48px 48px;
  width: 100%;
  animation: itUp 0.55s ease both;
}

/* Brand */
.it-brand { display: flex; align-items: center; gap: 13px; }

.it-brand-icon {
  width: 44px; height: 44px;
  background: var(--gold);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.it-brand-icon svg { width: 20px; height: 20px; color: var(--g); }

.it-brand-icon--dark { background: var(--g) !important; }
.it-brand-icon--dark svg { color: var(--white) !important; }

.it-brand-text { display: flex; flex-direction: column; gap: 1px; }
.it-brand-name { font-family: 'Nunito', sans-serif; font-weight: 900; font-size: 1.25rem; color: var(--white); }
.it-brand-sub  { font-size: 0.72rem; font-weight: 600; color: rgba(255,255,255,0.48); letter-spacing: 0.4px; }

/* Copy */
.it-copy { padding: 36px 0 28px; }

.it-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 0.7rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
  color: rgba(197,160,89,0.75); margin-bottom: 18px;
}
.it-eyebrow-pip { width: 6px; height: 6px; background: var(--gold); border-radius: 50%; }

.it-heading {
  font-family: 'Nunito', sans-serif; font-weight: 900;
  font-size: clamp(1.55rem, 2.3vw, 2rem); line-height: 1.22;
  color: var(--white); margin-bottom: 14px;
}
.it-heading-gold { color: var(--gold); }

.it-sub {
  font-size: 0.88rem; line-height: 1.78;
  color: rgba(255,255,255,0.52); max-width: 270px;
}

/* Pills */
.it-pills { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 32px; }
.it-pill {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(255,255,255,0.06);
  border: 1px solid var(--gold-bd);
  border-radius: 100px;
  padding: 7px 14px;
  font-size: 0.79rem; font-weight: 500; color: rgba(255,255,255,0.78);
}
.it-pill-dot { width: 6px; height: 6px; background: var(--gold); border-radius: 50%; flex-shrink: 0; }

/* Bottom feat card */
.it-feat {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--gold-bd);
  border-radius: 18px;
  padding: 20px 22px;
  display: flex; align-items: flex-start; gap: 15px;
}
.it-feat-icon {
  width: 42px; height: 42px;
  background: var(--gold-bg);
  border: 1px solid var(--gold-bd);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.it-feat-icon svg { width: 18px; height: 18px; color: var(--gold); }
.it-feat-title { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.9rem; color: var(--white); margin-bottom: 5px; }
.it-feat-desc  { font-size: 0.8rem; line-height: 1.55; color: rgba(255,255,255,0.52); }

/* ── RIGHT ───────────────────────────────────────────────── */
.it-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 40px;
  background: var(--smoke);
  position: relative;
  overflow: hidden;
}

/* Decorative circles on white panel — soft yellow-green, plain */
.it-deco-ring {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  background: rgba(180, 210, 140, 0.18);
}
.it-deco-ring-1 { width: 420px; height: 420px; top: -180px; right: -180px; }
.it-deco-ring-2 { width: 300px; height: 300px; bottom: -120px; left: -120px; }
.it-deco-ring-3 { width: 110px; height: 110px; bottom: 22%; right: 8%; background: rgba(180, 210, 140, 0.13); }

.it-form-wrap {
  position: relative; z-index: 2;
  width: 100%; max-width: 400px;
  animation: itUp 0.55s 0.1s ease both;
}

/* Mobile brand */
.it-mobile-brand {
  display: none;
  align-items: center; gap: 12px;
  margin-bottom: 32px;
}
.it-mobile-name { font-family: 'Nunito', sans-serif; font-weight: 900; font-size: 1.2rem; color: var(--dark); display: block; }
.it-mobile-sub  { font-size: 0.72rem; color: var(--muted); font-weight: 600; display: block; }

/* Form header */
.it-fhead { margin-bottom: 24px; }
.it-ftitle {
  font-family: 'Nunito', sans-serif; font-weight: 900;
  font-size: 1.8rem; color: var(--dark); margin-bottom: 6px;
}
.it-fdesc { font-size: 0.88rem; color: var(--muted); line-height: 1.55; }

/* Status */
.it-status {
  background: rgba(27,77,62,0.07);
  border: 1px solid rgba(27,77,62,0.15);
  color: var(--g);
  font-size: 0.85rem; font-weight: 600;
  padding: 12px 16px; border-radius: 10px;
  margin-bottom: 18px; text-align: center;
}

/* Divider */
.it-divider {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 22px;
}
.it-divider-line { flex: 1; height: 1px; background: var(--border); }
.it-divider-text {
  font-size: 0.7rem; font-weight: 700; letter-spacing: 1px;
  text-transform: uppercase; color: var(--muted); white-space: nowrap;
}

/* Form */
.it-form { display: flex; flex-direction: column; gap: 16px; }

.it-field { display: flex; flex-direction: column; gap: 6px; }

.it-label {
  font-family: 'DM Sans', sans-serif;
  font-size: 0.82rem; font-weight: 600; color: var(--dark);
}

.it-iw { position: relative; }
.it-iico {
  position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
  display: flex; align-items: center; pointer-events: none; z-index: 1;
}
.it-iico svg { width: 15px; height: 15px; color: var(--muted); }

.it-iw :deep(input) {
  width: 100%;
  padding: 11px 14px 11px 38px;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.9rem;
  color: var(--dark);
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 12px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  appearance: none;
}
.it-iw :deep(input::placeholder) { color: rgba(138,158,151,0.5); }
.it-iw :deep(input:focus) {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(197,160,89,0.2);
}

.it-err { font-size: 0.78rem; color: #e53e3e; margin-top: 2px; }

/* Remember */
.it-remember { margin-top: -2px; }
.it-remember-label {
  display: flex; align-items: center; gap: 9px;
  font-size: 0.85rem; color: var(--body); cursor: pointer; user-select: none;
}
.it-remember-label :deep([data-slot="checkbox"]),
.it-remember-label :deep([role="checkbox"]) {
  width: 17px; height: 17px;
  border: 1.5px solid var(--border);
  border-radius: 5px; background: var(--white);
  transition: background 0.2s, border-color 0.2s;
  flex-shrink: 0;
}
.it-remember-label :deep([data-slot="checkbox"][data-state="checked"]),
.it-remember-label :deep([role="checkbox"][aria-checked="true"]) {
  background: var(--g); border-color: var(--g);
}

/* Button */
.it-btn {
  width: 100%;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 13px 0;
  background: var(--g) !important;
  color: var(--white) !important;
  font-family: 'Nunito', sans-serif !important;
  font-weight: 800 !important;
  font-size: 0.95rem !important;
  border-radius: 12px !important;
  border: none !important;
  cursor: pointer;
  transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
  box-shadow: 0 4px 18px rgba(27,77,62,0.22);
  margin-top: 4px;
}
.it-btn:hover:not(:disabled) {
  background: var(--g2) !important;
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(27,77,62,0.3);
}
.it-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.it-btn-ico { width: 16px; height: 16px; }
.it-spinner  { width: 16px; height: 16px; }

/* Admin note */
.it-note {
  margin-top: 20px;
  padding: 13px 16px;
  background: rgba(27,77,62,0.055);
  border: 1px solid rgba(27,77,62,0.11);
  border-radius: 12px;
  display: flex; align-items: flex-start; gap: 10px;
}
.it-note-ico { width: 15px; height: 15px; color: var(--g); flex-shrink: 0; margin-top: 2px; }
.it-note-text { font-size: 0.78rem; line-height: 1.6; color: #4a6258; }
.it-note-text strong { font-weight: 700; color: var(--g); }

/* Footer */
.it-footer {
  text-align: center;
  font-size: 0.72rem;
  color: rgba(138,158,151,0.45);
  margin-top: 36px;
}

/* ── Keyframes ───────────────────────────────────────────── */
@keyframes itUp {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 900px) {
  .it-left { width: 40%; }
  .it-inner { padding: 40px 32px; }
  .it-sub { display: none; }
  .it-feat { display: none; }
}

@media (max-width: 640px) {
  .it-root { flex-direction: column; }
  .it-left { display: none; }
  .it-right { padding: 32px 24px 48px; align-items: flex-start; }
  .it-form-wrap { max-width: 100%; }
  .it-mobile-brand { display: flex; }
  .it-ftitle { font-size: 1.6rem; }
}
</style>