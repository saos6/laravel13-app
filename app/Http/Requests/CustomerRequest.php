<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('customers', 'code')->ignore($customer?->id)->where('is_deleted', false),
            ],
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:200|regex:/^[ァ-ヶー　 ]+$/u',
            'postal_code' => 'nullable|string|max:8|regex:/^\d{3}-?\d{4}$/',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'closing_day' => 'nullable|integer|min:1|max:31',
            'payment_cycle' => ['nullable', Rule::in(array_keys(Customer::PAYMENT_CYCLES))],
            'payment_day' => 'nullable|integer|min:1|max:31',
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => '得意先コードは必須です。',
            'code.max' => '得意先コードは20文字以内で入力してください。',
            'code.unique' => 'この得意先コードはすでに使用されています。',
            'name.required' => '得意先名は必須です。',
            'name.max' => '得意先名は100文字以内で入力してください。',
            'name_kana.max' => '得意先名カナは200文字以内で入力してください。',
            'name_kana.regex' => '得意先名カナは全角カタカナで入力してください。',
            'postal_code.regex' => '郵便番号はXXX-XXXXの形式で入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'employee_id.exists' => '選択された担当社員が存在しません。',
            'closing_day.min' => '締め日は1〜31の範囲で入力してください。',
            'closing_day.max' => '締め日は1〜31の範囲で入力してください。',
            'payment_cycle.in' => '支払いサイクルの値が不正です。',
            'payment_day.min' => '支払日は1〜31の範囲で入力してください。',
            'payment_day.max' => '支払日は1〜31の範囲で入力してください。',
            'remarks.max' => '備考は1000文字以内で入力してください。',
        ];
    }
}
