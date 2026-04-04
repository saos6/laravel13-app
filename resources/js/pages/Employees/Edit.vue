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

interface Employee {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    dept_id: number | null;
    dept: { id: number; name: string } | null;
    email: string | null;
}

const props = defineProps<{
    employee: Employee;
    depts: Dept[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '社員マスタ', href: EmployeeController.index.url() },
    { title: '編集', href: EmployeeController.edit.url(props.employee.id) },
];

const form = useForm({
    code: props.employee.code,
    name: props.employee.name,
    name_kana: props.employee.name_kana ?? '',
    dept_id: props.employee.dept_id ? String(props.employee.dept_id) : (null as string | null),
    email: props.employee.email ?? '',
});

function submit() {
    form.put(EmployeeController.update.url(props.employee.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`社員マスタ 編集 - ${employee.name}`" />

        <div class="max-w-2xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h1 class="mb-6 text-xl font-bold">社員 編集</h1>
                <EmployeeForm
                    :form="form"
                    :depts="depts"
                    :cancel-href="EmployeeController.index.url()"
                    submit-label="更新"
                    processing-label="更新中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
