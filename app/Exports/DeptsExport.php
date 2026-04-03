<?php

namespace App\Exports;

use App\Models\Dept;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeptsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'id',
        private string $direction = 'asc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['id', 'name', 'parent_id', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'id';
        $sortDir = $this->direction === 'desc' ? 'desc' : 'asc';

        return Dept::with('parent')
            ->active()
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($sortField, $sortDir)
            ->get();
    }

    public function headings(): array
    {
        return ['ID', '所属名', '親所属', '作成日時', '更新日時'];
    }

    public function map($dept): array
    {
        return [
            $dept->id,
            $dept->name,
            $dept->parent?->name ?? '',
            $dept->created_at?->format('Y-m-d H:i:s') ?? '',
            $dept->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
