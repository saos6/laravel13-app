<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import CustomerForm from '@/components/CustomerForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Employee {
    id: number;
    code: string;
    name: string;
}

interface Prefill {
    name?: string;
    name_kana?: string;
    postal_code?: string;
    address?: string;
    phone?: string;
    fax?: string;
    email?: string;
    employee_id?: number | null;
    closing_day?: number | null;
    payment_cycle?: string | null;
    payment_day?: number | null;
    remarks?: string;
}

const props = defineProps<{
    employees: Employee[];
    paymentCycles: Record<string, string>;
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '得意先マスタ', href: CustomerController.index.url() },
    { title: '新規登録', href: CustomerController.create.url() },
];

const p = props.prefill ?? {};

const form = useForm({
    code: '',
    name: p.name ?? '',
    name_kana: p.name_kana ?? '',
    postal_code: p.postal_code ?? '',
    address: p.address ?? '',
    phone: p.phone ?? '',
    fax: p.fax ?? '',
    email: p.email ?? '',
    employee_id: p.employee_id
        ? String(p.employee_id)
        : (null as string | null),
    closing_day: p.closing_day
        ? String(p.closing_day)
        : (null as string | null),
    payment_cycle: p.payment_cycle ?? (null as string | null),
    payment_day: p.payment_day
        ? String(p.payment_day)
        : (null as string | null),
    remarks: p.remarks ?? '',
});

function submit() {
    form.post(CustomerController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="得意先マスタ 新規登録" />

        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="text-xl font-bold">得意先 新規登録</h1>
                    <span
                        v-if="prefill"
                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                        >複製元データ適用済み</span
                    >
                </div>
                <CustomerForm
                    :form="form"
                    :employees="employees"
                    :payment-cycles="paymentCycles"
                    :cancel-href="CustomerController.index.url()"
                    submit-label="登録"
                    processing-label="登録中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
