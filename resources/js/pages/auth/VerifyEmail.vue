<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Email verification — iTinda" />

    <div class="it-verify-root">
        <main class="it-form-panel">
            <div class="it-form-inner">
                
                <!-- Logo -->
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
                    <h1 class="it-form-title">Check your inbox</h1>
                    <div class="it-email-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="it-form-desc">
                        We've sent a verification link to your email address.
                        <strong>Please check your inbox</strong> and click the link to verify your account.
                    </p>
                </div>

                <!-- Status message -->
                <div v-if="status === 'verification-link-sent'" class="it-status-msg">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>A new verification link has been sent!</span>
                </div>

                <!-- Resend section -->
                <div class="it-resend-section">
                    <p class="it-resend-label">Didn't receive the email?</p>
                    
                    <!-- Form -->
                    <Form
                        v-bind="send.form()"
                        class="it-resend-form"
                        v-slot="{ processing }"
                    >
                        <!-- Submit -->
                        <Button
                            type="submit"
                            :disabled="processing"
                            class="it-resend-btn"
                        >
                            <Spinner v-if="processing" class="it-spinner" />
                            <span>
                                {{ processing ? 'Sending...' : 'Resend verification email' }}
                            </span>
                        </Button>

                        <p class="it-resend-note">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            Check spam folder
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </p>
                    </Form>
                </div>

                <!-- Log out row -->
                <p class="it-signup-row">
                    Need to sign in as someone else?
                    <Link :href="logout()" as="button" class="it-signup-link">Log out</Link>
                </p>

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
.it-verify-root {
    --emerald:     #1b4d3e;
    --emerald-light: #e8f3ef;
    --emerald-mid: #2a6b57;
    --emerald-soft: rgba(27, 77, 62, 0.06);
    --emerald-glow: rgba(27, 77, 62, 0.15);
    --gold:        #C5A059;
    --smoke:       #f0f5f2;
    --white:       #ffffff;
    --text-dark:   #0f2820;
    --text-body:   #2c3e37;
    --text-muted:  #5b7068;
    --text-light:  #7e948c;
    --border:      rgba(27, 77, 62, 0.12);

    display: flex;
    min-height: 100vh;
    font-family: 'Lato', sans-serif;
    background: linear-gradient(145deg, #cbdcd5 0%, #b5ccc2 100%);
    position: relative;
}

/* Subtle pattern overlay */
.it-verify-root::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: radial-gradient(circle at 30% 40%, rgba(27, 77, 62, 0.03) 0%, transparent 30%),
                      radial-gradient(circle at 70% 60%, rgba(197, 160, 89, 0.03) 0%, transparent 40%);
    pointer-events: none;
}

.it-verify-root * { box-sizing: border-box; margin: 0; padding: 0; }
.it-verify-root a, .it-verify-root button { text-decoration: none; color: inherit; }

/* ════════════════════════════════════════════════════════════
   FORM PANEL
   ════════════════════════════════════════════════════════════ */
.it-form-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    position: relative;
    z-index: 2;
}

.it-form-inner {
    width: 100%;
    max-width: 400px;
    background: white;
    padding: 0 32px 28px;
    border-radius: 24px;
    box-shadow: 0 20px 40px -12px rgba(27, 77, 62, 0.4);
    animation: itFadeUp 0.5s ease both;
    position: relative;
    overflow: hidden;
}

/* INTEGRATED gradient bar - now part of the modal */
.it-form-inner::before {
    content: '';
    position: relative;
    display: block;
    width: calc(100% + 64px); /* Extend to cover padding */
    height: 4px;
    background: linear-gradient(90deg, 
        var(--emerald) 0%, 
        var(--gold) 50%, 
        var(--emerald) 100%);
    margin-left: -32px; /* Offset the padding */
    margin-right: -32px;
    margin-bottom: 28px;
    box-shadow: 0 2px 8px rgba(27, 77, 62, 0.2);
}

/* Logo - adjusted margin */
.it-mobile-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 900;
    font-size: 1.3rem;
    color: var(--emerald);
    margin-bottom: 20px;
}

.it-mobile-logo-icon {
    width: 32px; height: 32px;
    background: var(--emerald);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 10px rgba(27, 77, 62, 0.25);
}

.it-mobile-logo-icon svg { width: 16px; height: 16px; color: var(--gold); }

