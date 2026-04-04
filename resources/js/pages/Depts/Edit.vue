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

interface Dept {
    id: number;
    name: string;
    parent_id: number | null;
    parent: { id: number; name: string } | null;
}

const props = defineProps<{
    dept: Dept;
    parents: Parent[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '所属マスタ', href: DeptController.index.url() },
    { title: '編集', href: DeptController.edit.url(props.dept.id) },
];

const form = useForm({
    name: props.dept.name,
    parent_id: props.dept.parent_id
        ? String(props.dept.parent_id)
        : (null as string | null),
});

function submit() {
    form.put(DeptController.update.url(props.dept.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`所属マスタ 編集 - ${dept.name}`" />

        <div class="max-w-2xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h1 class="mb-6 text-xl font-bold">所属 編集</h1>
                <DeptForm
                    :form="form"
                    :parents="parents"
                    :cancel-href="DeptController.index.url()"
                    submit-label="更新"
                    processing-label="更新中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
