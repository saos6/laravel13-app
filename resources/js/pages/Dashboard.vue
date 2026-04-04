<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, TrendingUp, Send, CheckCircle } from 'lucide-vue-next';
import QuoteController from '@/actions/App/Http/Controllers/QuoteController';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';

interface RecentQuote {
    id: number;
    quote_number: string;
    customer: { id: number; name: string } | null;
    employee: { id: number; name: string } | null;
    quote_date: string;
    subject: string;
    status: string;
    total_amount: string;
}

const props = defineProps<{
    stats: {
        thisMonthCount: number;
        thisMonthTotal: number;
        thisMonthAccepted: number;
        sentCount: number;
        acceptedCount: number;
        draftCount: number;
        totalCount: number;
    };
    recentQuotes: RecentQuote[];
    statuses: Record<string, string>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const STATUS_VARIANT: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
    draft: 'secondary', sent: 'outline', accepted: 'default', rejected: 'destructive', expired: 'secondary',
};

function fmtAmount(val: number): string {
    return '¥' + val.toLocaleString('ja-JP');
}

function fmtDate(val: string): string {
    return new Date(val).toLocaleDateString('ja-JP');
}

const thisMonth = new Date().toLocaleDateString('ja-JP', { year: 'numeric', month: 'long' });
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4">

        <!-- KPIカード -->
        <div>
            <p class="mb-3 text-sm text-muted-foreground">{{ thisMonth }}の実績</p>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                <!-- 今月の見積件数 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の見積件数</p>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-3xl font-bold tabular-nums">{{ stats.thisMonthCount }}<span class="ml-1 text-base font-normal text-muted-foreground">件</span></p>
                    <p class="mt-1 text-xs text-muted-foreground">累計 {{ stats.totalCount }} 件</p>
                </div>

                <!-- 今月の見積金額 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の見積金額</p>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-2xl font-bold tabular-nums">{{ fmtAmount(stats.thisMonthTotal) }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">&nbsp;</p>
                </div>

                <!-- 今月の受注金額 -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">今月の受注金額</p>
                        <CheckCircle class="h-4 w-4 text-green-500" />
                    </div>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-green-600 dark:text-green-400">{{ fmtAmount(stats.thisMonthAccepted) }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">受注 {{ stats.acceptedCount }} 件（全期間）</p>
                </div>

                <!-- 送付済み（返答待ち） -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">送付済み（返答待ち）</p>
                        <Send class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-3xl font-bold tabular-nums">{{ stats.sentCount }}<span class="ml-1 text-base font-normal text-muted-foreground">件</span></p>
                    <p class="mt-1 text-xs text-muted-foreground">下書き {{ stats.draftCount }} 件</p>
                </div>

            </div>
        </div>

        <!-- 直近の見積 -->
        <div class="rounded-xl border bg-card">
            <div class="flex items-center justify-between border-b px-5 py-3">
                <h2 class="font-semibold">直近の見積</h2>
                <Link :href="QuoteController.index.url()" class="text-sm text-primary hover:underline">すべて見る →</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">見積番号</th>
                            <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">得意先</th>
                            <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">件名</th>
                            <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">見積日</th>
                            <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">ステータス</th>
                            <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">合計金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="quote in recentQuotes" :key="quote.id"
                            class="border-t transition-colors hover:bg-muted/30">
                            <td class="px-4 py-2.5">
                                <Link :href="QuoteController.show.url(quote.id)"
                                    class="font-mono text-primary hover:underline">
                                    {{ quote.quote_number }}
                                </Link>
                            </td>
                            <td class="px-4 py-2.5">{{ quote.customer?.name ?? '—' }}</td>
                            <td class="max-w-[200px] truncate px-4 py-2.5">{{ quote.subject }}</td>
                            <td class="px-4 py-2.5 text-muted-foreground whitespace-nowrap">{{ fmtDate(quote.quote_date) }}</td>
                            <td class="px-4 py-2.5">
                                <Badge :variant="STATUS_VARIANT[quote.status] ?? 'secondary'" class="text-xs">
                                    {{ statuses[quote.status] }}
                                </Badge>
                            </td>
                            <td class="px-4 py-2.5 text-right tabular-nums">{{ fmtAmount(Number(quote.total_amount)) }}</td>
                        </tr>
                        <tr v-if="recentQuotes.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">データがありません</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
