<script setup lang="ts">
import { Head, Form, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BadgeCheck,
    Bell,
    Mail,
    Phone,
    ShieldCheck,
    UserRound,
} from 'lucide-vue-next';

import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import VendorNav from '@/components/navigation/VendorNav.vue';
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
import { send } from '@/routes/verification';
import { useSidebar } from '@/composables/useSidebar';

const { isCollapsed } = useSidebar();

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

const page = usePage();

const profile = computed(() => {
    const props = page.props as any;
    
    // Get the user data from auth
    const user = props.auth?.user ?? {};
    
    // Get any profile-specific data
    const profileData = props.profile ?? {};
    
    // Merge them, prioritizing profile data but falling back to auth user data
    // This ensures email_verified_at comes from the actual user record
    return {
        name: profileData.name || user.name || '',
        email: profileData.email || user.email || '',
        phone: profileData.phone || user.phone || '',
        email_verified_at: user.email_verified_at ?? null, // Always from auth user
        login_id: user.login_id ?? '',
        role_label: 'Store Admin',
    };
});

const props = defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const initials = computed(() => {
    return profile.value.name
        ?.split(' ')
        .map((part: string) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});

const form = useForm({
    name: profile.value.name ?? '',
    email: profile.value.email ?? '',
    phone: profile.value.phone ?? '',
});

const saveChanges = () => {
    form.put('/vendor/profile', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="My Profile" />

    <div class="dashboard-wrapper">
        <Header />

        <Sidebar role="vendor">
            <VendorNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-5 lg:px-8 dark:bg-slate-900/60">
                <div class="mb-4 sm:mb-5">
                    <h1 class="text-xl sm:text-2xl font-semibold tracking-tight text-[#163F35] dark:text-slate-100">
                        My Profile
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-[#5F766E] dark:text-slate-300">
                        Manage your personal information, account details, and security settings.
                    </p>
                </div>

                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_360px]">
                    <div class="space-y-4">
                        <Card
                            class="rounded-xl border border-[#DCE7E0] bg-gradient-to-br from-white to-[#F7FAF8] shadow-[0_12px_32px_rgba(23,73,61,0.06)] dark:border-slate-700 dark:bg-slate-800 dark:from-slate-800 dark:to-slate-900 dark:shadow-[0_18px_40px_rgba(15,23,42,0.65)]"
                        >
                            <CardContent class="p-6">
                                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex min-w-0 items-start gap-4">
                                        <div
                                            class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-[#EAF4EF] text-xl font-semibold text-[#17493D] dark:bg-slate-700 dark:text-slate-100"
                                        >
                                            {{ initials }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h2
                                                    class="truncate text-lg sm:text-2xl font-semibold tracking-tight text-[#163F35] dark:text-slate-100"
                                                >
                                                    {{ profile.name }}
                                                </h2>

                                                <span
                                                    v-if="profile.email_verified_at"
                                                    class="inline-flex items-center gap-1 rounded-full bg-[#EAF4EF] px-2.5 py-1 text-xs font-semibold text-[#1B5B4B] dark:bg-emerald-500/10 dark:text-emerald-300"
                                                >
                                                    <BadgeCheck class="h-3.5 w-3.5" />
                                                    Verified
                                                </span>

                                                <span
                                                    v-else
                                                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300"
                                                >
                                                    <Mail class="h-3.5 w-3.5" />
                                                    Unverified email
                                                </span>
                                            </div>

                                            <p class="mt-1 text-sm text-[#60756D] dark:text-slate-300">
                                                {{ profile.email }}
                                            </p>

                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full border border-[#D8E4DD] bg-white px-3 py-1 text-xs font-medium text-[#3C6658] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                                                >
                                                    <ShieldCheck class="h-3.5 w-3.5" />
                                                    {{ profile.role_label }}
                                                </span>
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full border border-[#D8E4DD] bg-white px-3 py-1 text-xs font-medium text-[#3C6658] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                                                >
                                                    <Bell class="h-3.5 w-3.5" />
                                                    Notifications enabled
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card
                            class="rounded-xl border border-[#DCE7E0] bg-white shadow-[0_12px_32px_rgba(23,73,61,0.06)] dark:border-slate-700 dark:bg-slate-800"
                        >
                            <CardHeader class="pb-4">
                                <CardTitle class="text-lg text-[#173F35] dark:text-slate-100">
                                    Profile Information
                                </CardTitle>
                                <CardDescription class="text-[#657C74] dark:text-slate-300">
                                    Update your name, email address, and phone number.
                                </CardDescription>
                            </CardHeader>

                            <CardContent>
                                <div
                                    v-if="mustVerifyEmail && !profile.email_verified_at"
                                    class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-500/40 dark:bg-amber-500/10"
                                >
                                    <p class="text-sm text-amber-800 dark:text-amber-200">
                                        Your email address is unverified.
                                    </p>
                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                        We sent you a verification email. If you did not receive it, resend below.
                                    </p>

                                    <Form
                                        v-bind="send.form()"
                                        class="mt-3 flex flex-wrap items-center gap-3"
                                        v-slot="{ processing }"
                                    >
                                        <Button
                                            :disabled="processing"
                                            size="sm"
                                            class="bg-[#17493D] text-white hover:bg-[#10362D]"
                                        >
                                            {{ status === 'verification-link-sent' ? 'Resend verification email' : 'Send verification email' }}
                                        </Button>

                                        <span
                                            v-if="status === 'verification-link-sent'"
                                            class="text-xs font-medium text-green-700 dark:text-emerald-300"
                                        >
                                            A new verification link has been sent to your email address.
                                        </span>
                                    </Form>
                                </div>

                                <form @submit.prevent="saveChanges" class="space-y-6">
                                    <div class="grid gap-5 md:grid-cols-2">
                                        <div class="grid gap-2 md:col-span-1">
                                            <Label for="name">Full name</Label>
                                            <Input
                                                id="name"
                                                v-model="form.name"
                                                class="mt-1 block w-full"
                                                required
                                                autocomplete="name"
                                                placeholder="Full name"
                                            />
                                            <InputError class="mt-1" :message="form.errors.name" />
                                        </div>

                                        <div class="grid gap-2 md:col-span-1">
                                            <Label for="email">Email address</Label>
                                            <Input
                                                id="email"
                                                v-model="form.email"
                                                type="email"
                                                class="mt-1 block w-full"
                                                required
                                                autocomplete="username"
                                                placeholder="Email address"
                                                :aria-invalid="!!form.errors.email"
                                            />
                                            <InputError class="mt-1" :message="form.errors.email" />
                                        </div>

                                        <div class="grid gap-2 md:col-span-2">
                                            <Label for="phone">Phone number</Label>
                                            <Input
                                                id="phone"
                                                v-model="form.phone"
                                                type="text"
                                                class="mt-1 block w-full"
                                                placeholder="Phone number"
                                            />
                                            <InputError class="mt-1" :message="form.errors.phone" />
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <Button
                                            :disabled="form.processing"
                                            data-test="update-profile-button"
                                            class="bg-[#17493D] text-white hover:bg-[#10362D]"
                                        >
                                            Save changes
                                        </Button>

                                        <Transition
                                            enter-active-class="transition ease-in-out"
                                            enter-from-class="opacity-0"
                                            leave-active-class="transition ease-in-out"
                                            leave-to-class="opacity-0"
                                        >
                                            <p
                                                v-show="form.recentlySuccessful"
                                                class="text-sm font-medium text-[#1B5B4B] dark:text-emerald-300"
                                            >
                                                Changes saved.
                                            </p>
                                        </Transition>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>

                        <Card
                            class="rounded-xl border border-[#F0D9D9] bg-white shadow-[0_12px_32px_rgba(23,73,61,0.06)] dark:border-red-900/60 dark:bg-slate-800"
                        >
                            <CardHeader class="pb-4">
                                <CardTitle class="text-lg text-[#173F35] dark:text-slate-100">
                                    Account Actions
                                </CardTitle>
                                <CardDescription class="text-[#657C74] dark:text-slate-300">
                                    Manage sensitive actions related to your account.
                                </CardDescription>
                            </CardHeader>

                            <CardContent>
                                <DeleteUser />
                            </CardContent>
                        </Card>
                    </div>

                    <div class="space-y-4">
                        <Card
                            class="overflow-hidden border-0 bg-[#17493D] shadow-[0_18px_40px_rgba(23,73,61,0.18)] dark:bg-slate-900 dark:shadow-[0_18px_40px_rgba(15,23,42,0.75)] rounded-xl"
                        >
                            <CardHeader class="pb-4">
                                <CardTitle class="text-lg text-white">
                                    Account Overview
                                </CardTitle>
                                <CardDescription class="text-white">
                                    A quick summary of your profile.
                                </CardDescription>
                            </CardHeader>

                            <CardContent class="space-y-3">
                                <div class="rounded-2xl p-4" style="background:rgba(255,255,255,0.1)">
                                    <div class="flex items-start gap-3">
                                        <UserRound class="mt-0.5 h-4 w-4 text-white" />
                                        <div>
                                            <p class="text-xs uppercase tracking-wide" style="color:#ffffff">
                                                Full name
                                            </p>
                                            <p class="mt-1 text-sm font-medium" style="color:#ffffff">
                                                {{ form.name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl p-4" style="background:rgba(255,255,255,0.1)">
                                    <div class="flex items-start gap-3">
                                        <Mail class="mt-0.5 h-4 w-4 text-white" />
                                        <div>
                                            <p class="text-xs uppercase tracking-wide" style="color:#ffffff">
                                                Email
                                            </p>
                                            <p class="mt-1 text-sm font-medium text-white break-all">
                                                {{ form.email }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl p-4" style="background:rgba(255,255,255,0.1)">
                                    <div class="flex items-start gap-3">
                                        <ShieldCheck class="mt-0.5 h-4 w-4 text-white" />
                                        <div>
                                            <p class="text-xs uppercase tracking-wide" style="color:#ffffff">
                                                Account status
                                            </p>
                                            <p class="mt-1 text-sm font-medium" style="color:#ffffff">
                                                {{ profile.email_verified_at ? 'Verified and active' : 'Pending email verification' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card
                            class="rounded-xl border border-[#DCE7E0] bg-white shadow-[0_12px_32px_rgba(23,73,61,0.06)] dark:border-slate-700 dark:bg-slate-800"
                        >
                            <CardHeader class="pb-4">
                                <CardTitle class="text-lg text-[#173F35] dark:text-slate-100">
                                    Helpful Notes
                                </CardTitle>
                                <CardDescription class="text-[#657C74] dark:text-slate-300">
                                    Keep your profile updated for a secure vendor experience.
                                </CardDescription>
                            </CardHeader>

                            <CardContent class="space-y-3">
                                <div
                                    class="rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4 dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <div class="flex items-start gap-3">
                                        <Mail class="mt-0.5 h-4 w-4 text-[#5E7A70] dark:text-slate-200" />
                                        <div>
                                            <p class="text-sm font-medium text-[#24463D] dark:text-slate-100">
                                                Use an active email
                                            </p>
                                            <p class="mt-1 text-sm text-[#60756D] dark:text-slate-300">
                                                Important store updates and verification links are sent to your email.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4 dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <div class="flex items-start gap-3">
                                        <Phone class="mt-0.5 h-4 w-4 text-[#5E7A70] dark:text-slate-200" />
                                        <div>
                                            <p class="text-sm font-medium text-[#24463D] dark:text-slate-100">
                                                Keep contact details current
                                            </p>
                                            <p class="mt-1 text-sm text-[#60756D] dark:text-slate-300">
                                                Updated account details help with store management and support issues.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4 dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <div class="flex items-start gap-3">
                                        <ShieldCheck class="mt-0.5 h-4 w-4 text-[#5E7A70] dark:text-slate-200" />
                                        <div>
                                            <p class="text-sm font-medium text-[#24463D] dark:text-slate-100">
                                                Security matters
                                            </p>
                                            <p class="mt-1 text-sm text-[#60756D] dark:text-slate-300">
                                                Matching account details make your overall vendor experience more secure.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

