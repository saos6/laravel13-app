<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import ProductForm from '@/components/ProductForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Product {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    category: string | null;
    spec: string | null;
    maker: string | null;
    jan_code: string | null;
    unit: string | null;
    price: string | null;
    cost: string | null;
    tax_rate: string | null;
    has_stock: boolean;
    status: string;
    remarks: string | null;
}

const props = defineProps<{
    product: Product;
    categories: Record<string, string>;
    taxRates: Record<string, string>;
    statuses: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '商品マスタ', href: ProductController.index.url() },
    { title: '編集', href: ProductController.edit.url(props.product.id) },
];

const form = useForm({
    code: props.product.code,
    name: props.product.name,
    name_kana: props.product.name_kana ?? '',
    category: props.product.category ?? (null as string | null),
    spec: props.product.spec ?? '',
    maker: props.product.maker ?? '',
    jan_code: props.product.jan_code ?? '',
    unit: props.product.unit ?? '',
    price: props.product.price ?? '',
    cost: props.product.cost ?? '',
    tax_rate: props.product.tax_rate ?? (null as string | null),
    has_stock: props.product.has_stock,
    status: props.product.status,
    remarks: props.product.remarks ?? '',
});

function submit() {
    form.put(ProductController.update.url(props.product.id));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`商品マスタ 編集 - ${product.name}`" />
        <div class="max-w-4xl p-6">
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h1 class="mb-6 text-xl font-bold">商品 編集</h1>
                <ProductForm
                    :form="form"
                    :categories="categories"
                    :tax-rates="taxRates"
                    :statuses="statuses"
                    :cancel-href="ProductController.index.url()"
                    submit-label="更新"
                    processing-label="更新中..."
                    @submit="submit"
                />
            </div>
        </div>
    </AppLayout>
</template>
