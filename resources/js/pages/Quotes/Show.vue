<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import QuoteController from '@/actions/App/Http/Controllers/QuoteController';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Pencil } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface QuoteItem {
    id: number;
    line_no: number;
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
    customer: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    quote_date: string;
    expiry_date: string | null;
    subject: string;
    status: string;
    subtotal: string;
    tax_amount: string;
    total_amount: string;
    remarks: string | null;
    items: QuoteItem[];
}

const props = defineProps<{
    quote: Quote;
    statuses: Record<string, string>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: '見積', href: QuoteController.index.url() },
    { title: props.quote.quote_number, href: QuoteController.show.url(props.quote.id) },
];

const STATUS_VARIANT: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
    draft: 'secondary', sent: 'outline', accepted: 'default', rejected: 'destructive', expired: 'secondary',
};

function fmt(val: string | number): string {
    return '¥' + Number(val).toLocaleString('ja-JP');
}

function fmtDate(val: string | null): string {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('ja-JP');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`見積 ${quote.quote_number}`" />
        <div class="flex flex-col gap-4 p-4">

            <!-- ヘッダー -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold font-mono">{{ quote.quote_number }}</h1>
                    <Badge :variant="STATUS_VARIANT[quote.status] ?? 'secondary'" class="text-sm">
                        {{ statuses[quote.status] }}
                    </Badge>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="QuoteController.index.url()">一覧へ戻る</Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="QuoteController.edit.url(quote.id)"><Pencil class="mr-1 h-4 w-4" />編集</Link>
                    </Button>
                </div>
            </div>

            <!-- 基本情報 -->
            <div class="rounded-md border p-4">
                <h2 class="mb-3 text-sm font-semibold text-muted-foreground">基本情報</h2>
                <dl class="grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-3 text-sm">
                    <div>
                        <dt class="text-muted-foreground">得意先</dt>
                        <dd class="font-medium">{{ quote.customer?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">担当者</dt>
                        <dd>{{ quote.employee?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">見積日</dt>
                        <dd>{{ fmtDate(quote.quote_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">有効期限</dt>
                        <dd>{{ fmtDate(quote.expiry_date) }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-muted-foreground">件名</dt>
                        <dd class="font-medium">{{ quote.subject }}</dd>
                    </div>
                    <div v-if="quote.remarks" class="col-span-3">
                        <dt class="text-muted-foreground">備考</dt>
                        <dd class="whitespace-pre-wrap">{{ quote.remarks }}</dd>
                    </div>
                </dl>
            </div>

            <!-- 明細 -->
            <div class="rounded-md border p-4">
                <h2 class="mb-3 text-sm font-semibold text-muted-foreground">明細</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">#</th>
                                <th class="px-3 py-2 text-left font-medium">品名</th>
                                <th class="px-3 py-2 text-left font-medium">仕様</th>
                                <th class="px-3 py-2 text-right font-medium">数量</th>
                                <th class="px-3 py-2 text-left font-medium">単位</th>
                                <th class="px-3 py-2 text-right font-medium">単価</th>
                                <th class="px-3 py-2 text-center font-medium">税率</th>
                                <th class="px-3 py-2 text-right font-medium">金額</th>
                                <th class="px-3 py-2 text-left font-medium">備考</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in quote.items" :key="item.id" class="border-t">
                                <td class="px-3 py-2 text-muted-foreground">{{ item.line_no }}</td>
                                <td class="px-3 py-2 font-medium">{{ item.product_name }}</td>
                                <td class="px-3 py-2 text-muted-foreground">{{ item.spec ?? '—' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ Number(item.quantity).toLocaleString('ja-JP') }}</td>
                                <td class="px-3 py-2 text-muted-foreground">{{ item.unit ?? '' }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ fmt(item.unit_price) }}</td>
                                <td class="px-3 py-2 text-center text-muted-foreground">{{ item.tax_rate }}%</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ fmt(item.amount) }}</td>
                                <td class="px-3 py-2 text-muted-foreground">{{ item.remarks ?? '' }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t bg-muted/30 font-medium">
                            <tr>
                                <td colspan="7" class="px-3 py-2 text-right text-muted-foreground">小計</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ fmt(quote.subtotal) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="px-3 py-2 text-right text-muted-foreground">消費税</td>
                                <td class="px-3 py-2 text-right tabular-nums">{{ fmt(quote.tax_amount) }}</td>
                                <td></td>
                            </tr>
                            <tr class="text-base">
                                <td colspan="7" class="px-3 py-2 text-right">合計</td>
                                <td class="px-3 py-2 text-right tabular-nums font-bold">{{ fmt(quote.total_amount) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
