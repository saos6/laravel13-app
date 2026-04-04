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

        $query = Dept::with('parent')
            ->active()
            ->filtered($this->search);

        if ($sortField === 'parent_id') {
            $query->leftJoin('depts as parents', 'depts.parent_id', '=', 'parents.id')
                ->orderBy('parents.name', $sortDir)
                ->select('depts.*');
        } else {
            $query->orderBy($sortField, $sortDir);
        }

        return $query->get();
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
