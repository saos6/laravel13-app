<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ArrowUpDown, ArrowUp, ArrowDown, Download, Plus, Pencil, Trash2, Columns3, Search } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Customer {
    id: number;
    code: string;
    name: string;
    name_kana: string | null;
    phone: string | null;
    employee: { id: number; code: string; name: string } | null;
    closing_day: number | null;
    payment_cycle: string | null;
    payment_day: number | null;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    customers: {
        data: Customer[];
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
    paymentCycles: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '得意先マスタ', href: CustomerController.index.url() },
];

const search = ref(props.filters.search ?? '');
const perPage = ref(props.filters.per_page ?? '10');
const sortField = ref(props.filters.sort ?? 'code');
const sortDir = ref(props.filters.direction ?? 'asc');

type ColumnKey = 'id' | 'code' | 'name' | 'name_kana' | 'phone' | 'employee' | 'closing_day' | 'payment_cycle' | 'payment_day' | 'created_at' | 'updated_at';

const COLUMNS_STORAGE_KEY = 'customers.columns';

const defaultColumns: Record<ColumnKey, { label: string; visible: boolean }> = {
    id: { label: 'ID', visible: false },
    code: { label: '得意先コード', visible: true },
    name: { label: '得意先名', visible: true },
    name_kana: { label: '得意先名カナ', visible: false },
    phone: { label: '電話番号', visible: true },
    employee: { label: '担当社員', visible: true },
    closing_day: { label: '締め日', visible: true },
    payment_cycle: { label: '支払いサイクル', visible: true },
    payment_day: { label: '支払日', visible: true },
    created_at: { label: '作成日時', visible: false },
    updated_at: { label: '更新日時', visible: false },
};

function loadColumns(): Record<ColumnKey, { label: string; visible: boolean }> {
    try {
        const saved = localStorage.getItem(COLUMNS_STORAGE_KEY);
        if (!saved) return defaultColumns;
        const parsed = JSON.parse(saved) as Partial<Record<ColumnKey, boolean>>;
        return (Object.keys(defaultColumns) as ColumnKey[]).reduce(
            (acc, key) => {
                acc[key] = { ...defaultColumns[key], visible: parsed[key] ?? defaultColumns[key].visible };
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
            (acc, key) => { acc[key] = val[key].visible; return acc; },
            {} as Partial<Record<ColumnKey, boolean>>,
        );
        localStorage.setItem(COLUMNS_STORAGE_KEY, JSON.stringify(visibility));
    },
    { deep: true },
);

const visibleColumns = computed(() =>
    (Object.keys(columns.value) as ColumnKey[]).filter((k) => columns.value[k].visible),
);

function applyFilters() {
    router.get(
        CustomerController.index.url(),
        { search: search.value, sort: sortField.value, direction: sortDir.value, per_page: perPage.value },
        { preserveState: true, replace: true },
    );
}

function handleSearch() { applyFilters(); }

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
    const params = new URLSearchParams({ search: search.value, sort: sortField.value, direction: sortDir.value });
    window.location.href = `${CustomerController.exportMethod.url()}?${params}`;
}

function deleteCustomer(id: number, name: string) {
    if (confirm(`「${name}」を削除してもよろしいですか？`)) {
        router.delete(CustomerController.destroy.url(id));
    }
}

function sortIcon(field: string): 'asc' | 'desc' | 'none' {
    if (sortField.value !== field) return 'none';
    return sortDir.value === 'asc' ? 'asc' : 'desc';
}

function paginationLabel(label: string): string {
    return label.replace(/&laquo;\s*/g, '«').replace(/\s*&raquo;/g, '»');
}

