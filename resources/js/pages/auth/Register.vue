<script setup lang="ts">
import { ref } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

// Role selection modal state
const showRoleModal = ref(true);
const selectedRole = ref<'customer' | 'vendor' | null>(null);

function selectRole(role: 'customer' | 'vendor') {
    selectedRole.value = role;
    showRoleModal.value = false;
}
</script>

<template>
    <Head title="Register — iTinda" />

    <div class="it-reg-root">

        <!-- ── ROLE SELECTION MODAL ─────────────────────── -->
        <Transition name="it-modal">
            <div v-if="showRoleModal" class="it-modal-backdrop">
                <div class="it-modal">
                    <div class="it-modal-grid"></div>
                    <div class="it-modal-glow"></div>

                    <div class="it-modal-inner">
                        <!-- Logo -->
                        <div class="it-modal-logo">
                            <div class="it-modal-logo-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <span>iTinda</span>
                        </div>

                        <h2 class="it-modal-title">How will you use iTinda?</h2>
                        <p class="it-modal-sub">Choose your role to get started. You can always update this later.</p>

                        <div class="it-role-cards">
                            <!-- Customer -->
                            <button class="it-role-card it-role-customer" @click="selectRole('customer')">
                                <div class="it-rc-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="it-rc-label">I'm a Customer</div>
                                <p class="it-rc-desc">Browse nearby shops, compare prices, and pre-order from local vendors.</p>
                                <div class="it-rc-arrow">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </button>

                            <!-- Vendor -->
                            <button class="it-role-card it-role-vendor" @click="selectRole('vendor')">
                                <div class="it-rc-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div class="it-rc-label">I'm a Vendor</div>
                                <p class="it-rc-desc">Manage your store, track inventory, monitor sales and grow your local business.</p>
                                <div class="it-rc-arrow">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </button>
                        </div>

                        <p class="it-modal-login">
                            Already have an account?
                            <TextLink :href="login()" class="it-modal-login-link">Log in</TextLink>
                        </p>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ── LEFT PANEL (brand) ───────────────────────── -->
        <aside class="it-brand-panel">
            <div class="it-panel-grid"></div>
            <div class="it-panel-glow"></div>

            <div class="it-panel-inner">
                <a href="/" class="it-panel-logo">
                    <div class="it-panel-logo-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span>iTinda</span>
                </a>

                <!-- Dynamic copy based on selected role -->
                <div class="it-panel-copy">
                    <div class="it-panel-eyebrow">
                        {{ selectedRole === 'vendor' ? 'For local businesses' : selectedRole === 'customer' ? 'For shoppers' : 'Join your community' }}
                    </div>
                    <h2 class="it-panel-heading">
                        <template v-if="selectedRole === 'vendor'">
                            Run your store<br />
                            <span class="it-panel-accent">smarter, not harder.</span>
                        </template>
                        <template v-else-if="selectedRole === 'customer'">
                            Your neighborhood<br />
                            <span class="it-panel-accent">at your fingertips.</span>
                        </template>
                        <template v-else>
                            Everything you need,<br />
                            <span class="it-panel-accent">close to home.</span>
                        </template>
                    </h2>
                    <p class="it-panel-sub">
                        <template v-if="selectedRole === 'vendor'">
                            Manage inventory, track expenses, and reach more customers in your area — all from one simple dashboard.
                        </template>
                        <template v-else-if="selectedRole === 'customer'">
                            Discover stores near you, compare prices, and pre-order from local vendors without leaving home.
                        </template>
                        <template v-else>
                            iTinda brings your neighborhood market online — making it easier to find, connect, and transact with the people and places right around you.
                        </template>
                    </p>
                </div>

                <!-- Role badge (shown once selected) -->
                <div v-if="selectedRole" class="it-role-badge">
                    <div class="it-rb-dot"></div>
                    <span>Signing up as a <strong>{{ selectedRole === 'vendor' ? 'Vendor' : 'Customer' }}</strong></span>
                    <button class="it-rb-change" @click="showRoleModal = true">Change</button>
                </div>

                <!-- Testimonial quote card -->
                <div class="it-quote-card">
                    <div class="it-quote-marks">"</div>
                    <p class="it-quote-text">
                        <template v-if="selectedRole === 'vendor'">
                            Since joining iTinda, I can see exactly what's moving and what's not. It changed how I run my tindahan entirely.
                        </template>
                        <template v-else>
                            It feels like walking through our local market, but I can do it from anywhere. Everything is right here.
                        </template>
                    </p>
                    <div class="it-quote-author">
                        <div class="it-quote-avatar">{{ selectedRole === 'vendor' ? 'JD' : 'MR' }}</div>
                        <div>
                            <div class="it-quote-name">{{ selectedRole === 'vendor' ? 'Jun D.' : 'Maria R.' }}</div>
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
                    <div v-if="selectedRole" class="it-form-role-chip">
                        <span class="it-chip-dot"></span>
                        {{ selectedRole === 'vendor' ? 'Vendor account' : 'Customer account' }}
                        <button class="it-chip-change" @click="showRoleModal = true">Change</button>
                    </div>
                    <h1 class="it-form-title">Create your account</h1>
                    <p class="it-form-desc">Join thousands of people in your local community.</p>
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="it-form"
                >
                    <!-- Hidden role field -->
                    <input type="hidden" name="role" :value="selectedRole" />

                    <!-- Name -->
                    <div class="it-field">
                        <Label for="name" class="it-label">Full name</Label>
                        <div class="it-input-wrap">
                            <span class="it-input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <Input id="name" type="text" name="name" required autofocus
                                :tabindex="1" autocomplete="name" placeholder="Your full name" class="it-input" />
                        </div>
                        <InputError :message="errors.name" class="it-field-error" />
                    </div>

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
                            <Input id="email" type="email" name="email" required
                                :tabindex="2" autocomplete="email" placeholder="email@example.com" class="it-input" />
                        </div>
                        <InputError :message="errors.email" class="it-field-error" />
                    </div>

                    <!-- Phone (optional) -->
                    <div class="it-field">
                        <Label for="phone" class="it-label">Phone number <span style="opacity: 0.5; font-size: 0.85em;">(optional)</span></Label>
                        <div class="it-input-wrap">
                            <span class="it-input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            <Input id="phone" type="tel" name="phone"
                                :tabindex="3" autocomplete="tel" placeholder="+63 912 345 6789" class="it-input" />
                        </div>
                        <InputError :message="errors.phone" class="it-field-error" />
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
                            <Input id="password" type="password" name="password" required
                                :tabindex="4" autocomplete="new-password" placeholder="Create a password" class="it-input" />
                        </div>
                        <InputError :message="errors.password" class="it-field-error" />
                    </div>

                    <!-- Confirm password -->
                    <div class="it-field">
                        <Label for="password_confirmation" class="it-label">Confirm password</Label>
                        <div class="it-input-wrap">
                            <span class="it-input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <Input id="password_confirmation" type="password" name="password_confirmation" required
                                :tabindex="5" autocomplete="new-password" placeholder="Repeat your password" class="it-input" />
                        </div>
                        <InputError :message="errors.password_confirmation" class="it-field-error" />
                    </div>

                    <!-- Submit -->
                    <Button
                        type="submit"
                        :tabindex="6"
                        :disabled="processing || !selectedRole"
                        data-test="register-user-button"
                        class="it-submit-btn"
                    >
                        <Spinner v-if="processing" class="it-spinner" />
                        <span>{{ processing ? 'Creating account…' : 'Create account' }}</span>
                    </Button>

                    <p v-if="!selectedRole" class="it-no-role-hint">
                        Please select a role above to continue.
                    </p>

                    <!-- Login link -->
                    <p class="it-login-row">
                        Already have an account?
                        <TextLink :href="login()" :tabindex="7" class="it-login-link">Log in</TextLink>
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
.it-reg-root {
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
    position: relative;
}

