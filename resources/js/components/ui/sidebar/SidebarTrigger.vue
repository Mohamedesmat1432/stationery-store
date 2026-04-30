<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { PanelLeftClose, PanelLeftOpen } from "lucide-vue-next"
import { cn } from "@/lib/utils"
import { Button } from '@/components/ui/button'
import { useSidebar } from "./utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()

const { isMobile, state, toggleSidebar } = useSidebar()
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :title="$t('Toggle sidebar')"
    :class="cn('h-7 w-7', props.class)"
    @click="toggleSidebar"
  >
    <PanelLeftOpen v-if="isMobile || state === 'collapsed'" class="rtl:rotate-180" />
    <PanelLeftClose v-else class="rtl:rotate-180" />
    <span class="sr-only">{{ $t('Toggle sidebar') }}</span>
  </Button>
</template>
