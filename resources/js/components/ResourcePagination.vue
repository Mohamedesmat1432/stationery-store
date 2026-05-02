<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';

interface Props {
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    total: number;
    count: number;
    resourceName?: string;
}

withDefaults(defineProps<Props>(), {
    resourceName: 'results',
});

const { t } = useI18n();

const translateLabel = (label: string) => {
    if (!label) {
        return label;
    }

    if (label.includes('Previous')) {
        return label.replace('Previous', t('Previous'));
    }

    if (label.includes('Next')) {
        return label.replace('Next', t('Next'));
    }

    return label;
};
</script>

<template>
    <div class="mt-4 flex items-center justify-between" v-if="total > 0">
        <span class="text-sm text-muted-foreground">
            {{
                $t('Showing {count} of {total} {resource}', {
                    count,
                    total,
                    resource: $t(resourceName),
                })
            }}
        </span>
        <div class="flex gap-2">
            <template v-for="(link, i) in links" :key="i">
                <Button
                    v-if="link.url"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    as-child
                >
                    <Link
                        :href="link.url"
                        v-html="translateLabel(link.label)"
                    ></Link>
                </Button>
                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    disabled
                    v-html="translateLabel(link.label)"
                >
                </Button>
            </template>
        </div>
    </div>
</template>
