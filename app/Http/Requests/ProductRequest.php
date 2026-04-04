<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('products', 'code')->ignore($product?->id)->where('is_deleted', false),
            ],
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:200|regex:/^[ァ-ヶー　 ]+$/u',
            'category' => ['nullable', Rule::in(array_keys(Product::CATEGORIES))],
            'spec' => 'nullable|string|max:255',
            'maker' => 'nullable|string|max:100',
            'jan_code' => [
                'nullable', 'string', 'digits:13',
                Rule::unique('products', 'jan_code')->ignore($product?->id)->where('is_deleted', false),
            ],
            'unit' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'tax_rate' => ['nullable', Rule::in(array_keys(Product::TAX_RATES))],
            'has_stock' => 'boolean',
            'status' => ['required', Rule::in(array_keys(Product::STATUSES))],
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => '商品コードは必須です。',
            'code.max' => '商品コードは20文字以内で入力してください。',
            'code.unique' => 'この商品コードはすでに使用されています。',
            'name.required' => '商品名は必須です。',
            'name.max' => '商品名は100文字以内で入力してください。',
            'name_kana.regex' => '商品名カナは全角カタカナで入力してください。',
            'category.in' => 'カテゴリの値が不正です。',
            'jan_code.digits' => 'JANコードは13桁の数字で入力してください。',
            'jan_code.unique' => 'このJANコードはすでに使用されています。',
            'price.numeric' => '販売単価は数値で入力してください。',
            'price.min' => '販売単価は0以上で入力してください。',
            'cost.numeric' => '仕入単価は数値で入力してください。',
            'cost.min' => '仕入単価は0以上で入力してください。',
            'tax_rate.in' => '税率の値が不正です。',
            'status.required' => '状態は必須です。',
            'status.in' => '状態の値が不正です。',
            'remarks.max' => '備考は1000文字以内で入力してください。',
        ];
    }
}
