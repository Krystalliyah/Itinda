<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { watch, h } from 'vue'

type VendorApproved = {
  name: string
  url: string
  login_id: string
}

type FlashProps = {
  success?: string | null
  error?: string | null
  warning?: string | null
  info?: string | null
  vendor_approved?: VendorApproved | null
}

const page = usePage()

watch(
  () => page.props.flash as FlashProps | undefined,
  (flash) => {
    if (!flash) return

    if (flash.vendor_approved) {
      const { name, url, login_id } = flash.vendor_approved
      toast.success(`${name} approved`, {
        duration: 10000,
        description: h('div', { style: 'display:flex;flex-direction:column;gap:6px;margin-top:4px' }, [
          h('a', {
            href: url,
            target: '_blank',
            rel: 'noopener noreferrer',
            style: 'color:#60a5fa;text-decoration:underline;font-weight:500'
          }, '🔗 Visit tenant store'),
          h('span', { style: 'font-size:0.85em;color:inherit;opacity:0.85' }, `Login: ${login_id}`),
        ]),
      })
    }

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
