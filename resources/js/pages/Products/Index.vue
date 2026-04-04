<script setup lang="ts">
import { router, Head, Link } from '@inertiajs/vue3';
import {
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Download,
    Plus,
    Pencil,
    Trash2,
    Columns3,
    Search,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Product {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    category: string | null;
    maker: string | null;
    unit: string | null;
    price: string | null;
    tax_rate: string | null;
    has_stock: boolean;
    status: string;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    products: {
        data: Product[];
        total: number;
        per_page: number;
        current_page: number;
        last_page: number;
        links: PaginationLink[];
        from: number | null;
        to: number | null;
    };
    filters: {
        search: string;
        sort: string;
        direction: string;
        per_page: string;
    };
    categories: Record<string, string>;
    taxRates: Record<string, string>;
    statuses: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '商品マスタ', href: ProductController.index.url() },
];

const search = ref(props.filters.search ?? '');
const perPage = ref(props.filters.per_page ?? '10');
const sortField = ref(props.filters.sort ?? 'code');
const sortDir = ref(props.filters.direction ?? 'asc');

type ColumnKey =
    | 'id'
    | 'code'
    | 'name'
    | 'name_kana'
    | 'category'
    | 'maker'
    | 'unit'
    | 'price'
    | 'tax_rate'
    | 'has_stock'
    | 'status'
    | 'created_at'
    | 'updated_at';

const COLUMNS_STORAGE_KEY = 'products.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    id: { label: 'ID', visible: false },
    code: { label: '商品コード', visible: true },
    name: { label: '商品名', visible: true },
    name_kana: { label: '商品名カナ', visible: false },
    category: { label: 'カテゴリ', visible: true },
    maker: { label: 'メーカー', visible: true },
    unit: { label: '単位', visible: true },
    price: { label: '販売単価', visible: true },
    tax_rate: { label: '税率', visible: true },
    has_stock: { label: '在庫管理', visible: false },
    status: { label: '状態', visible: true },
    created_at: { label: '作成日時', visible: false },
    updated_at: { label: '更新日時', visible: false },
};

function loadColumns(): Record<ColumnKey, { label: string; visible: boolean }> {
    try {
        const saved = localStorage.getItem(COLUMNS_STORAGE_KEY);

        if (!saved) {
            return defaultColumns;
        }

        const parsed = JSON.parse(saved) as Partial<Record<ColumnKey, boolean>>;

        return (Object.keys(defaultColumns) as ColumnKey[]).reduce(
            (acc, key) => {
                acc[key] = {
                    ...defaultColumns[key],
                    visible: parsed[key] ?? defaultColumns[key].visible,
                };

                return acc;
            },
            {} as Record<ColumnKey, { label: string; visible: boolean }>,
        );
    } catch {
        return defaultColumns;
    }
}

const columns = ref(loadColumns());

watch(
    columns,
    (val) => {
        const visibility = (Object.keys(val) as ColumnKey[]).reduce(
            (acc, key) => {
                acc[key] = val[key].visible;

                return acc;
            },
            {} as Partial<Record<ColumnKey, boolean>>,
        );
        localStorage.setItem(COLUMNS_STORAGE_KEY, JSON.stringify(visibility));
    },
    { deep: true },
);

const visibleColumns = computed(() =>
    (Object.keys(columns.value) as ColumnKey[]).filter(
        (k) => columns.value[k].visible,
    ),
);

function applyFilters() {
    router.get(
        ProductController.index.url(),
        {
            search: search.value,
            sort: sortField.value,
            direction: sortDir.value,
            per_page: perPage.value,
        },
        { preserveState: true, replace: true },
    );
}

function handleSearch() {
    applyFilters();
}

function toggleSort(field: string) {
    if (sortField.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDir.value = 'asc';
    }

    applyFilters();
}

function handlePerPageChange(value: string) {
    perPage.value = value;
    applyFilters();
}

