<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const reason = ref('');

interface Props {
    open: boolean;
    title: string;
    description: string;
    details?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
    loading?: boolean;
    showReasonInput?: boolean;
    reasonLabel?: string;
    reasonPlaceholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    details: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'default',
    loading: false,
    showReasonInput: false,
    reasonLabel: 'Reason',
    reasonPlaceholder: 'Please provide a reason...',
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [reason?: string];
    cancel: [];
}>();

const handleConfirm = () => {
    if (props.showReasonInput) {
        // For modals with reason input, emit the reason
        emit('confirm', reason.value);
    } else {
        // For simple confirmation modals, just emit confirm and close
        emit('confirm');
        emit('update:open', false);
    }
};

const handleCancel = () => {
    reason.value = '';
    emit('cancel');
    emit('update:open', false);
};

const isConfirmDisabled = computed(() => {
    // Always disable if loading
    if (props.loading) return true;
    
    // If this modal requires a reason, disable until text is entered
    if (props.showReasonInput) {
        return !reason.value.trim();
    }
    
    // Otherwise, button is always enabled
    return false;
});

// Reset reason when modal closes
watch(() => props.open, (isOpen) => {
    if (!isOpen) {
        reason.value = '';
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle :class="{ 'confirmation-title-destructive': variant === 'destructive' }">{{ title }}</DialogTitle>
                <DialogDescription class="confirmation-description">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>
            <div v-if="details" class="confirmation-warning-panel">
                <div class="warning-icon" aria-hidden="true">!</div>
                <p>{{ details }}</p>
            </div>
            <div v-if="showReasonInput" class="mt-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ reasonLabel }}
                </label>
                <textarea
                    id="reason"
                    v-model="reason"
                    :placeholder="reasonPlaceholder"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    rows="3"
                    required
                ></textarea>
            </div>
            <DialogFooter class="gap-2">
                <Button variant="outline" @click="handleCancel" :disabled="props.loading">
                    {{ cancelText }}
                </Button>
                <Button :variant="variant" @click="handleConfirm" :disabled="isConfirmDisabled">
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.confirmation-title-destructive {
    color: #b91c1c;
}

.confirmation-description {
    color: #334155;
    font-weight: 600;
}
.dark .confirmation-description {
    color: #cbd5e1;
}

.confirmation-warning-panel {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    margin: 14px 0 0;
    border-radius: 14px;
    background: rgba(248, 113, 113, 0.12);
    border: 1px solid rgba(248, 113, 113, 0.24);
    color: #b91c1c;
    font-size: 0.95rem;
    line-height: 1.55;
}
.dark .confirmation-warning-panel {
    background: rgba(248, 113, 113, 0.18);
    border-color: rgba(248, 113, 113, 0.38);
    color: #fde2e2;
}
.warning-icon {
    min-width: 32px;
    min-height: 32px;
    display: grid;
    place-items: center;
    border-radius: 9999px;
    background: rgba(248, 113, 113, 0.16);
    color: #991b1b;
    font-weight: 700;
    font-size: 1rem;
}
.dark .warning-icon {
    background: rgba(248, 113, 113, 0.28);
    color: #fee2e2;
}
</style>
