<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import CustomerForm from '@/components/CustomerForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Employee {
    id: number;
    code: string;
    name: string;
}

defineProps<{
    employees: Employee[];
    paymentCycles: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '得意先マスタ', href: CustomerController.index.url() },
    { title: '新規登録', href: CustomerController.create.url() },
];

const form = useForm({
    code: '',
    name: '',
    name_kana: '',
    postal_code: '',
    address: '',
    phone: '',
    fax: '',
    email: '',
    employee_id: null as string | null,
    closing_day: null as string | null,
    payment_cycle: null as string | null,
    payment_day: null as string | null,
    remarks: '',
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
                <h1 class="mb-6 text-xl font-bold">得意先 新規登録</h1>
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
