<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $thisMonth = now()->startOfMonth();

        // ステータス別件数（全件）
        $statusCounts = Quote::active()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 今月の見積件数・合計金額
        $thisMonthStats = Quote::active()
            ->where('quote_date', '>=', $thisMonth)
            ->selectRaw('count(*) as count, coalesce(sum(total_amount), 0) as total')
            ->first();

        // 今月の受注金額
        $thisMonthAccepted = Quote::active()
            ->where('status', 'accepted')
            ->where('quote_date', '>=', $thisMonth)
            ->sum('total_amount');

        // 直近10件
        $recentQuotes = Quote::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->orderByDesc('quote_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'quote_number', 'customer_id', 'employee_id', 'quote_date', 'subject', 'status', 'total_amount']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'thisMonthCount' => (int) $thisMonthStats->count,
                'thisMonthTotal' => (float) $thisMonthStats->total,
                'thisMonthAccepted' => (float) $thisMonthAccepted,
                'sentCount' => (int) ($statusCounts['sent'] ?? 0),
                'acceptedCount' => (int) ($statusCounts['accepted'] ?? 0),
                'draftCount' => (int) ($statusCounts['draft'] ?? 0),
                'totalCount' => array_sum($statusCounts),
            ],
            'recentQuotes' => $recentQuotes,
            'statuses' => Quote::STATUSES,
        ]);
    }
}