.it-reg-root * { box-sizing: border-box; margin: 0; padding: 0; }
.it-reg-root a { text-decoration: none; color: inherit; }

/* ════════════════════════════════════════════════════════════
   ROLE SELECTION MODAL
   ════════════════════════════════════════════════════════════ */
.it-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 500;
    background: rgba(15, 40, 32, 0.7);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.it-modal {
    background: var(--emerald);
    border-radius: 20px;
    width: 100%;
    max-width: 540px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.35);
}

.it-modal-grid {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(197,160,89,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(197,160,89,0.05) 1px, transparent 1px);
    background-size: 44px 44px;
    pointer-events: none;
}

.it-modal-glow {
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 70% 60% at 80% 10%, rgba(197,160,89,0.14) 0%, transparent 60%);
    pointer-events: none;
}

.it-modal-inner {
    position: relative; z-index: 2;
    padding: 40px 44px 36px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.it-modal-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 900;
    font-size: 1.35rem;
    color: var(--gold);
    margin-bottom: 28px;
}

.it-modal-logo-icon {
    width: 32px; height: 32px;
    background: var(--gold);
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
}

.it-modal-logo-icon svg { width: 17px; height: 17px; color: var(--emerald); }

.it-modal-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.55rem;
    color: var(--white);
    margin-bottom: 10px;
}

