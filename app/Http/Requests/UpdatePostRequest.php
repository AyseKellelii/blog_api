<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category_name' => 'required|exists:categories,name',
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'file' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Kategori adı zorunludur.',
            'category_name.exists' => 'Girilen kategori mevcut değil.',
            'title.required' => 'Başlık zorunludur.',
            'content.required' => 'İçerik boş olamaz.',
        ];
    }
}