/* Form header */
.it-form-header { 
    margin-bottom: 24px; 
    text-align: center; 
}

.it-form-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.5rem;
    color: var(--text-dark);
    margin-bottom: 12px;
    letter-spacing: -0.02em;
}

.it-email-icon {
    width: 56px;
    height: 56px;
    background: var(--emerald-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    border: 2px solid white;
    box-shadow: 0 4px 12px rgba(27, 77, 62, 0.15);
}

.it-email-icon svg {
    width: 28px;
    height: 28px;
    color: var(--emerald);
}

.it-form-desc {
    font-size: 0.9rem;
    color: var(--text-body);
    line-height: 1.6;
    max-width: 300px;
    margin: 0 auto;
}

.it-form-desc strong {
    color: var(--emerald);
    font-weight: 700;
    background: var(--emerald-light);
    padding: 2px 6px;
    border-radius: 6px;
    font-weight: 800;
    font-size: 0.85rem;
}

/* Status message */
.it-status-msg {
    background: var(--emerald-light);
    border: 1px solid var(--emerald);
    color: var(--emerald);
    font-size: 0.85rem;
    font-weight: 600;
    padding: 10px 14px;
    border-radius: 30px;
    margin-bottom: 20px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(27, 77, 62, 0.12);
}

.it-status-msg svg {
    width: 16px;
    height: 16px;
    stroke-width: 2.5;
    color: var(--emerald);
}

/* Resend section */
.it-resend-section {
    border-top: 1px solid var(--border);
    padding-top: 20px;
    margin-top: 4px;
}

.it-resend-label {
    text-align: center;
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 12px;
    font-weight: 500;
}

.it-resend-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Resend button */
.it-resend-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 0;
    background: var(--white) !important;
    color: var(--emerald) !important;
    font-family: 'Montserrat', sans-serif !important;
    font-weight: 700 !important;
    font-size: 0.85rem !important;
    border-radius: 30px !important;
    border: 2px solid var(--emerald) !important;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(27, 77, 62, 0.1);
}

.it-resend-btn:hover:not(:disabled) {
    background: var(--emerald) !important;
    color: white !important;
    border-color: var(--emerald) !important;
    transform: translateY(-1px);
    box-shadow: 0 8px 16px rgba(27, 77, 62, 0.25);
}

.it-resend-btn:active:not(:disabled) {
    transform: translateY(0);
}

.it-resend-btn:disabled { 
    opacity: 0.5; 
    cursor: not-allowed;
    border-color: var(--text-light) !important;
    color: var(--text-light) !important;
    background: var(--smoke) !important;
}

.it-resend-note {
    text-align: center;
    font-size: 0.8rem;
    color: var(--text-light);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.it-resend-note svg {
    width: 12px;
    height: 12px;
    color: var(--emerald);
    opacity: 0.5;
}

.it-spinner { 
    width: 16px; 
    height: 16px; 
    color: currentColor;
}

/* Sign out row */
.it-signup-row {
    text-align: center;
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 20px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
}

.it-signup-link {
    font-weight: 700;
    color: var(--emerald);
    margin-left: 4px;
    transition: all 0.2s;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 2px 6px;
    font-family: inherit;
    font-size: inherit;
    border-radius: 4px;
}

.it-signup-link:hover { 
    background: var(--emerald-light);
    color: var(--emerald-mid);
}

/* Footer */
.it-form-footer {
    text-align: center;
    font-size: 0.7rem;
    color: var(--text-light);
    margin-top: 20px;
    opacity: 0.7;
}

/* ════════════════════════════════════════════════════════════
   KEYFRAMES
   ════════════════════════════════════════════════════════════ */
@keyframes itFadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE
   ════════════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    .it-verify-root {
        background: linear-gradient(145deg, #c0d4cc 0%, #adc6bb 100%);
    }
    
    .it-form-inner {
        padding: 0 20px 24px;
    }
    
    .it-form-inner::before {
        width: calc(100% + 40px);
        margin-left: -20px;
        margin-right: -20px;
        margin-bottom: 24px;
    }
    
    .it-form-panel { 
        padding: 16px; 
    }
    
    .it-email-icon {
        width: 48px;
        height: 48px;
    }
    
    .it-email-icon svg {
        width: 24px;
        height: 24px;
    }
    
    .it-form-title {
        font-size: 1.4rem;
    }
}
</style>