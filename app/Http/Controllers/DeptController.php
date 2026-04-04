<?php

namespace App\Http\Controllers;

use App\Exports\DeptsExport;
use App\Http\Requests\DeptRequest;
use App\Models\Dept;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DeptController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['id', 'name', 'parent_id', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'id';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search = $request->get('search', '');

        $query = Dept::with('parent')
            ->active()
            ->filtered($search);

        if ($sort === 'parent_id') {
            $query->leftJoin('depts as parents', 'depts.parent_id', '=', 'parents.id')
                ->orderBy('parents.name', $direction)
                ->select('depts.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $depts = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Depts/Index', [
            'depts' => $depts,
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
        $parents = Dept::active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Depts/Create', [
            'parents' => $parents,
        ]);
    }

    public function store(DeptRequest $request): RedirectResponse
    {
        Dept::create($request->validated());

        return redirect()->route('depts.index')->with('success', '所属を登録しました。');
    }

    public function edit(Dept $dept): Response
    {
        $parents = Dept::active()
            ->where('id', '!=', $dept->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Depts/Edit', [
            'dept' => $dept->load('parent'),
            'parents' => $parents,
        ]);
    }

    public function update(DeptRequest $request, Dept $dept): RedirectResponse
    {
        $dept->update($request->validated());

        return redirect()->route('depts.index')->with('success', '所属を更新しました。');
    }

    public function destroy(Dept $dept): RedirectResponse
    {
        if ($dept->children()->active()->exists()) {
            return redirect()->back()->with('error', 'この所属には子所属が存在するため削除できません。');
        }

        $dept->is_deleted = true;
        $dept->save();

        return redirect()->route('depts.index')->with('success', '所属を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new DeptsExport(
            search: $request->string('search')->toString(),
            sort: $request->string('sort', 'id')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
        );

        $filename = '所属マスタ_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download($export, $filename);
    }
}
