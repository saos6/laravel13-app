<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import DeptController from '@/actions/App/Http/Controllers/DeptController';
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

interface Dept {
    id: number;
    name: string;
    parent: { id: number; name: string } | null;
    created_at: string;
    updated_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    depts: {
        data: Dept[];
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
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '所属マスタ', href: DeptController.index.url() },
];

const search = ref(props.filters.search ?? '');
const perPage = ref(props.filters.per_page ?? '10');
const sortField = ref(props.filters.sort ?? 'id');
const sortDir = ref(props.filters.direction ?? 'asc');

type ColumnKey = 'id' | 'name' | 'parent' | 'created_at' | 'updated_at';

const columns = ref<Record<ColumnKey, { label: string; visible: boolean }>>({
    id: { label: 'ID', visible: true },
    name: { label: '所属名', visible: true },
    parent: { label: '親所属', visible: true },
    created_at: { label: '作成日時', visible: true },
    updated_at: { label: '更新日時', visible: false },
});

const visibleColumns = computed(() =>
    (Object.keys(columns.value) as ColumnKey[]).filter((k) => columns.value[k].visible),
);

function applyFilters() {
    router.get(
        DeptController.index.url(),
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
    window.location.href = `${DeptController.exportMethod.url()}?${params}`;
}

function deleteDept(id: number, name: string) {
    if (confirm(`「${name}」を削除してもよろしいですか？`)) {
        router.delete(DeptController.destroy.url(id));
    }
}

function sortIcon(field: string) {
    if (sortField.value !== field) return 'none';
    return sortDir.value === 'asc' ? 'asc' : 'desc';
}

const pageLinks = computed(() =>
    props.depts.links.filter((link) => {
        const label = link.label.replace(/&laquo;|&raquo;/g, '').trim();
        return label !== '';
    }),
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="所属マスタ" />

        <div class="flex flex-col gap-4 p-4">
            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">所属マスタ</h1>
                <div class="flex gap-2">
                    <!-- 列表示選択 -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                <Columns3 class="mr-1 h-4 w-4" />
                                列表示
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-40">
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

                    <!-- Excelエクスポート -->
                    <Button variant="outline" size="sm" @click="exportExcel">
                        <Download class="mr-1 h-4 w-4" />
                        Excel出力
                    </Button>

                    <!-- 新規登録 -->
                    <Button size="sm" as-child>
                        <Link :href="DeptController.create.url()">
                            <Plus class="mr-1 h-4 w-4" />
                            新規登録
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
                        placeholder="所属名で検索..."
                        class="pl-8"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button variant="secondary" size="sm" @click="handleSearch">検索</Button>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="
                        () => {
                            search = '';
                            handleSearch();
                        }
                    "
                >
                    クリア
                </Button>
            </div>

            <!-- 件数・ページあたり表示数 -->
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                    全 <strong class="text-foreground">{{ depts.total }}</strong> 件
                    <template v-if="depts.from && depts.to">
                        （{{ depts.from }}〜{{ depts.to }} 件表示）
                    </template>
                </span>
                <div class="flex items-center gap-2">
                    <span>表示件数：</span>
                    <Select :model-value="perPage" @update:model-value="handlePerPageChange">
                        <SelectTrigger class="h-8 w-24">
                            <SelectValue />
                        </SelectTrigger>
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
                            <th
                                v-if="columns.id.visible"
                                class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap"
                                @click="toggleSort('id')"
                            >
                                <span class="flex items-center gap-1">
                                    ID
                                    <ArrowUp v-if="sortIcon('id') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('id') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th
                                v-if="columns.name.visible"
                                class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap"
                                @click="toggleSort('name')"
                            >
                                <span class="flex items-center gap-1">
                                    所属名
                                    <ArrowUp v-if="sortIcon('name') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('name') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th
                                v-if="columns.parent.visible"
                                class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap"
                                @click="toggleSort('parent_id')"
                            >
                                <span class="flex items-center gap-1">
                                    親所属
                                    <ArrowUp v-if="sortIcon('parent_id') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('parent_id') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th
                                v-if="columns.created_at.visible"
                                class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap"
                                @click="toggleSort('created_at')"
                            >
                                <span class="flex items-center gap-1">
                                    作成日時
                                    <ArrowUp v-if="sortIcon('created_at') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('created_at') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th
                                v-if="columns.updated_at.visible"
                                class="cursor-pointer select-none px-4 py-3 text-left font-medium whitespace-nowrap"
                                @click="toggleSort('updated_at')"
                            >
                                <span class="flex items-center gap-1">
                                    更新日時
                                    <ArrowUp v-if="sortIcon('updated_at') === 'asc'" class="h-3.5 w-3.5" />
                                    <ArrowDown v-else-if="sortIcon('updated_at') === 'desc'" class="h-3.5 w-3.5" />
                                    <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-40" />
                                </span>
                            </th>
                            <th class="px-4 py-3 text-left font-medium whitespace-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="dept in depts.data"
                            :key="dept.id"
                            class="border-t transition-colors hover:bg-muted/30"
                        >
                            <td v-if="columns.id.visible" class="px-4 py-3 text-muted-foreground">
                                {{ dept.id }}
                            </td>
                            <td v-if="columns.name.visible" class="px-4 py-3 font-medium">
                                {{ dept.name }}
                            </td>
                            <td v-if="columns.parent.visible" class="px-4 py-3 text-muted-foreground">
                                {{ dept.parent?.name ?? '—' }}
                            </td>
                            <td v-if="columns.created_at.visible" class="px-4 py-3 text-muted-foreground whitespace-nowrap">
                                {{ dept.created_at ? new Date(dept.created_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td v-if="columns.updated_at.visible" class="px-4 py-3 text-muted-foreground whitespace-nowrap">
                                {{ dept.updated_at ? new Date(dept.updated_at).toLocaleString('ja-JP') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1">
                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                        <Link :href="DeptController.edit.url(dept.id)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-destructive hover:text-destructive"
                                        @click="deleteDept(dept.id, dept.name)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="depts.data.length === 0">
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
            <div v-if="depts.last_page > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in depts.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        variant="outline"
                        size="sm"
                        :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                        @click="router.visit(link.url)"
                        v-html="link.label"
                    />
                    <Button
                        v-else
                        variant="outline"
                        size="sm"
                        disabled
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
