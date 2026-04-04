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
    Eye,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { watch } from 'vue';
import QuoteController from '@/actions/App/Http/Controllers/QuoteController';
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

interface Quote {
    id: number;
    quote_number: string;
    customer: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    quote_date: string;
    expiry_date: string | null;
    subject: string;
    status: string;
    subtotal: string;
    tax_amount: string;
    total_amount: string;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    quotes: {
        data: Quote[];
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
        status: string;
        sort: string;
        direction: string;
        per_page: string;
    };
    statuses: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '見積', href: QuoteController.index.url() },
];

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const perPage = ref(props.filters.per_page ?? '10');
const sortField = ref(props.filters.sort ?? 'quote_number');
const sortDir = ref(props.filters.direction ?? 'desc');

type ColumnKey =
    | 'quote_number'
    | 'customer'
    | 'employee'
    | 'quote_date'
    | 'expiry_date'
    | 'subject'
    | 'status'
    | 'total_amount'
    | 'created_at'
    | 'updated_at';

const COLUMNS_STORAGE_KEY = 'quotes.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    quote_number: { label: '見積番号', visible: true },
    customer: { label: '得意先', visible: true },
    employee: { label: '担当者', visible: true },
    quote_date: { label: '見積日', visible: true },
    expiry_date: { label: '有効期限', visible: true },
    subject: { label: '件名', visible: true },
    status: { label: 'ステータス', visible: true },
    total_amount: { label: '合計金額', visible: true },
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

const nonSortable: ColumnKey[] = ['customer', 'employee'];

function applyFilters() {
    router.get(
        QuoteController.index.url(),
        {
            search: search.value,
            status: status.value,
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
    if (nonSortable.includes(field as ColumnKey)) {
        return;
    }

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
        status: status.value,
        sort: sortField.value,
        direction: sortDir.value,
    });
    window.location.href = `${QuoteController.exportMethod.url()}?${params}`;
}

function deleteQuote(id: number, quoteNumber: string) {
    if (confirm(`見積「${quoteNumber}」を削除してもよろしいですか？`)) {
        router.delete(QuoteController.destroy.url(id));
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

function formatAmount(val: string | null): string {
    if (val === null || val === '') {
        return '—';
    }

    return '¥' + Number(val).toLocaleString('ja-JP');
}

const STATUS_VARIANT: Record<
    string,
    'default' | 'secondary' | 'outline' | 'destructive'
> = {
    draft: 'secondary',
    sent: 'outline',
    accepted: 'default',
    rejected: 'destructive',
    expired: 'secondary',
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="見積" />
        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">見積</h1>
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
                        <Link :href="QuoteController.create.url()"
                            ><Plus class="mr-1 h-4 w-4" />新規作成</Link
                        >
                    </Button>
                </div>
            </div>

            <!-- 検索 -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="見積番号・件名・得意先で検索..."
                        class="pl-8"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Select
                    :model-value="status || '__all__'"
                    @update:model-value="
                        (v) => {
                            status = v === '__all__' ? '' : v;
                            applyFilters();
                        }
                    "
                >
                    <SelectTrigger class="h-9 w-36"
                        ><SelectValue placeholder="ステータス"
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="__all__">すべて</SelectItem>
                        <SelectItem
                            v-for="(label, key) in statuses"
                            :key="key"
                            :value="key"
                            >{{ label }}</SelectItem
                        >
                    </SelectContent>
                </Select>
                <Button variant="secondary" size="sm" @click="handleSearch"
                    >検索</Button
                >
                <Button
                    variant="ghost"
                    size="sm"
                    @click="
                        () => {
                            search = '';
                            status = '';
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
                    <strong class="text-foreground">{{ quotes.total }}</strong>
                    件
                    <template v-if="quotes.from && quotes.to"
                        >（{{ quotes.from }}〜{{ quotes.to }} 件表示）</template
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
                                        'cursor-pointer': !nonSortable.includes(
                                            key as ColumnKey,
                                        ),
                                    }"
                                    @click="toggleSort(key)"
                                >
                                    <span class="flex items-center gap-1">
                                        {{ col.label }}
                                        <template
                                            v-if="
                                                !nonSortable.includes(
                                                    key as ColumnKey,
                                                )
                                            "
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
                            v-for="quote in quotes.data"
                            :key="quote.id"
                            class="border-t transition-colors hover:bg-muted/30"
                        >
                            <td
                                v-if="columns.quote_number.visible"
                                class="px-4 py-3 font-mono text-sm"
                            >
                                {{ quote.quote_number }}
                            </td>
                            <td
                                v-if="columns.customer.visible"
                                class="px-4 py-3"
                            >
                                {{ quote.customer?.name ?? '—' }}
                            </td>
                            <td
                                v-if="columns.employee.visible"
                                class="px-4 py-3 text-muted-foreground"
                            >
                                {{ quote.employee?.name ?? '—' }}
                            </td>
                            <td
                                v-if="columns.quote_date.visible"
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    quote.quote_date
                                        ? new Date(
                                              quote.quote_date,
                                          ).toLocaleDateString('ja-JP')
                                        : '—'
                                }}
                            </td>
                            <td
                                v-if="columns.expiry_date.visible"
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    quote.expiry_date
                                        ? new Date(
                                              quote.expiry_date,
                                          ).toLocaleDateString('ja-JP')
                                        : '—'
                                }}
                            </td>
                            <td
                                v-if="columns.subject.visible"
                                class="max-w-xs truncate px-4 py-3"
                            >
                                {{ quote.subject }}
                            </td>
                            <td v-if="columns.status.visible" class="px-4 py-3">
                                <Badge
                                    :variant="
                                        STATUS_VARIANT[quote.status] ??
                                        'secondary'
                                    "
                                >
                                    {{ statuses[quote.status] }}
                                </Badge>
                            </td>
                            <td
                                v-if="columns.total_amount.visible"
                                class="px-4 py-3 text-right tabular-nums"
                            >
                                {{ formatAmount(quote.total_amount) }}
                            </td>
                            <td
                                v-if="columns.created_at.visible"
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    quote.created_at
                                        ? new Date(
                                              quote.created_at,
                                          ).toLocaleString('ja-JP')
                                        : '—'
                                }}
                            </td>
                            <td
                                v-if="columns.updated_at.visible"
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    quote.updated_at
                                        ? new Date(
                                              quote.updated_at,
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
                                                QuoteController.show.url(
                                                    quote.id,
                                                )
                                            "
                                            ><Eye class="h-4 w-4"
                                        /></Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                QuoteController.edit.url(
                                                    quote.id,
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
                                            deleteQuote(
                                                quote.id,
                                                quote.quote_number,
                                            )
                                        "
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="quotes.data.length === 0">
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
                v-if="quotes.last_page > 1"
                class="flex items-center justify-center gap-1"
            >
                <template v-for="link in quotes.links" :key="link.label">
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
