<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

defineProps<Props>();

const showLogoutModal = ref(false);

const openLogoutModal = () => {
    showLogoutModal.value = true;
};

const confirmLogout = () => {
    router.flushAll();
    router.post(logout());
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem @click="openLogoutModal">
        <LogOut class="mr-2 h-4 w-4" />
        Log out
    </DropdownMenuItem>

    <Dialog v-model:open="showLogoutModal">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Confirm Logout</DialogTitle>
                <DialogDescription>
                    Are you sure you want to log out of your account?
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="outline" @click="showLogoutModal = false">
                    Cancel
                </Button>
                <Button variant="destructive" @click="confirmLogout">
                    Log out
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
