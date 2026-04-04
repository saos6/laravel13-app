<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $search = '',
        private string $sort = 'code',
        private string $direction = 'asc',
    ) {}

    public function collection()
    {
        $allowedSorts = ['id', 'code', 'name', 'name_kana', 'dept_id', 'email', 'created_at', 'updated_at'];
        $sortField = in_array($this->sort, $allowedSorts) ? $this->sort : 'code';
        $sortDir = $this->direction === 'desc' ? 'desc' : 'asc';

        $query = Employee::with('dept')
            ->active()
            ->filtered($this->search);

        if ($sortField === 'dept_id') {
            $query->leftJoin('depts', 'employees.dept_id', '=', 'depts.id')
                ->orderBy('depts.name', $sortDir)
                ->select('employees.*');
        } else {
            $query->orderBy($sortField, $sortDir);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['ID', '社員コード', '氏名', '氏名カナ', '所属', 'メールアドレス', '作成日時', '更新日時'];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->code,
            $employee->name,
            $employee->name_kana ?? '',
            $employee->dept?->name ?? '',
            $employee->email ?? '',
            $employee->created_at?->format('Y-m-d H:i:s') ?? '',
            $employee->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
