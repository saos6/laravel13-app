<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import DeptController from '@/actions/App/Http/Controllers/DeptController';
import DeptForm from '@/components/DeptForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Parent {
    id: number;
    name: string;
}

defineProps<{ parents: Parent[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '所属マスタ', href: DeptController.index.url() },
    { title: '新規登録', href: DeptController.create.url() },
];

const form = useForm({
    name: '',
    parent_id: null as string | null,
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
                <h1 class="mb-6 text-xl font-bold">所属 新規登録</h1>
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
