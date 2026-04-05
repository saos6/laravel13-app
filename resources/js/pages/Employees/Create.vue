<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import EmployeeForm from '@/components/EmployeeForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Dept {
    id: number;
    name: string;
}

interface Prefill {
    name?: string;
    name_kana?: string;
    dept_id?: string | null;
}

const props = defineProps<{
    depts: Dept[];
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '社員マスタ', href: EmployeeController.index.url() },
    { title: '新規登録', href: EmployeeController.create.url() },
];

const p = props.prefill;

const form = useForm({
    code: '',
    name: p?.name ?? '',
    name_kana: p?.name_kana ?? '',
    dept_id: p?.dept_id ?? (null as string | null),
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
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="text-xl font-bold">社員 新規登録</h1>
                    <span
                        v-if="prefill"
                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                        >複製元データ適用済み</span
                    >
                </div>
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
