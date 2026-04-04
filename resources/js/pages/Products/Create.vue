<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import ProductForm from '@/components/ProductForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    categories: Record<string, string>;
    taxRates: Record<string, string>;
    statuses: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '商品マスタ', href: ProductController.index.url() },
    { title: '新規登録', href: ProductController.create.url() },
];

const form = useForm({
    code: '',
    name: '',
    name_kana: '',
    category: null as string | null,
    spec: '',
    maker: '',
    jan_code: '',
    unit: '',
    price: '',
    cost: '',
    tax_rate: null as string | null,
    has_stock: true,
    status: 'active',
    remarks: '',
});

function submit() {
    form.post(ProductController.store.url());
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="商品マスタ 新規登録" />
        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h1 class="mb-6 text-xl font-bold">商品 新規登録</h1>
                <ProductForm
                    :form="form"
                    :categories="categories"
                    :tax-rates="taxRates"
                    :statuses="statuses"
                    :cancel-href="ProductController.index.url()"
                    submit-label="登録"
                    processing-label="登録中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