.it-modal-sub {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.55);
    line-height: 1.6;
    margin-bottom: 32px;
    max-width: 340px;
}

/* Role cards inside modal */
.it-role-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    width: 100%;
    margin-bottom: 28px;
}

.it-role-card {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(197,160,89,0.2);
    border-radius: 14px;
    padding: 24px 20px;
    cursor: pointer;
    text-align: left;
    position: relative;
    transition: background 0.2s, border-color 0.2s, transform 0.18s;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.it-role-card:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(197,160,89,0.5);
    transform: translateY(-2px);
}

.it-rc-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.it-role-customer .it-rc-icon {
    background: rgba(197,160,89,0.18);
}

.it-role-vendor .it-rc-icon {
    background: rgba(197,160,89,0.18);
}

.it-rc-icon svg { width: 20px; height: 20px; color: var(--gold); }

.it-rc-label {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    color: var(--white);
}

.it-rc-desc {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.55);
    line-height: 1.55;
}

.it-rc-arrow {
    position: absolute;
    bottom: 16px; right: 16px;
    width: 22px; height: 22px;
    color: rgba(197,160,89,0.5);
    transition: color 0.2s, transform 0.2s;
}

.it-role-card:hover .it-rc-arrow {
    color: var(--gold);
    transform: translateX(3px);
}

.it-rc-arrow svg { width: 100%; height: 100%; }

.it-modal-login {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.45);
}

.it-modal-login-link {
    color: var(--gold);
    font-weight: 700;
    margin-left: 4px;
    transition: opacity 0.2s;
}

.it-modal-login-link:hover { opacity: 0.75; }

/* Modal transition */
.it-modal-enter-active,
.it-modal-leave-active {
    transition: opacity 0.25s ease;
}

.it-modal-enter-active .it-modal,
.it-modal-leave-active .it-modal {
    transition: transform 0.25s ease, opacity 0.25s ease;
}

.it-modal-enter-from,
.it-modal-leave-to {
    opacity: 0;
}

.it-modal-enter-from .it-modal {
    transform: scale(0.95) translateY(12px);
}

.it-modal-leave-to .it-modal {
    transform: scale(0.95) translateY(12px);
    opacity: 0;
}

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
    animation: itFadeUp 0.5s ease both;
}

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

.it-panel-copy { margin: auto 0; padding: 40px 0 28px; }

.it-panel-eyebrow {
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.72rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(197,160,89,0.7);
    margin-bottom: 12px;
    transition: opacity 0.3s;
}

