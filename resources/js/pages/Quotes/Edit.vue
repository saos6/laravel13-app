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
interface QuoteItem {
    product_id: number | null;
    product_name: string;
    spec: string | null;
    quantity: string;
    unit: string | null;
    unit_price: string;
    tax_rate: string;
    amount: string;
    remarks: string | null;
}
interface Quote {
    id: number;
    quote_number: string;
    customer_id: number;
    employee_id: number | null;
    quote_date: string;
    expiry_date: string | null;
    subject: string;
    status: string;
    remarks: string | null;
    items: QuoteItem[];
}

const props = defineProps<{
    quote: Quote;
    customers: Customer[];
    employees: Employee[];
    products: ProductOption[];
    statuses: Record<string, string>;
    taxRates: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '見積', href: QuoteController.index.url() },
    { title: props.quote.quote_number, href: QuoteController.show.url(props.quote.id) },
    { title: '編集', href: QuoteController.edit.url(props.quote.id) },
];

const form = useForm({
    customer_id:  String(props.quote.customer_id),
    employee_id:  props.quote.employee_id ? String(props.quote.employee_id) : '',
    quote_date:   props.quote.quote_date,
    expiry_date:  props.quote.expiry_date ?? '',
    subject:      props.quote.subject,
    status:       props.quote.status,
    remarks:      props.quote.remarks ?? '',
    items: props.quote.items.map((item) => ({
        product_id:   item.product_id,
        product_name: item.product_name,
        spec:         item.spec ?? '',
        quantity:     item.quantity,
        unit:         item.unit ?? '',
        unit_price:   item.unit_price,
        tax_rate:     item.tax_rate,
        amount:       parseFloat(item.amount) || 0,
        remarks:      item.remarks ?? '',
    })),
});

function submit() {
    form.put(QuoteController.update.url(props.quote.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`見積 ${quote.quote_number} 編集`" />
        <div class="p-4">
            <h1 class="mb-6 text-2xl font-bold">見積 編集 <span class="font-mono text-muted-foreground">{{ quote.quote_number }}</span></h1>
            <QuoteForm
                :form="form"
                :customers="customers"
                :employees="employees"
                :products="products"
                :statuses="statuses"
                :tax-rates="taxRates"
                :cancel-href="QuoteController.show.url(quote.id)"
                submit-label="更新"
                processing-label="更新中..."
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
