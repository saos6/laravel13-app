<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import DeptController from '@/actions/App/Http/Controllers/DeptController';
import DeptForm from '@/components/DeptForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Parent {
    id: number;
    name: string;
}

interface Prefill {
    parent_id?: string | null;
}

const props = defineProps<{
    parents: Parent[];
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '所属マスタ', href: DeptController.index.url() },
    { title: '新規登録', href: DeptController.create.url() },
];

const p = props.prefill;

const form = useForm({
    name: '',
    parent_id: p?.parent_id ?? (null as string | null),
});

function submit() {
    form.post(DeptController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="所属マスタ 新規登録" />

        <div class="max-w-2xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="text-xl font-bold">所属 新規登録</h1>
                    <span
                        v-if="prefill"
                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                        >複製元データ適用済み</span
                    >
                </div>
                <DeptForm
                    :form="form"
                    :parents="parents"
                    :cancel-href="DeptController.index.url()"
                    submit-label="登録"
                    processing-label="登録中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
