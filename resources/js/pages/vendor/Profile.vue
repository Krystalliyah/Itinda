<script setup lang="ts">
import { Head, Form, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
import { useSidebar } from '@/composables/useSidebar';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import { send } from '@/routes/verification';

defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
}>();

const { isCollapsed } = useSidebar();

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

const page = usePage();
const user = computed(() => page.props.auth.user as {
    name: string;
    email: string;
    email_verified_at?: string | null;
});
</script>

<template>
    <Head title="Vendor Profile" />

    <div class="dashboard-wrapper">
        <Header />

        <Sidebar role="vendor">
            <VendorNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#163F35]">
                        My Profile
                    </h1>
                    <p class="mt-1 text-sm text-[#5F766E]">
                        Update your account details and keep your vendor login secure.
                    </p>
                </div>

                <div class="grid gap-6">
                    <Card class="border-[#DCE7E0] bg-white shadow-sm">
                        <CardHeader>
                            <CardTitle class="text-lg text-[#173F35]">
                                Profile Information
                            </CardTitle>
                            <CardDescription class="text-[#657C74]">
                                Change your username and email address.
                            </CardDescription>
                        </CardHeader>

                        <CardContent>
                            <Form
                                v-bind="ProfileController.update.form()"
                                class="space-y-6"
                                v-slot="{ errors, processing, recentlySuccessful }"
                            >
                                <div class="grid gap-5 md:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="name">Username / Name</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            :default-value="user.name"
                                            required
                                            autocomplete="name"
                                            placeholder="Enter your name"
                                        />
                                        <InputError :message="errors.name" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="email">Email address</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            :default-value="user.email"
                                            required
                                            autocomplete="username"
                                            placeholder="Enter your email"
                                            :aria-invalid="!!errors.email"
                                        />
                                        <InputError :message="errors.email" />
                                    </div>
                                </div>

                                <div
                                    v-if="mustVerifyEmail && !user.email_verified_at"
                                    class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3"
                                >
                                    <p class="text-sm text-amber-800">
                                        Your email address is unverified.
                                        <Link
                                            :href="send()"
                                            as="button"
                                            class="ml-1 font-medium underline underline-offset-4"
                                        >
                                            Click here to resend the verification email.
                                        </Link>
                                    </p>

                                    <div
                                        v-if="status === 'verification-link-sent'"
                                        class="mt-2 text-sm font-medium text-green-600"
                                    >
                                        A new verification link has been sent to your email address.
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <Button
                                        :disabled="processing"
                                        class="bg-[#17493D] text-white hover:bg-[#10362D]"
                                    >
                                        Save profile
                                    </Button>

                                    <Transition
                                        enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0"
                                    >
                                        <p
                                            v-show="recentlySuccessful"
                                            class="text-sm font-medium text-[#1B5B4B]"
                                        >
                                            Profile updated.
                                        </p>
                                    </Transition>
                                </div>
                            </Form>
                        </CardContent>
                    </Card>

                    <Card class="border-[#DCE7E0] bg-white shadow-sm">
                        <CardHeader>
                            <CardTitle class="text-lg text-[#173F35]">
                                Change Password
                            </CardTitle>
                            <CardDescription class="text-[#657C74]">
                                Use a strong password to protect your vendor account.
                            </CardDescription>
                        </CardHeader>

                        <CardContent>
                            <Form
                                v-bind="PasswordController.update.form()"
                                class="space-y-6"
                                v-slot="{ errors, processing, recentlySuccessful }"
                            >
                                <div class="grid gap-5 md:grid-cols-2">
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label for="current_password">Current password</Label>
                                        <Input
                                            id="current_password"
                                            type="password"
                                            name="current_password"
                                            required
                                            autocomplete="current-password"
                                            placeholder="Enter your current password"
                                        />
                                        <InputError :message="errors.current_password" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="password">New password</Label>
                                        <Input
                                            id="password"
                                            type="password"
                                            name="password"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Enter a new password"
                                        />
                                        <p class="text-xs text-[#6B7C75]">
                                            Use at least 8 characters with uppercase, lowercase, and a number.
                                        </p>
                                        <InputError :message="errors.password" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="password_confirmation">Confirm new password</Label>
                                        <Input
                                            id="password_confirmation"
                                            type="password"
                                            name="password_confirmation"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Confirm your new password"
                                        />
                                        <InputError :message="errors.password_confirmation" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <Button
                                        :disabled="processing"
                                        class="bg-[#17493D] text-white hover:bg-[#10362D]"
                                    >
                                        Update password
                                    </Button>

                                    <Transition
                                        enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0"
                                    >
                                        <p
                                            v-show="recentlySuccessful"
                                            class="text-sm font-medium text-[#1B5B4B]"
                                        >
                                            Password updated.
                                        </p>
                                    </Transition>
                                </div>
                            </Form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </main>
    </div>
</template>