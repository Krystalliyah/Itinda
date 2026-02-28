<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Log in — iTinda" />

    <div class="it-login-root">

        <!-- ── LEFT PANEL (brand / community vibe) ─────── -->
        <aside class="it-brand-panel">
            <div class="it-panel-grid"></div>
            <div class="it-panel-glow"></div>

            <div class="it-panel-inner">

                <!-- Logo -->
                <a href="/" class="it-panel-logo">
                    <div class="it-panel-logo-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span>iTinda</span>
                </a>

                <!-- Community headline -->
                <div class="it-panel-copy">
                    <div class="it-panel-eyebrow">Your local community</div>
                    <h2 class="it-panel-heading">
                        Everything you need,<br />
                        <span class="it-panel-accent">close to home.</span>
                    </h2>
                    <p class="it-panel-sub">
                        iTinda brings your neighborhood market online —
                        making it easier to find, connect, and transact
                        with the people and places right around you.
                    </p>
                </div>

                <!-- Testimonial / quote card — neutral, human -->
                <div class="it-quote-card">
                    <div class="it-quote-marks">"</div>
                    <p class="it-quote-text">
                        It feels like walking through our local market, but I can do it from anywhere.
                        Everything is right here.
                    </p>
                    <div class="it-quote-author">
                        <div class="it-quote-avatar">MR</div>
                        <div>
                            <div class="it-quote-name">Maria R.</div>
                            <div class="it-quote-loc">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Barangay San Isidro
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Simple stat strip — community scale only, no roles -->
                <div class="it-panel-stats">
                    <div class="it-ps-item">
                        <span class="it-ps-num">1,000+</span>
                        <span class="it-ps-label">Local Vendors</span>
                    </div>
                    <div class="it-ps-div"></div>
                    <div class="it-ps-item">
                        <span class="it-ps-num">50K+</span>
                        <span class="it-ps-label">Customers Served</span>
                    </div>
                </div>

            </div>
        </aside>

        <!-- ── RIGHT PANEL (form) ───────────────────────── -->
        <main class="it-form-panel">
            <div class="it-form-inner">

                <!-- Mobile logo -->
                <a href="/" class="it-mobile-logo">
                    <div class="it-mobile-logo-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span>iTinda</span>
                </a>

                <!-- Form header -->
                <div class="it-form-header">
                    <h1 class="it-form-title">Welcome back</h1>
                    <p class="it-form-desc">Log in to your iTinda account to continue.</p>
                </div>

                <!-- Status message -->
                <div v-if="status" class="it-status-msg">
                    {{ status }}
                </div>

                <!-- Form -->
                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="it-form"
                >
                    <!-- Email -->
                    <div class="it-field">
                        <Label for="email" class="it-label">Email address</Label>
                        <div class="it-input-wrap">
                            <span class="it-input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </span>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="email@example.com"
                                class="it-input"
                            />
                        </div>
                        <InputError :message="errors.email" class="it-field-error" />
                    </div>

                    <!-- Password -->
                    <div class="it-field">
                        <div class="it-label-row">
                            <Label for="password" class="it-label">Password</Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                :tabindex="5"
                                class="it-forgot-link"
                            >
                                Forgot password?
                            </TextLink>
                        </div>
                        <div class="it-input-wrap">
                            <span class="it-input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="it-input"
                            />
                        </div>
                        <InputError :message="errors.password" class="it-field-error" />
                    </div>

                    <!-- Remember me -->
                    <div class="it-remember-row">
                        <Label for="remember" class="it-remember-label">
                            <Checkbox id="remember" name="remember" :tabindex="3" class="it-checkbox" />
                            <span>Remember me for 30 days</span>
                        </Label>
                    </div>

                    <!-- Submit -->
                    <Button
                        type="submit"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                        class="it-submit-btn"
                    >
                        <Spinner v-if="processing" class="it-spinner" />
                        <span>{{ processing ? 'Logging in…' : 'Log in' }}</span>
                    </Button>

                    <!-- Sign up link -->
                    <p v-if="canRegister" class="it-signup-row">
                        Don't have an account?
                        <TextLink :href="register()" :tabindex="5" class="it-signup-link">
                            Sign up for free
                        </TextLink>
                    </p>
                </Form>

                <p class="it-form-footer">
                    &copy; {{ new Date().getFullYear() }} iTinda. All rights reserved.
                </p>
            </div>
        </main>

    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Lato:wght@300;400;700&display=swap');

/* ── Variables ─────────────────────────────────────────────── */
.it-login-root {
    --emerald:     #1B4D3E;
    --emerald-mid: #245c4a;
    --gold:        #C5A059;
    --gold-light:  #d9b87a;
    --gold-pale:   #f5ead4;
    --smoke:       #F5F5F5;
    --white:       #ffffff;
    --text-dark:   #0f2820;
    --text-body:   #3a4a44;
    --text-muted:  #7a8f88;
    --border:      rgba(27, 77, 62, 0.14);

    display: flex;
    min-height: 100vh;
    font-family: 'Lato', sans-serif;
    background: var(--smoke);
}

