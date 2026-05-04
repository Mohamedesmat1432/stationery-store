<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { Primitive, type PrimitiveProps } from "reka-ui"
import { cn } from "@/lib/utils"

interface SkeletonProps extends PrimitiveProps {
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<SkeletonProps>(), {
  as: 'div'
})
</script>

<template>
  <Primitive
    data-slot="skeleton"
    :as="as"
    :as-child="asChild"
    :class="cn('animate-pulse rounded-md bg-primary/10', props.class)"
  />
</template>
