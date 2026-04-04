<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import QuoteController from '@/actions/App/Http/Controllers/QuoteController';
import AppLayout from '@/layouts/AppLayout.vue';
import QuoteForm from '@/components/QuoteForm.vue';
import type { BreadcrumbItem } from '@/types';

interface Customer { id: number; name: string; }
interface Employee { id: number; name: string; }
interface ProductOption {
    id: number; code: string; name: string;
    spec: string | null; unit: string | null;
    price: string | null; tax_rate: string | null;
}

defineProps<{
    customers: Customer[];
    employees: Employee[];
    products: ProductOption[];
    statuses: Record<string, string>;
    taxRates: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '見積', href: QuoteController.index.url() },
    { title: '新規作成', href: QuoteController.create.url() },
];

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    customer_id:  '',
    employee_id:  '',
    quote_date:   today,
    expiry_date:  '',
    subject:      '',
    status:       'draft',
    remarks:      '',
    items: [
        {
            product_id: null as number | null,
            product_name: '',
            spec: '',
            quantity: '1',
            unit: '',
            unit_price: '0',
            tax_rate: '10',
            amount: 0,
            remarks: '',
        },
    ],
});

function submit() {
    form.post(QuoteController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="見積 新規作成" />
        <div class="p-4">
            <h1 class="mb-6 text-2xl font-bold">見積 新規作成</h1>
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