.it-panel-heading {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: clamp(1.6rem, 2.3vw, 2.1rem);
    line-height: 1.22;
    color: var(--white);
    margin-bottom: 16px;
    transition: opacity 0.3s;
}

.it-panel-accent { color: var(--gold); }

.it-panel-sub {
    font-size: 0.92rem;
    line-height: 1.72;
    color: rgba(255,255,255,0.55);
    max-width: 310px;
    transition: opacity 0.3s;
}

/* Role badge — shown after role selected */
.it-role-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(197,160,89,0.14);
    border: 1px solid rgba(197,160,89,0.28);
    border-radius: 100px;
    padding: 6px 14px 6px 10px;
    margin-bottom: 20px;
    font-size: 0.82rem;
    color: rgba(255,255,255,0.8);
    animation: itFadeUp 0.3s ease both;
}

.it-rb-dot {
    width: 7px; height: 7px;
    background: var(--gold);
    border-radius: 50%;
    flex-shrink: 0;
    animation: itPulse 2s infinite;
}

.it-role-badge strong { color: var(--gold); }

.it-rb-change {
    background: none; border: none; cursor: pointer;
    font-family: 'Lato', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    color: rgba(255,255,255,0.45);
    margin-left: 4px;
    padding: 0;
    transition: color 0.2s;
}

.it-rb-change:hover { color: var(--gold); }

/* Quote card */
.it-quote-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(197,160,89,0.18);
    border-radius: 14px;
    padding: 20px 22px 18px;
    margin-bottom: 24px;
}

.it-quote-marks {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.8rem; font-weight: 900;
    color: var(--gold); opacity: 0.4;
    line-height: 1; margin-bottom: 4px;
}

.it-quote-text {
    font-size: 0.88rem; line-height: 1.65;
    color: rgba(255,255,255,0.75);
    font-style: italic; margin-bottom: 14px;
}

.it-quote-author { display: flex; align-items: center; gap: 10px; }

.it-quote-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: rgba(197,160,89,0.2);
    border: 1px solid rgba(197,160,89,0.32);
    color: var(--gold);
    font-family: 'Montserrat', sans-serif;
    font-size: 0.62rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.it-quote-name {
    font-family: 'Lato', sans-serif; font-weight: 700;
    font-size: 0.8rem; color: rgba(255,255,255,0.88);
}

.it-quote-loc {
    display: flex; align-items: center; gap: 4px;
    font-size: 0.7rem; color: rgba(255,255,255,0.38); margin-top: 2px;
}

.it-quote-loc svg { width: 10px; height: 10px; }

/* Stats */
.it-panel-stats {
    display: flex; align-items: center; gap: 24px;
    padding: 16px 20px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(197,160,89,0.14);
    border-radius: 12px;
}

.it-ps-item { display: flex; flex-direction: column; gap: 2px; }

.it-ps-num {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800; font-size: 1.18rem; color: var(--gold);
}

.it-ps-label {
    font-size: 0.67rem; font-weight: 700;
    letter-spacing: 0.8px; text-transform: uppercase;
    color: rgba(255,255,255,0.38);
}

.it-ps-div { width: 1px; height: 28px; background: rgba(255,255,255,0.1); }

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
    overflow-y: auto;
}

.it-form-inner {
    width: 100%;
    max-width: 420px;
    display: flex;
    flex-direction: column;
    animation: itFadeUp 0.5s 0.12s ease both;
}

/* Mobile logo */
.it-mobile-logo {
    display: none;
    align-items: center;
    gap: 10px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 900; font-size: 1.3rem;
    color: var(--emerald);
    margin-bottom: 32px;
}

.it-mobile-logo-icon {
    width: 32px; height: 32px;
    background: var(--emerald); border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
}

.it-mobile-logo-icon svg { width: 16px; height: 16px; color: var(--white); }

/* Form header */
.it-form-header { margin-bottom: 28px; }

