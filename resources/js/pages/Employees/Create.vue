<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import AppLayout from '@/layouts/AppLayout.vue';
import EmployeeForm from '@/components/EmployeeForm.vue';
import type { BreadcrumbItem } from '@/types';

interface Dept {
    id: number;
    name: string;
}

defineProps<{ depts: Dept[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '社員マスタ', href: EmployeeController.index.url() },
    { title: '新規登録', href: EmployeeController.create.url() },
];

const form = useForm({
    code: '',
    name: '',
    name_kana: '',
    dept_id: null as string | null,
    email: '',
});

function submit() {
    form.post(EmployeeController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="社員マスタ 新規登録" />

        <div class="max-w-2xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h1 class="mb-6 text-xl font-bold">社員 新規登録</h1>
                <EmployeeForm
                    :form="form"
                    :depts="depts"
                    :cancel-href="EmployeeController.index.url()"
                    submit-label="登録"
                    processing-label="登録中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
