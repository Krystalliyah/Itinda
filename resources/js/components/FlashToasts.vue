<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { watch } from 'vue'

type FlashProps = {
  success?: string | null
  error?: string | null
  warning?: string | null
  info?: string | null
}

const page = usePage()

watch(
  () => page.props.flash as FlashProps | undefined,
  (flash) => {
    console.log('flash changed:', flash)

    if (!flash) return

    if (flash.success) toast.success(flash.success)
    if (flash.error) toast.error(flash.error)
    if (flash.warning) toast.warning(flash.warning)
    if (flash.info) toast.info(flash.info)
  },
  { immediate: true, deep: true }
)
</script>

<template>
  <div class="hidden" />
</template>