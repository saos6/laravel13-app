<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'code',
        private string $direction = 'asc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['id', 'code', 'name', 'name_kana', 'closing_day', 'payment_cycle', 'payment_day', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'code';
        $sortDir = $this->direction === 'desc' ? 'desc' : 'asc';

        return Customer::with('employee')
            ->active()
            ->filtered($this->search)
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', '得意先コード', '得意先名', '得意先名カナ',
            '郵便番号', '住所', '電話番号', 'FAX', 'メールアドレス',
            '担当社員', '締め日', '支払いサイクル', '支払日', '備考',
            '作成日時', '更新日時',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->code,
            $customer->name,
            $customer->name_kana ?? '',
            $customer->postal_code ?? '',
            $customer->address ?? '',
            $customer->phone ?? '',
            $customer->fax ?? '',
            $customer->email ?? '',
            $customer->employee ? "[{$customer->employee->code}] {$customer->employee->name}" : '',
            $customer->closing_day_label,
            $customer->payment_cycle_label,
            $customer->payment_day_label,
            $customer->remarks ?? '',
            $customer->created_at?->format('Y-m-d H:i:s') ?? '',
            $customer->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