function dayLabel(day: number | null): string {
    if (day === null) return '—';
    return day === 31 ? '末日' : `${day}日`;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="得意先マスタ" />

        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">得意先マスタ</h1>
                <div class="flex gap-2">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                <Columns3 class="mr-1 h-4 w-4" />列表示
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuLabel>表示する列</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuCheckboxItem
                                v-for="(col, key) in columns"
                                :key="key"
                                :checked="col.visible"
                                @update:checked="(v) => (columns[key as ColumnKey].visible = v)"
                            >
                                {{ col.label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Button variant="outline" size="sm" @click="exportExcel">
                        <Download class="mr-1 h-4 w-4" />Excel出力
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="CustomerController.create.url()">
                            <Plus class="mr-1 h-4 w-4" />新規登録
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- 検索バー -->
            <div class="flex items-center gap-2">
                <div class="relative max-w-sm flex-1">
                    <Search class="absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="コード・得意先名・カナ・電話・メールで検索..."
                        class="pl-8"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button variant="secondary" size="sm" @click="handleSearch">検索</Button>
                <Button variant="ghost" size="sm" @click="() => { search = ''; handleSearch(); }">クリア</Button>
            </div>

            <!-- 件数・ページあたり表示数 -->
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                    全 <strong class="text-foreground">{{ customers.total }}</strong> 件
                    <template v-if="customers.from && customers.to">
                        （{{ customers.from }}〜{{ customers.to }} 件表示）
                    </template>
                </span>
                <div class="flex items-center gap-2">
                    <span>表示件数：</span>
                    <Select :model-value="perPage" @update:model-value="handlePerPageChange">
                        <SelectTrigger class="h-8 w-24"><SelectValue /></SelectTrigger>
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
                            <th v-if="columns.id.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('id')">
                                <span class="flex items-center gap-1">ID
                                    <ArrowUp v-if="sortIcon('id') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('id') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th v-if="columns.code.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('code')">
                                <span class="flex items-center gap-1">得意先コード
                                    <ArrowUp v-if="sortIcon('code') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('code') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th v-if="columns.name.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('name')">
                                <span class="flex items-center gap-1">得意先名
                                    <ArrowUp v-if="sortIcon('name') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('name') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th v-if="columns.name_kana.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('name_kana')">
                                <span class="flex items-center gap-1">得意先名カナ
                                    <ArrowUp v-if="sortIcon('name_kana') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('name_kana') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th v-if="columns.phone.visible" class="px-4 py-3 text-left font-medium whitespace-nowrap">電話番号</th>
                            <th v-if="columns.employee.visible" class="px-4 py-3 text-left font-medium whitespace-nowrap">担当社員</th>
                            <th v-if="columns.closing_day.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('closing_day')">
                                <span class="flex items-center gap-1">締め日
                                    <ArrowUp v-if="sortIcon('closing_day') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('closing_day') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th v-if="columns.payment_cycle.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('payment_cycle')">
                                <span class="flex items-center gap-1">支払いサイクル
                                    <ArrowUp v-if="sortIcon('payment_cycle') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('payment_cycle') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th v-if="columns.payment_day.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('payment_day')">
                                <span class="flex items-center gap-1">支払日
                                    <ArrowUp v-if="sortIcon('payment_day') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('payment_day') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th v-if="columns.created_at.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('created_at')">
                                <span class="flex items-center gap-1">作成日時
                                    <ArrowUp v-if="sortIcon('created_at') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('created_at') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th v-if="columns.updated_at.visible" class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap" @click="toggleSort('updated_at')">
                                <span class="flex items-center gap-1">更新日時
                                    <ArrowUp v-if="sortIcon('updated_at') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('updated_at') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th class="px-4 py-3 text-left font-medium whitespace-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="customer in customers.data" :key="customer.id" class="border-t transition-colors hover:bg-muted/30">
                            <td v-if="columns.id.visible" class="px-4 py-3 text-muted-foreground">{{ customer.id }}</td>
                            <td v-if="columns.code.visible" class="px-4 py-3 font-mono text-sm">{{ customer.code }}</td>
                            <td v-if="columns.name.visible" class="px-4 py-3 font-medium">{{ customer.name }}</td>
                            <td v-if="columns.name_kana.visible" class="px-4 py-3 text-muted-foreground">{{ customer.name_kana ?? '—' }}</td>
                            <td v-if="columns.phone.visible" class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ customer.phone ?? '—' }}</td>
                            <td v-if="columns.employee.visible" class="px-4 py-3 text-muted-foreground whitespace-nowrap">
                                {{ customer.employee ? `[${customer.employee.code}] ${customer.employee.name}` : '—' }}
                            </td>
                            <td v-if="columns.closing_day.visible" class="px-4 py-3 text-muted-foreground text-center whitespace-nowrap">{{ dayLabel(customer.closing_day) }}</td>
                            <td v-if="columns.payment_cycle.visible" class="px-4 py-3 text-muted-foreground whitespace-nowrap">
                                {{ customer.payment_cycle ? paymentCycles[customer.payment_cycle] : '—' }}
                            </td>
                            <td v-if="columns.payment_day.visible" class="px-4 py-3 text-muted-foreground text-center whitespace-nowrap">{{ dayLabel(customer.payment_day) }}</td>
                            <td v-if="columns.created_at.visible" class="px-4 py-3 text-muted-foreground whitespace-nowrap">
                                {{ customer.created_at ? new Date(customer.created_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td v-if="columns.updated_at.visible" class="px-4 py-3 text-muted-foreground whitespace-nowrap">
                                {{ customer.updated_at ? new Date(customer.updated_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="CustomerController.edit.url(customer.id)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:text-destructive"
                                        @click="deleteCustomer(customer.id, customer.name)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customers.data.length === 0">
                            <td :colspan="visibleColumns.length + 1" class="px-4 py-10 text-center text-muted-foreground">
                                データがありません
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ページング -->
            <div v-if="customers.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in customers.links" :key="link.label">
                    <Button
                        v-if="link.url" variant="outline" size="sm"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        @click="router.visit(link.url)"
                    >{{ paginationLabel(link.label) }}</Button>
                    <Button v-else variant="outline" size="sm" disabled>{{ paginationLabel(link.label) }}</Button>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
