<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {

        return [
            'name' => ['required', 'string', 'max:255',
                // Aynı kullanıcıya ait kategorilerde benzersiz olmalı
                Rule::unique('categories', 'name')
                    ->where('user_id', auth()->id())
                    ->ignore($this->route('category')?->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Kategori adı zorunludur.',
            'name.unique' => 'Bu kategori zaten mevcut.',
        ];
    }
}
