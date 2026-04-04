<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeptRequest extends FormRequest
{
    public function rules(): array
    {
        $dept = $this->route('dept');

        $parentIdRules = ['nullable', 'exists:depts,id'];
        if ($dept) {
            $parentIdRules[] = Rule::notIn([$dept->id]);
        }

        return [
            'name' => 'required|string|max:100',
            'parent_id' => $parentIdRules,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '所属名は必須です。',
            'name.max' => '所属名は100文字以内で入力してください。',
            'parent_id.exists' => '選択された親所属が存在しません。',
            'parent_id.not_in' => '自分自身を親所属に設定できません。',
        ];
    }
}
