<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'code',
        private string $direction = 'asc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['id', 'code', 'name', 'name_kana', 'category', 'maker', 'unit', 'price', 'cost', 'tax_rate', 'status', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'code';
        $sortDir = $this->direction === 'desc' ? 'desc' : 'asc';

        return Product::active()
            ->filtered($this->search)
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', '商品コード', '商品名', '商品名カナ', 'カテゴリ',
            '仕様・型番', 'メーカー', 'JANコード', '単位',
            '販売単価', '仕入単価', '税率', '在庫管理', '状態', '備考',
            '作成日時', '更新日時',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->code,
            $product->name,
            $product->name_kana ?? '',
            Product::CATEGORIES[$product->category] ?? '',
            $product->spec ?? '',
            $product->maker ?? '',
            $product->jan_code ?? '',
            $product->unit ?? '',
            $product->price !== null ? number_format($product->price, 2) : '',
            $product->cost !== null ? number_format($product->cost, 2) : '',
            Product::TAX_RATES[$product->tax_rate] ?? '',
            $product->has_stock ? '有' : '無',
            Product::STATUSES[$product->status] ?? '',
            $product->remarks ?? '',
            $product->created_at?->format('Y-m-d H:i:s') ?? '',
            $product->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
