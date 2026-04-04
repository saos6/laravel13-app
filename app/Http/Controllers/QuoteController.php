<?php

namespace App\Http\Controllers;

use App\Exports\QuotesExport;
use App\Http\Requests\QuoteRequest;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class QuoteController extends Controller
{
    public function index(Request $request): Response
    {
        $search    = (string) $request->input('search', '');
        $status    = (string) $request->input('status', '');
        $sort      = $request->input('sort', 'quote_number');
        $direction = $request->input('direction', 'desc');
        $perPage   = (int) $request->input('per_page', 10);

        $allowedSorts = ['quote_number', 'quote_date', 'expiry_date', 'subject', 'status', 'total_amount', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'quote_number';
        }
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $quotes = Quote::active()
            ->with(['customer:id,name', 'employee:id,name'])
            ->filtered($search, $status)
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Quotes/Index', [
            'quotes'   => $quotes,
            'filters'  => compact('search', 'status', 'sort', 'direction', 'per_page') + ['per_page' => (string) $perPage],
            'statuses' => Quote::STATUSES,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Quotes/Create', $this->formData());
    }

    public function store(QuoteRequest $request)
    {
        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $validated['quote_number'] = Quote::generateQuoteNumber();
        [$subtotal, $taxAmount, $totalAmount] = $this->calculateTotals($items);
        $validated['subtotal']      = $subtotal;
        $validated['tax_amount']    = $taxAmount;
        $validated['total_amount']  = $totalAmount;

        $quote = Quote::create($validated);
        $this->syncItems($quote, $items);

        return redirect()->route('quotes.index')->with('success', '見積を登録しました。');
    }

    public function show(Quote $quote): Response
    {
        abort_if($quote->is_deleted, 404);
        $quote->load(['customer', 'employee', 'items.product']);

        return Inertia::render('Quotes/Show', [
            'quote'    => $quote,
            'statuses' => Quote::STATUSES,
        ]);
    }

    public function edit(Quote $quote): Response
    {
        abort_if($quote->is_deleted, 404);
        $quote->load('items');

        return Inertia::render('Quotes/Edit', array_merge(
            $this->formData(),
            ['quote' => $quote]
        ));
    }

    public function update(QuoteRequest $request, Quote $quote)
    {
        abort_if($quote->is_deleted, 404);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        [$subtotal, $taxAmount, $totalAmount] = $this->calculateTotals($items);
        $validated['subtotal']     = $subtotal;
        $validated['tax_amount']   = $taxAmount;
        $validated['total_amount'] = $totalAmount;

        $quote->update($validated);
        $this->syncItems($quote, $items);

        return redirect()->route('quotes.index')->with('success', '見積を更新しました。');
    }

    public function destroy(Quote $quote)
    {
        abort_if($quote->is_deleted, 404);
        $quote->is_deleted = true;
        $quote->save();

        return redirect()->route('quotes.index')->with('success', '見積を削除しました。');
    }

    public function exportMethod(Request $request)
    {
        $search    = (string) $request->input('search', '');
        $status    = (string) $request->input('status', '');
        $sort      = $request->input('sort', 'quote_number');
        $direction = $request->input('direction', 'desc');

        return Excel::download(
            new QuotesExport($search, $status, $sort, $direction),
            '見積一覧_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    // ─── Private helpers ───────────────────────────────────────────────

    private function formData(): array
    {
        return [
            'customers' => Customer::active()->orderBy('code')->get(['id', 'name']),
            'employees' => Employee::active()->orderBy('code')->get(['id', 'name']),
            'products'  => Product::active()->orderBy('code')->get(['id', 'code', 'name', 'spec', 'unit', 'price', 'tax_rate']),
            'statuses'  => Quote::STATUSES,
            'taxRates'  => Product::TAX_RATES,
        ];
    }

    private function calculateTotals(array $items): array
    {
        $subtotal  = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $qty   = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $rate  = (int) ($item['tax_rate'] ?? 10);
            $amt   = round($qty * $price, 2);
            $subtotal  += $amt;
            $taxAmount += round($amt * $rate / 100, 2);
        }

        return [round($subtotal, 2), round($taxAmount, 2), round($subtotal + $taxAmount, 2)];
    }

    private function syncItems(Quote $quote, array $items): void
    {
        $quote->items()->delete();

        foreach ($items as $i => $item) {
            $qty   = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);

            QuoteItem::create([
                'quote_id'     => $quote->id,
                'line_no'      => $i + 1,
                'product_id'   => $item['product_id'] ?: null,
                'product_name' => $item['product_name'],
                'spec'         => $item['spec'] ?? null,
                'quantity'     => $qty,
                'unit'         => $item['unit'] ?? null,
                'unit_price'   => $price,
                'tax_rate'     => $item['tax_rate'],
                'amount'       => round($qty * $price, 2),
                'remarks'      => $item['remarks'] ?? null,
            ]);
        }
    }
}
