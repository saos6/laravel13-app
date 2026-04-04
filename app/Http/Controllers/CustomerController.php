<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['id', 'code', 'name', 'name_kana', 'closing_day', 'payment_cycle', 'payment_day', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'code';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search = $request->get('search', '');

        $customers = Customer::with('employee')
            ->active()
            ->filtered($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => (string) $perPage,
            ],
            'paymentCycles' => Customer::PAYMENT_CYCLES,
        ]);
    }

    public function create(): Response
    {
        $employees = Employee::active()->orderBy('code')->get(['id', 'code', 'name']);

        return Inertia::render('Customers/Create', [
            'employees' => $employees,
            'paymentCycles' => Customer::PAYMENT_CYCLES,
        ]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated());

        return redirect()->route('customers.index')->with('success', '得意先を登録しました。');
    }

    public function edit(Customer $customer): Response
    {
        $employees = Employee::active()->orderBy('code')->get(['id', 'code', 'name']);

        return Inertia::render('Customers/Edit', [
            'customer' => $customer->load('employee'),
            'employees' => $employees,
            'paymentCycles' => Customer::PAYMENT_CYCLES,
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('success', '得意先を更新しました。');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->is_deleted = true;
        $customer->save();

        return redirect()->route('customers.index')->with('success', '得意先を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new CustomersExport(
            search: $request->string('search')->toString(),
            sort: $request->string('sort', 'code')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
        );

        $filename = '得意先マスタ_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download($export, $filename);
    }
}
