<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>見積書 {{ $quote->quote_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'IPAexGothic', 'MS Gothic', 'Hiragino Kaku Gothic Pro', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
        background: #fff;
        padding: 20mm 18mm 20mm 18mm;
    }
    /* ─── ヘッダーエリア ─── */
    .header { margin-bottom: 12px; }
    .doc-title {
        font-size: 22px;
        font-weight: bold;
        letter-spacing: 6px;
        text-align: center;
        border-bottom: 2px solid #1a1a1a;
        padding-bottom: 6px;
        margin-bottom: 16px;
    }
    .header-body { width: 100%; }
    .header-body td { vertical-align: top; }
    /* 宛先 */
    .to-block { width: 55%; }
    .customer-name {
        font-size: 16px;
        font-weight: bold;
        border-bottom: 1px solid #333;
        padding-bottom: 2px;
        margin-bottom: 4px;
    }
    .honorific { font-size: 13px; }
    .subject-row {
        margin-top: 10px;
        font-size: 13px;
    }
    .subject-label { color: #555; margin-right: 6px; }
    /* 発行情報 */
    .from-block { width: 44%; text-align: right; }
    .meta-table { width: 100%; border-collapse: collapse; }
    .meta-table td { padding: 2px 4px; font-size: 11px; }
    .meta-label { color: #555; white-space: nowrap; }
    .meta-value { font-weight: bold; }
    .quote-number { font-size: 13px; font-weight: bold; }
    /* 有効期限 */
    .expiry { color: #c00; font-size: 10px; margin-top: 4px; }
    /* ─── 合計金額 ─── */
    .total-box {
        border: 2px solid #1a1a1a;
        display: inline-block;
        padding: 6px 20px;
        margin: 14px 0 14px 0;
        text-align: center;
    }
    .total-box .label { font-size: 11px; color: #555; margin-bottom: 2px; }
    .total-box .amount { font-size: 20px; font-weight: bold; }
    /* ─── 明細テーブル ─── */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 10.5px;
    }
    .items-table th {
        background: #2c2c2c;
        color: #fff;
        padding: 5px 6px;
        text-align: center;
        font-weight: bold;
        border: 1px solid #2c2c2c;
    }
    .items-table td {
        padding: 5px 6px;
        border: 1px solid #ccc;
        vertical-align: middle;
    }
    .items-table tbody tr:nth-child(even) { background: #f7f7f7; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    /* ─── 合計フッター ─── */
    .summary-table {
        width: 260px;
        margin-left: auto;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 11px;
    }
    .summary-table td {
        padding: 4px 8px;
        border: 1px solid #ccc;
    }
    .summary-table .s-label { color: #444; width: 130px; }
    .summary-table .s-value { text-align: right; font-weight: bold; }
    .summary-table .total-row td {
        background: #2c2c2c;
        color: #fff;
        font-size: 13px;
        font-weight: bold;
    }
    /* ─── 備考 ─── */
    .remarks-section { margin-top: 14px; }
    .remarks-label { font-weight: bold; color: #444; margin-bottom: 4px; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
    .remarks-body { font-size: 10.5px; white-space: pre-wrap; color: #333; margin-top: 4px; }
    /* ─── 担当者 ─── */
    .staff-row { margin-top: 8px; font-size: 10.5px; color: #555; }
</style>
</head>
<body>

<div class="doc-title">見　積　書</div>

<table class="header-body" cellspacing="0" cellpadding="0">
<tr>
    <!-- 宛先 -->
    <td class="to-block">
        <div class="customer-name">{{ $quote->customer?->name ?? '—' }} <span class="honorific">御中</span></div>
        <div class="subject-row">
            <span class="subject-label">件名：</span><strong>{{ $quote->subject }}</strong>
        </div>
        <div class="total-box" style="margin-top:14px;">
            <div class="label">お見積金額（税込）</div>
            <div class="amount">{{ '¥' . number_format($quote->total_amount) }}</div>
        </div>
    </td>
    <!-- 発行情報 -->
    <td class="from-block">
        <table class="meta-table" cellspacing="0" cellpadding="0">
            <tr>
                <td class="meta-label">見積番号</td>
                <td class="meta-value quote-number">{{ $quote->quote_number }}</td>
            </tr>
            <tr>
                <td class="meta-label">見積日</td>
                <td class="meta-value">{{ $quote->quote_date?->format('Y年m月d日') }}</td>
            </tr>
            @if($quote->expiry_date)
            <tr>
                <td class="meta-label">有効期限</td>
                <td class="meta-value expiry">{{ $quote->expiry_date->format('Y年m月d日') }}</td>
            </tr>
            @endif
            @if($quote->employee)
            <tr>
                <td class="meta-label">担当者</td>
                <td class="meta-value">{{ $quote->employee->name }}</td>
            </tr>
            @endif
        </table>
    </td>
</tr>
</table>

<!-- 明細 -->
<table class="items-table" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th style="width:4%">No.</th>
            <th style="width:28%">品名</th>
            <th style="width:16%">仕様</th>
            <th style="width:7%">数量</th>
            <th style="width:7%">単位</th>
            <th style="width:12%">単価</th>
            <th style="width:6%">税率</th>
            <th style="width:14%">金額</th>
            <th style="width:6%">備考</th>
        </tr>
    </thead>
    <tbody>
        @foreach($quote->items as $item)
        <tr>
            <td class="text-center">{{ $item->line_no }}</td>
            <td>{{ $item->product_name }}</td>
            <td>{{ $item->spec ?? '' }}</td>
            <td class="text-right">{{ rtrim(rtrim(number_format((float)$item->quantity, 2), '0'), '.') }}</td>
            <td class="text-center">{{ $item->unit ?? '' }}</td>
            <td class="text-right">¥{{ number_format((float)$item->unit_price) }}</td>
            <td class="text-center">{{ $item->tax_rate }}%</td>
            <td class="text-right">¥{{ number_format((float)$item->amount) }}</td>
            <td class="text-center">{{ $item->remarks ?? '' }}</td>
        </tr>
        @endforeach
        @for($i = count($quote->items); $i < 5; $i++)
        <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        @endfor
    </tbody>
</table>

<!-- 合計 -->
<table class="summary-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="s-label">小計</td>
        <td class="s-value">¥{{ number_format((float)$quote->subtotal) }}</td>
    </tr>
    @foreach($taxBreakdown as $rate => $amount)
    @if($amount > 0)
    <tr>
        <td class="s-label">消費税（{{ $rate }}%）</td>
        <td class="s-value">¥{{ number_format($amount) }}</td>
    </tr>
    @endif
    @endforeach
    <tr class="total-row">
        <td class="s-label">合計金額（税込）</td>
        <td class="s-value">¥{{ number_format((float)$quote->total_amount) }}</td>
    </tr>
</table>

@if($quote->remarks)
<div class="remarks-section">
    <div class="remarks-label">備考</div>
    <div class="remarks-body">{{ $quote->remarks }}</div>
</div>
@endif

</body>
</html>