/* Role chip in the form header */
.it-form-role-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(27,77,62,0.08);
    border: 1px solid rgba(27,77,62,0.18);
    border-radius: 100px;
    padding: 5px 12px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--emerald);
    margin-bottom: 14px;
    animation: itFadeUp 0.3s ease both;
}

.it-chip-dot {
    width: 6px; height: 6px;
    background: var(--emerald);
    border-radius: 50%;
    flex-shrink: 0;
}

.it-chip-change {
    background: none; border: none; cursor: pointer;
    font-family: 'Lato', sans-serif;
    font-size: 0.75rem; font-weight: 700;
    color: var(--text-muted); padding: 0;
    margin-left: 2px;
    transition: color 0.2s;
}

.it-chip-change:hover { color: var(--emerald); }

.it-form-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800; font-size: 1.8rem;
    color: var(--text-dark); margin-bottom: 8px;
}

.it-form-desc {
    font-size: 0.92rem; color: var(--text-muted); line-height: 1.5;
}

/* Form layout */
.it-form { display: flex; flex-direction: column; gap: 18px; }

.it-field { display: flex; flex-direction: column; gap: 7px; }

.it-label {
    font-family: 'Lato', sans-serif;
    font-weight: 700; font-size: 0.85rem;
    color: var(--text-dark);
}

.it-input-wrap { position: relative; }

.it-input-icon {
    position: absolute; left: 13px; top: 50%;
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

.it-input-wrap :deep(input::placeholder) { color: rgba(122,143,136,0.55); }

.it-input-wrap :deep(input:focus) {
    border-color: var(--emerald);
    box-shadow: 0 0 0 3px rgba(27,77,62,0.1);
}

.it-field-error { font-size: 0.8rem; color: #c0392b; margin-top: 2px; }

/* Submit */
.it-submit-btn {
    width: 100%;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 13px 0;
    background: var(--emerald) !important;
    color: var(--white) !important;
    font-family: 'Montserrat', sans-serif !important;
    font-weight: 700 !important; font-size: 0.95rem !important;
    border-radius: 8px !important; border: none !important;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 18px rgba(27,77,62,0.22);
    margin-top: 4px;
}

.it-submit-btn:hover:not(:disabled) {
    background: var(--emerald-mid) !important;
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(27,77,62,0.28);
}

.it-submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }

.it-spinner { width: 16px; height: 16px; }

/* No role hint */
.it-no-role-hint {
    text-align: center;
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: -4px;
}

/* Login row */
.it-login-row {
    text-align: center; font-size: 0.88rem;
    color: var(--text-muted); margin-top: 4px;
}

.it-login-link {
    font-weight: 700; color: var(--emerald); margin-left: 4px;
    transition: opacity 0.2s;
}

.it-login-link:hover { opacity: 0.72; }

.it-form-footer {
    text-align: center; font-size: 0.77rem;
    color: rgba(122,143,136,0.5); margin-top: 40px;
}

/* ════════════════════════════════════════════════════════════
   KEYFRAMES
   ════════════════════════════════════════════════════════════ */
@keyframes itFadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes itPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.75); }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE — tablet ≤ 900px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .it-brand-panel   { width: 40%; }
    .it-panel-inner   { padding: 40px 32px; }
    .it-panel-sub     { display: none; }
    .it-quote-card    { display: none; }
    .it-role-cards    { grid-template-columns: 1fr; }
    .it-modal-inner   { padding: 32px 28px 28px; }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE — mobile ≤ 640px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    .it-reg-root      { flex-direction: column; }
    .it-brand-panel   { display: none; }
    .it-form-panel    { padding: 32px 24px 48px; align-items: flex-start; }
    .it-form-inner    { max-width: 100%; }
    .it-mobile-logo   { display: flex; }
    .it-form-title    { font-size: 1.55rem; }
    .it-modal-inner   { padding: 28px 20px 24px; }
    .it-role-cards    { grid-template-columns: 1fr; gap: 12px; }
}
</style>