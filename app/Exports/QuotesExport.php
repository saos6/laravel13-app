<?php

namespace App\Exports;

use App\Models\Quote;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuotesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search,
        private string $status,
        private string $sort,
        private string $direction,
    ) {}

    public function query()
    {
        return Quote::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->filtered($this->search, $this->status)
            ->orderBy($this->sort, $this->direction);
    }

    public function headings(): array
    {
        return ['見積番号', '得意先', '担当者', '見積日', '有効期限', '件名', 'ステータス', '小計', '消費税', '合計'];
    }

    public function map($quote): array
    {
        return [
            $quote->quote_number,
            $quote->customer?->name ?? '',
            $quote->employee?->name ?? '',
            $quote->quote_date?->format('Y/m/d') ?? '',
            $quote->expiry_date?->format('Y/m/d') ?? '',
            $quote->subject,
            Quote::STATUSES[$quote->status] ?? $quote->status,
            $quote->subtotal,
            $quote->tax_amount,
            $quote->total_amount,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
