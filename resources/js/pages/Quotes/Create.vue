<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import QuoteController from '@/actions/App/Http/Controllers/QuoteController';
import QuoteForm from '@/components/QuoteForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Customer {
    id: number;
    name: string;
}
interface Employee {
    id: number;
    name: string;
}
interface ProductOption {
    id: number;
    code: string;
    name: string;
    spec: string | null;
    unit: string | null;
    price: string;
    tax_rate: string;
}
interface PrefillItem {
    product_id: number | null;
    product_name: string;
    spec: string;
    quantity: string;
    unit: string;
    unit_price: string;
    tax_rate: string;
    amount: number;
    remarks: string;
}
interface Prefill {
    customer_id?: string;
    employee_id?: string;
    subject?: string;
    remarks?: string;
    items?: PrefillItem[];
}

const props = defineProps<{
    customers: Customer[];
    employees: Employee[];
    products: ProductOption[];
    statuses: Record<string, string>;
    taxRates: Record<string, string>;
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '見積', href: QuoteController.index.url() },
    { title: '新規作成', href: QuoteController.create.url() },
];

const today = new Date().toISOString().slice(0, 10);
const p = props.prefill;

const defaultItem = {
    product_id: null as number | null,
    product_name: '',
    spec: '',
    quantity: '1',
    unit: '',
    unit_price: '0',
    tax_rate: '10',
    amount: 0,
    remarks: '',
};

const form = useForm({
    customer_id: p?.customer_id ?? '',
    employee_id: p?.employee_id ?? '',
    quote_date: today,
    expiry_date: '',
    subject: p?.subject ?? '',
    status: 'draft',
    remarks: p?.remarks ?? '',
    items: p?.items?.length ? p.items : [{ ...defaultItem }],
});

function submit() {
    form.post(QuoteController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="見積 新規作成" />
        <div class="p-4">
            <div class="mb-6 flex items-center gap-3">
                <h1 class="text-2xl font-bold">見積 新規作成</h1>
                <span
                    v-if="prefill"
                    class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                    >複製元データ適用済み</span
                >
            </div>
            <QuoteForm
                :form="form"
                :customers="customers"
                :employees="employees"
                :products="products"
                :statuses="statuses"
                :tax-rates="taxRates"
                :cancel-href="QuoteController.index.url()"
                submit-label="登録"
                processing-label="登録中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
