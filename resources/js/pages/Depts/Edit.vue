<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Copy } from 'lucide-vue-next';
import DeptController from '@/actions/App/Http/Controllers/DeptController';
import DeptForm from '@/components/DeptForm.vue';
import { Button } from '@/components/ui/button';
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
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-xl font-bold">所属 編集</h1>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="DeptController.replicate.url(dept.id)">
                            <Copy class="mr-1.5 h-4 w-4" />この所属を複製
                        </Link>
                    </Button>
                </div>
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