function exportExcel() {
    const params = new URLSearchParams({
        search: search.value,
        sort: sortField.value,
        direction: sortDir.value,
    });
    window.location.href = `${ProductController.exportMethod.url()}?${params}`;
}

function deleteProduct(id: number, name: string) {
    if (confirm(`「${name}」を削除してもよろしいですか？`)) {
        router.delete(ProductController.destroy.url(id));
    }
}

function sortIcon(field: string): 'asc' | 'desc' | 'none' {
    if (sortField.value !== field) {
        return 'none';
    }

    return sortDir.value === 'asc' ? 'asc' : 'desc';
}

function paginationLabel(label: string): string {
    return label.replace(/&laquo;\s*/g, '«').replace(/\s*&raquo;/g, '»');
}

function formatPrice(val: string | null): string {
    if (val === null || val === '') {
        return '—';
    }

    return '¥' + Number(val).toLocaleString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="商品マスタ" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">商品マスタ</h1>
                <div class="flex gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm"
                                ><Columns3 class="mr-1 h-4 w-4" />列表示</Button
                            >
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-44">
                            <DropdownMenuLabel>表示する列</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuCheckboxItem
                                v-for="(col, key) in columns"
                                :key="key"
                                :checked="col.visible"
                                @update:checked="
                                    (v) =>
                                        (columns[key as ColumnKey].visible = v)
                                "
                            >
                                {{ col.label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button variant="outline" size="sm" @click="exportExcel"
                        ><Download class="mr-1 h-4 w-4" />Excel出力</Button
                    >
                    <Button size="sm" as-child>
                        <Link :href="ProductController.create.url()"
                            ><Plus class="mr-1 h-4 w-4" />新規登録</Link
                        >
                    </Button>
                </div>
            </div>

            <!-- 検索 -->
            <div class="flex items-center gap-2">
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="コード・商品名・メーカー・JANで検索..."
                        class="pl-8"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button variant="secondary" size="sm" @click="handleSearch"
                    >検索</Button
                >
                <Button
                    variant="ghost"
                    size="sm"
                    @click="
                        () => {
                            search = '';
                            handleSearch();
                        }
                    "
                    >クリア</Button
                >
            </div>

            <!-- 件数 -->
            <div
                class="flex items-center justify-between text-sm text-muted-foreground"
            >
                <span>
                    全
                    <strong class="text-foreground">{{
                        products.total
                    }}</strong>
                    件
                    <template v-if="products.from && products.to"
                        >（{{ products.from }}〜{{
                            products.to
                        }}
                        件表示）</template
                    >
                </span>
                <div class="flex items-center gap-2">
                    <span>表示件数：</span>
                    <Select
                        :model-value="perPage"
                        @update:model-value="handlePerPageChange"
                    >
                        <SelectTrigger class="h-8 w-24"
                            ><SelectValue
                        /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10件</SelectItem>
                            <SelectItem value="25">25件</SelectItem>
                            <SelectItem value="50">50件</SelectItem>
                            <SelectItem value="100">100件</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- テーブル -->
            <div class="overflow-x-auto rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50">
                        <tr>
                            <template v-for="(col, key) in columns" :key="key">
                                <th
                                    v-if="col.visible"
                                    class="px-4 py-3 text-left font-medium whitespace-nowrap select-none"
                                    :class="{
                                        'cursor-pointer': ![
                                            'has_stock',
                                        ].includes(key),
                                    }"
                                    @click="
                                        !['has_stock'].includes(key) &&
                                        toggleSort(key)
                                    "
                                >
                                    <span class="flex items-center gap-1">
                                        {{ col.label }}
                                        <template
                                            v-if="!['has_stock'].includes(key)"
                                        >
                                            <ArrowUp
                                                v-if="sortIcon(key) === 'asc'"
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowDown
                                                v-else-if="
                                                    sortIcon(key) === 'desc'
                                                "
                                                class="h-3.5 w-3.5"
                                            />
                                            <ArrowUpDown
                                                v-else
                                                class="h-3.5 w-3.5 opacity-40"
                                            />
                                        </template>
                                    </span>
                                </th>
                            </template>
                            <th
                                class="px-4 py-3 text-left font-medium whitespace-nowrap"
                            >
                                操作
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="border-t transition-colors hover:bg-muted/30"
                        >
                            <td
                                v-if="columns.id.visible"
                                class="px-4 py-3 text-muted-foreground"
                            >
                                {{ product.id }}
                            </td>
                            <td
                                v-if="columns.code.visible"
                                class="px-4 py-3 font-mono text-sm"
                            >
                                {{ product.code }}
                            </td>
                            <td
                                v-if="columns.name.visible"
                                class="px-4 py-3 font-medium"
                            >
                                {{ product.name }}
                            </td>
                            <td
                                v-if="columns.name_kana.visible"
                                class="px-4 py-3 text-muted-foreground"
                            >
                                {{ product.name_kana ?? '—' }}
                            </td>
                            <td
                                v-if="columns.category.visible"
                                class="px-4 py-3 text-muted-foreground"
                            >
                                {{
                                    product.category
                                        ? categories[product.category]
                                        : '—'
                                }}
                            </td>
                            <td
                                v-if="columns.maker.visible"
                                class="px-4 py-3 text-muted-foreground"
                            >
                                {{ product.maker ?? '—' }}
                            </td>
                            <td
                                v-if="columns.unit.visible"
                                class="px-4 py-3 text-center text-muted-foreground"
                            >
                                {{ product.unit ?? '—' }}
                            </td>
                            <td
                                v-if="columns.price.visible"
                                class="px-4 py-3 text-right tabular-nums"
                            >
                                {{ formatPrice(product.price) }}
                            </td>
                            <td
                                v-if="columns.tax_rate.visible"
                                class="px-4 py-3 text-center text-muted-foreground"
                            >
                                {{
                                    product.tax_rate
                                        ? taxRates[product.tax_rate]
                                        : '—'
                                }}
                            </td>
                            <td
                                v-if="columns.has_stock.visible"
                                class="px-4 py-3 text-center"
                            >
                                <Badge
                                    :variant="
                                        product.has_stock
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{ product.has_stock ? '有' : '無' }}
                                </Badge>
                            </td>
                            <td v-if="columns.status.visible" class="px-4 py-3">
                                <Badge
                                    :variant="
                                        product.status === 'active'
                                            ? 'default'
                                            : 'destructive'
                                    "
                                >
                                    {{ statuses[product.status] }}
                                </Badge>
                            </td>
                            <td
                                v-if="columns.created_at.visible"
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    product.created_at
                                        ? new Date(
                                              product.created_at,
                                          ).toLocaleString('ja-JP')
                                        : '—'
                                }}
                            </td>
                            <td
                                v-if="columns.updated_at.visible"
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    product.updated_at
                                        ? new Date(
                                              product.updated_at,
                                          ).toLocaleString('ja-JP')
                                        : '—'
                                }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                ProductController.edit.url(
                                                    product.id,
                                                )
                                            "
                                            ><Pencil class="h-4 w-4"
                                        /></Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive"
                                        @click="
                                            deleteProduct(
                                                product.id,
                                                product.name,
                                            )
                                        "
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td
                                :colspan="visibleColumns.length + 1"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                データがありません
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ページング -->
            <div
                v-if="products.last_page > 1"
                class="flex items-center justify-center gap-1"
            >
                <template v-for="link in products.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        variant="outline"
                        size="sm"
                        :class="{
                            'bg-primary text-primary-foreground hover:bg-primary/90':
                                link.active,
                        }"
                        @click="router.visit(link.url)"
                        >{{ paginationLabel(link.label) }}</Button
                    >
                    <Button v-else variant="outline" size="sm" disabled>{{
                        paginationLabel(link.label)
                    }}</Button>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
