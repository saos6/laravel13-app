<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedSorts = ['id', 'code', 'name', 'name_kana', 'category', 'maker', 'unit', 'price', 'cost', 'tax_rate', 'status', 'created_at', 'updated_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'code';
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $search = $request->get('search', '');

        $products = Product::active()
            ->filtered($search)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'filters'    => [
                'search'    => $search,
                'sort'      => $sort,
                'direction' => $direction,
                'per_page'  => (string) $perPage,
            ],
            'categories' => Product::CATEGORIES,
            'taxRates'   => Product::TAX_RATES,
            'statuses'   => Product::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => Product::CATEGORIES,
            'taxRates'   => Product::TAX_RATES,
            'statuses'   => Product::STATUSES,
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()->route('products.index')->with('success', '商品を登録しました。');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', [
            'product'    => $product,
            'categories' => Product::CATEGORIES,
            'taxRates'   => Product::TAX_RATES,
            'statuses'   => Product::STATUSES,
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('products.index')->with('success', '商品を更新しました。');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->is_deleted = true;
        $product->save();

        return redirect()->route('products.index')->with('success', '商品を削除しました。');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $export = new ProductsExport(
            search: $request->string('search')->toString(),
            sort: $request->string('sort', 'code')->toString(),
            direction: $request->string('direction', 'asc')->toString(),
        );

        $filename = '商品マスタ_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download($export, $filename);
    }
}