.it-login-root * { box-sizing: border-box; margin: 0; padding: 0; }
.it-login-root a { text-decoration: none; color: inherit; }

/* ════════════════════════════════════════════════════════════
   LEFT — BRAND PANEL
   ════════════════════════════════════════════════════════════ */
.it-brand-panel {
    width: 44%;
    flex-shrink: 0;
    background: var(--emerald);
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

/* Logo */
.it-panel-logo {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 900;
    font-size: 1.4rem;
    color: var(--gold);
}

.it-panel-logo-icon {
    width: 34px; height: 34px;
    background: var(--gold);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
}

.it-panel-logo-icon svg { width: 18px; height: 18px; color: var(--emerald); }

/* Eyebrow + heading */
.it-panel-copy { margin: auto 0; padding: 40px 0 32px; }

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

.it-panel-accent { color: var(--gold); }

.it-panel-sub {
    font-size: 0.93rem;
    line-height: 1.72;
    color: rgba(255, 255, 255, 0.55);
    max-width: 320px;
}

/* ── Testimonial quote card ────────────────────────────────── */
.it-quote-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(197, 160, 89, 0.2);
    border-radius: 14px;
    padding: 22px 24px 20px;
    margin-bottom: 28px;
    position: relative;
}

.it-quote-marks {
    font-family: 'Montserrat', sans-serif;
    font-size: 3rem;
    font-weight: 900;
    color: var(--gold);
    opacity: 0.4;
    line-height: 1;
    margin-bottom: 4px;
}

.it-quote-text {
    font-size: 0.9rem;
    line-height: 1.65;
    color: rgba(255, 255, 255, 0.78);
    font-style: italic;
    margin-bottom: 16px;
}

.it-quote-author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.it-quote-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: rgba(197, 160, 89, 0.22);
    border: 1px solid rgba(197, 160, 89, 0.35);
    color: var(--gold);
    font-family: 'Montserrat', sans-serif;
    font-size: 0.65rem;
    font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.it-quote-name {
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.9);
}

.it-quote-loc {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.42);
    margin-top: 2px;
}

.it-quote-loc svg { width: 10px; height: 10px; flex-shrink: 0; }

/* ── Stats strip — 2 stats only, no "roles" ───────────────── */
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
    font-size: 1.2rem;
    color: var(--gold);
}

.it-ps-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.4);
}

.it-ps-div {
    width: 1px; height: 28px;
    background: rgba(255, 255, 255, 0.1);
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

/* Mobile logo */
.it-mobile-logo {
    display: none;
    align-items: center;
    gap: 10px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 900;
    font-size: 1.3rem;
    color: var(--emerald);
    margin-bottom: 32px;
}

.it-mobile-logo-icon {
    width: 32px; height: 32px;
    background: var(--emerald);
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
}

.it-mobile-logo-icon svg { width: 16px; height: 16px; color: var(--white); }

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
    color: var(--emerald);
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

.it-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.it-forgot-link {
    font-family: 'Lato', sans-serif;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--emerald);
    opacity: 0.72;
    transition: opacity 0.2s;
}

.it-forgot-link:hover { opacity: 1; }

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

.it-input-wrap :deep(input::placeholder) { color: rgba(122, 143, 136, 0.55); }

.it-input-wrap :deep(input:focus) {
    border-color: var(--emerald);
    box-shadow: 0 0 0 3px rgba(27, 77, 62, 0.1);
}

.it-field-error { font-size: 0.8rem; color: #c0392b; margin-top: 2px; }

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
    background: var(--emerald);
    border-color: var(--emerald);
}

/* Submit button */
.it-submit-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 0;
    background: var(--emerald) !important;
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
    background: var(--emerald-mid) !important;
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(27, 77, 62, 0.28);
}

.it-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.it-spinner { width: 16px; height: 16px; }

/* Sign up row */
.it-signup-row {
    text-align: center;
    font-size: 0.88rem;
    color: var(--text-muted);
    margin-top: 4px;
}

.it-signup-link {
    font-weight: 700;
    color: var(--emerald);
    margin-left: 4px;
    transition: opacity 0.2s;
}

.it-signup-link:hover { opacity: 0.72; }

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
    .it-brand-panel { width: 40%; }
    .it-panel-inner { padding: 40px 32px; }
    .it-panel-sub   { display: none; }
    .it-quote-card  { display: none; }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE — mobile ≤ 640px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    .it-login-root  { flex-direction: column; }
    .it-brand-panel { display: none; }
    .it-form-panel  { padding: 32px 24px 48px; align-items: flex-start; }
    .it-form-inner  { max-width: 100%; }
    .it-mobile-logo { display: flex; }
    .it-form-title  { font-size: 1.6rem; }
}
</style>