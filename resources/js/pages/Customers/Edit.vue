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

interface Customer {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    postal_code: string | null;
    address: string | null;
    phone: string | null;
    fax: string | null;
    email: string | null;
    employee_id: number | null;
    employee: { id: number; code: string; name: string } | null;
    closing_day: number | null;
    payment_cycle: string | null;
    payment_day: number | null;
    remarks: string | null;
}

const props = defineProps<{
    customer: Customer;
    employees: Employee[];
    paymentCycles: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '得意先マスタ', href: CustomerController.index.url() },
    { title: '編集', href: CustomerController.edit.url(props.customer.id) },
];

const form = useForm({
    code: props.customer.code,
    name: props.customer.name,
    name_kana: props.customer.name_kana ?? '',
    postal_code: props.customer.postal_code ?? '',
    address: props.customer.address ?? '',
    phone: props.customer.phone ?? '',
    fax: props.customer.fax ?? '',
    email: props.customer.email ?? '',
    employee_id: props.customer.employee_id
        ? String(props.customer.employee_id)
        : (null as string | null),
    closing_day: props.customer.closing_day
        ? String(props.customer.closing_day)
        : (null as string | null),
    payment_cycle: props.customer.payment_cycle ?? (null as string | null),
    payment_day: props.customer.payment_day
        ? String(props.customer.payment_day)
        : (null as string | null),
    remarks: props.customer.remarks ?? '',
});

function submit() {
    form.put(CustomerController.update.url(props.customer.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`得意先マスタ 編集 - ${customer.name}`" />

        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h1 class="mb-6 text-xl font-bold">得意先 編集</h1>
                <CustomerForm
                    :form="form"
                    :employees="employees"
                    :payment-cycles="paymentCycles"
                    :cancel-href="CustomerController.index.url()"
                    submit-label="更新"
                    processing-label="更新中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
