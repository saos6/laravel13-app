<?php

namespace App\Http\Controllers;

use App\Exports\EmployeesExport;
use App\Http\Requests\EmployeeRequest;
use App\Models\Dept;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['id', 'code', 'name', 'name_kana', 'dept_id', 'email', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'code';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search = $request->get('search', '');

        $query = Employee::with('dept')
            ->active()
            ->filtered($search);

        if ($sort === 'dept_id') {
            $query->leftJoin('depts', 'employees.dept_id', '=', 'depts.id')
                ->orderBy('depts.name', $direction)
                ->select('employees.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $employees = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => (string) $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        $depts = Dept::active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Employees/Create', [
            'depts' => $depts,
        ]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        Employee::create($request->validated());

        return redirect()->route('employees.index')->with('success', '社員を登録しました。');
    }

    public function edit(Employee $employee): Response
    {
        $depts = Dept::active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Employees/Edit', [
            'employee' => $employee->load('dept'),
            'depts' => $depts,
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return redirect()->route('employees.index')->with('success', '社員を更新しました。');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->is_deleted = true;
        $employee->save();

        return redirect()->route('employees.index')->with('success', '社員を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new EmployeesExport(
            search: $request->string('search')->toString(),
            sort: $request->string('sort', 'code')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
        );

        $filename = '社員マスタ_'.now()->format('YmdHis').'.xlsx';

        return Excel::download($export, $filename);
    }
}
