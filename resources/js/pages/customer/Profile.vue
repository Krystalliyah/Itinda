<script setup lang="ts">
import { Head, Form, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BadgeCheck,
    Bell,
    Mail,
    MapPin,
    PencilLine,
    Phone,
    ShieldCheck,
    UserRound,
} from 'lucide-vue-next';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import Header from '@/components/Header.vue';
import Sidebar from '@/components/Sidebar.vue';
import CustomerNav from '@/components/navigation/CustomerNav.vue';
import CustomerNavIcons from '@/components/navigation/CustomerNavIcons.vue';
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
import { toast } from 'vue-sonner';

const { isCollapsed } = useSidebar();

const contentClass = computed(() => ({
    'dashboard-content': true,
    'sidebar-collapsed': isCollapsed.value,
}));

const page = usePage();
const user = page.props.auth.user as {
    name: string;
    email: string;
    email_verified_at?: string | null;
};

defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
}>();

const initials = computed(() => {
    return user.name
        ?.split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});

const handleSuccess = () => {
    toast.success('Changes saved.')
}
</script>

<template>
    <Head title="My Profile" />

    <div class="dashboard-wrapper">
        <Header />

        <Sidebar role="customer">
            <CustomerNav />
        </Sidebar>

        <main :class="contentClass">
            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <div class="mb-5">
                    <h1 class="text-2xl font-semibold tracking-tight text-[#163F35]">
                        My Profile
                    </h1>
                    <p class="mt-1 text-sm text-[#5F766E]">
                        Manage your personal information, account details, and security settings.
                    </p>
                </div>

                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_360px]">
                    <div class="space-y-4">
                        <Card class="border-[#DCE7E0] bg-gradient-to-br from-white to-[#F7FAF8] shadow-[0_12px_32px_rgba(23,73,61,0.06)]">
                            <CardContent class="p-6">
                                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex min-w-0 items-start gap-4">
                                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-[#EAF4EF] text-xl font-semibold text-[#17493D]">
                                            {{ initials }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h2 class="truncate text-2xl font-semibold tracking-tight text-[#163F35]">
                                                    {{ user.name }}
                                                </h2>

                                                <span
                                                    v-if="user.email_verified_at"
                                                    class="inline-flex items-center gap-1 rounded-full bg-[#EAF4EF] px-2.5 py-1 text-xs font-semibold text-[#1B5B4B]"
                                                >
                                                    <BadgeCheck class="h-3.5 w-3.5" />
                                                    Verified
                                                </span>

                                                <span
                                                    v-else
                                                    class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700"
                                                >
                                                    <Mail class="h-3.5 w-3.5" />
                                                    Unverified email
                                                </span>
                                            </div>

                                            <p class="mt-1 text-sm text-[#60756D]">
                                                {{ user.email }}
                                            </p>

                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center gap-1 rounded-full border border-[#D8E4DD] bg-white px-3 py-1 text-xs font-medium text-[#3C6658]">
                                                    <ShieldCheck class="h-3.5 w-3.5" />
                                                    Secure account
                                                </span>
                                                <span class="inline-flex items-center gap-1 rounded-full border border-[#D8E4DD] bg-white px-3 py-1 text-xs font-medium text-[#3C6658]">
                                                    <Bell class="h-3.5 w-3.5" />
                                                    Notifications enabled
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="border-[#DCE7E0] bg-white shadow-[0_12px_32px_rgba(23,73,61,0.06)]">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-lg text-[#173F35]">
                                    Profile Information
                                </CardTitle>
                                <CardDescription class="text-[#657C74]">
                                    Update your name and email address.
                                </CardDescription>
                            </CardHeader>

                            <CardContent>
                                <div
                                    v-if="mustVerifyEmail && !user.email_verified_at"
                                    class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3"
                                >
                                    <p class="text-sm text-amber-800">
                                        Your email address is unverified.
                                    </p>
                                    <p class="mt-1 text-xs text-amber-700">
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
                                            class="text-xs font-medium text-green-700"
                                        >
                                            A new verification link has been sent to your email address.
                                        </span>
                                    </Form>
                                </div>

                                <Form
                                    v-bind="ProfileController.update.form()"
                                    @success="handleSuccess"
                                    class="space-y-6"
                                    v-slot="{ errors, processing, recentlySuccessful }"
                                >
                                    <div class="grid gap-5 md:grid-cols-2">
                                        <div class="grid gap-2 md:col-span-1">
                                            <Label for="name">Full name</Label>
                                            <Input
                                                id="name"
                                                class="mt-1 block w-full"
                                                name="name"
                                                :default-value="user.name"
                                                required
                                                autocomplete="name"
                                                placeholder="Full name"
                                            />
                                            <InputError class="mt-1" :message="errors.name" />
                                        </div>

                                        <div class="grid gap-2 md:col-span-1">
                                            <Label for="email">Email address</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                class="mt-1 block w-full"
                                                name="email"
                                                :default-value="user.email"
                                                required
                                                autocomplete="username"
                                                placeholder="Email address"
                                                :aria-invalid="!!errors.email"
                                            />
                                            <InputError class="mt-1" :message="errors.email" />
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <Button
                                            :disabled="processing"
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
                                                v-show="recentlySuccessful"
                                                class="text-sm font-medium text-[#1B5B4B]"
                                            >
                                                Changes saved.
                                            </p>
                                        </Transition>
                                    </div>
                                </Form>
                            </CardContent>
                        </Card>

                        <Card class="border-[#F0D9D9] bg-white shadow-[0_12px_32px_rgba(23,73,61,0.06)]">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-lg text-[#173F35]">
                                    Account Actions
                                </CardTitle>
                                <CardDescription class="text-[#657C74]">
                                    Manage sensitive actions related to your account.
                                </CardDescription>
                            </CardHeader>

                            <CardContent>
                                <DeleteUser />
                            </CardContent>
                        </Card>
                    </div>

                    <div class="space-y-4">
                        <Card class="overflow-hidden border-0 bg-[#17493D] shadow-[0_18px_40px_rgba(23,73,61,0.18)]">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-lg text-white">
                                    Account Overview
                                </CardTitle>
                                <CardDescription style="color:rgba(255,255,255,0.85)">
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
                                                {{ user.name }}
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
                                            <p class="mt-1 text-sm font-medium break-all" style="color:#ffffff">
                                                {{ user.email }}
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
                                                {{ user.email_verified_at ? 'Verified and active' : 'Pending email verification' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="border-[#DCE7E0] bg-white shadow-[0_12px_32px_rgba(23,73,61,0.06)]">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-lg text-[#173F35]">
                                    Helpful Notes
                                </CardTitle>
                                <CardDescription class="text-[#657C74]">
                                    Keep your profile updated for a smoother pickup experience.
                                </CardDescription>
                            </CardHeader>

                            <CardContent class="space-y-3">
                                <div class="rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4">
                                    <div class="flex items-start gap-3">
                                        <Mail class="mt-0.5 h-4 w-4 text-[#5E7A70]" />
                                        <div>
                                            <p class="text-sm font-medium text-[#24463D]">
                                                Use an active email
                                            </p>
                                            <p class="mt-1 text-sm text-[#60756D]">
                                                Important order updates and verification links are sent to your email.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4">
                                    <div class="flex items-start gap-3">
                                        <Phone class="mt-0.5 h-4 w-4 text-[#5E7A70]" />
                                        <div>
                                            <p class="text-sm font-medium text-[#24463D]">
                                                Keep contact details current
                                            </p>
                                            <p class="mt-1 text-sm text-[#60756D]">
                                                Updated account details help avoid pickup and support issues later.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-[#E4ECE7] bg-[#FBFCFB] p-4">
                                    <div class="flex items-start gap-3">
                                        <MapPin class="mt-0.5 h-4 w-4 text-[#5E7A70]" />
                                        <div>
                                            <p class="text-sm font-medium text-[#24463D]">
                                                Profile consistency matters
                                            </p>
                                            <p class="mt-1 text-sm text-[#60756D]">
                                                Matching account details make your overall customer experience more seamless.
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