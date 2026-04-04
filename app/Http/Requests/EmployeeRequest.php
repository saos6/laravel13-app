<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees', 'code')->ignore($employee?->id)->whereNull('deleted_at')->where('is_deleted', false),
            ],
            'name' => 'required|string|max:50',
            'name_kana' => 'nullable|string|max:100|regex:/^[ァ-ヶー　 ]+$/u',
            'dept_id' => 'nullable|exists:depts,id',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee?->id)->where('is_deleted', false),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => '社員コードは必須です。',
            'code.max' => '社員コードは20文字以内で入力してください。',
            'code.unique' => 'この社員コードはすでに使用されています。',
            'name.required' => '氏名は必須です。',
            'name.max' => '氏名は50文字以内で入力してください。',
            'name_kana.max' => '氏名カナは100文字以内で入力してください。',
            'name_kana.regex' => '氏名カナは全角カタカナで入力してください。',
            'dept_id.exists' => '選択された所属が存在しません。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは255文字以内で入力してください。',
            'email.unique' => 'このメールアドレスはすでに使用されています。',
        ];
    }
}
