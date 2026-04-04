<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::active()->pluck('id')->toArray();
        $employees = Employee::active()->pluck('id')->toArray();
        $products = Product::active()->get(['id', 'name', 'spec', 'unit', 'price', 'tax_rate']);

        if (empty($customers)) {
            return;
        }

        $statuses = array_keys(Quote::STATUSES);

        for ($i = 1; $i <= 10; $i++) {
            $quoteDate = now()->subDays(rand(0, 60));

            $quote = Quote::create([
                'quote_number' => Quote::generateQuoteNumber(),
                'customer_id' => $customers[array_rand($customers)],
                'employee_id' => empty($employees) ? null : $employees[array_rand($employees)],
                'quote_date' => $quoteDate->format('Y-m-d'),
                'expiry_date' => $quoteDate->addDays(30)->format('Y-m-d'),
                'subject' => "サンプル見積 #{$i}",
                'status' => $statuses[array_rand($statuses)],
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'remarks' => null,
            ]);

            $lineCount = rand(1, 4);
            $subtotal = 0;
            $taxAmount = 0;

            for ($j = 1; $j <= $lineCount; $j++) {
                $product = $products->isNotEmpty() ? $products->random() : null;
                $qty = rand(1, 10);
                $price = $product ? (float) $product->price : rand(1000, 50000);
                $rate = $product ? (int) $product->tax_rate : 10;
                $amount = round($qty * $price, 2);
                $subtotal += $amount;
                $taxAmount += round($amount * $rate / 100, 2);

                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'line_no' => $j,
                    'product_id' => $product?->id,
                    'product_name' => $product?->name ?? "商品{$j}",
                    'spec' => $product?->spec,
                    'quantity' => $qty,
                    'unit' => $product?->unit ?? '個',
                    'unit_price' => $price,
                    'tax_rate' => (string) $rate,
                    'amount' => $amount,
                    'remarks' => null,
                ]);
            }

            $quote->update([
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'total_amount' => round($subtotal + $taxAmount, 2),
            ]);
        }
    }
}
