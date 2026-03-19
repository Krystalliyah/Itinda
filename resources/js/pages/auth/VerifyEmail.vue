<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import '@/css/auth-verify.css';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Email verification" />

    <div class="it-verify-root">
        <div class="it-verify-card">
            <div class="it-brand">
                <div class="it-brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                    </svg>
                </div>
                <span class="it-brand-name">iTinda</span>
            </div>

            <h1 class="it-title">Verify your email</h1>
            <p class="it-description">
                We sent a verification email to your inbox. If you did not receive it, resend below.
            </p>

            <div
                v-if="status === 'verification-link-sent'"
                class="it-status"
            >
                A new verification link has been sent to your email address.
            </div>

            <Form
                v-bind="send.form()"
                class="it-actions"
                v-slot="{ processing }"
            >
                <Button :disabled="processing" class="it-resend-btn">
                    <Spinner v-if="processing" class="it-spinner" />
                    <span>
                        {{
                            processing
                                ? 'Sending...'
                                : status === 'verification-link-sent'
                                  ? 'Resend verification email'
                                  : 'Send verification email'
                        }}
                    </span>
                </Button>

                <Link
                    :href="logout()"
                    as="button"
                    class="it-logout-link"
                >
                    Log out
                </Link>
            </Form>
        </div>
    </div>
</template>

