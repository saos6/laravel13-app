<?php

namespace App\Http\Requests;

use App\Models\Quote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'quote_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:quote_date'],
            'subject' => ['required', 'string', 'max:200'],
            'status' => ['required', Rule::in(array_keys(Quote::STATUSES))],
            'remarks' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:200'],
            'items.*.spec' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', Rule::in(['0', '8', '10'])],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => '得意先は必須です。',
            'customer_id.exists' => '得意先が存在しません。',
            'employee_id.exists' => '担当者が存在しません。',
            'quote_date.required' => '見積日は必須です。',
            'quote_date.date' => '見積日の形式が正しくありません。',
            'expiry_date.date' => '有効期限の形式が正しくありません。',
            'expiry_date.after_or_equal' => '有効期限は見積日以降の日付を入力してください。',
            'subject.required' => '件名は必須です。',
            'subject.max' => '件名は200文字以内で入力してください。',
            'status.required' => 'ステータスは必須です。',
            'status.in' => 'ステータスが正しくありません。',
            'items.required' => '明細を1行以上入力してください。',
            'items.min' => '明細を1行以上入力してください。',
            'items.*.product_name.required' => '品名は必須です。',
            'items.*.product_name.max' => '品名は200文字以内で入力してください。',
            'items.*.quantity.required' => '数量は必須です。',
            'items.*.quantity.min' => '数量は0より大きい値を入力してください。',
            'items.*.unit_price.required' => '単価は必須です。',
            'items.*.unit_price.min' => '単価は0以上の値を入力してください。',
            'items.*.tax_rate.required' => '税率は必須です。',
            'items.*.tax_rate.in' => '税率が正しくありません。',
        ];
    }
}
