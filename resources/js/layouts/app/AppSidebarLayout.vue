<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const flash = computed(
    () => page.props.flash as { success: string | null; error: string | null },
);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div
                v-if="flash.success || flash.error"
                class="flex flex-col gap-2 px-4 pt-4"
            >
                <Alert
                    v-if="flash.success"
                    class="border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
                >
                    <CheckCircle2 class="size-4" />
                    <AlertDescription>{{ flash.success }}</AlertDescription>
                </Alert>
                <Alert v-if="flash.error" variant="destructive">
                    <AlertCircle class="size-4" />
                    <AlertDescription>{{ flash.error }}</AlertDescription>
                </Alert>
            </div>
            <slot />
        </AppContent>
    </AppShell>
</template>
