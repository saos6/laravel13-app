<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import ProductForm from '@/components/ProductForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Prefill {
    name?: string;
    name_kana?: string;
    category?: string | null;
    spec?: string;
    maker?: string;
    unit?: string;
    price?: string;
    cost?: string;
    tax_rate?: string | null;
    has_stock?: boolean;
    status?: string;
    remarks?: string;
}

const props = defineProps<{
    categories: Record<string, string>;
    taxRates: Record<string, string>;
    statuses: Record<string, string>;
    prefill?: Prefill;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '商品マスタ', href: ProductController.index.url() },
    { title: '新規登録', href: ProductController.create.url() },
];

const p = props.prefill ?? {};

const form = useForm({
    code: '',
    name: p.name ?? '',
    name_kana: p.name_kana ?? '',
    category: p.category ?? (null as string | null),
    spec: p.spec ?? '',
    maker: p.maker ?? '',
    jan_code: '',
    unit: p.unit ?? '',
    price: p.price ?? '',
    cost: p.cost ?? '',
    tax_rate: p.tax_rate ?? (null as string | null),
    has_stock: p.has_stock ?? true,
    status: p.status ?? 'active',
    remarks: p.remarks ?? '',
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
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="text-xl font-bold">商品 新規登録</h1>
                    <span
                        v-if="prefill"
                        class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                        >複製元データ適用済み</span
                    >
                </div>
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
