<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category_name' => 'required|exists:categories,name',
            'title' => 'required|string|max:255|unique:posts,title',
            'content' => 'required|string|min:10',
            'file' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Kategori seçilmesi zorunludur.',
            'category_id.exists' => 'Seçilen kategori mevcut değil.',
            'title.required' => 'Başlık zorunludur.',
            'title.unique' => 'Bu başlık zaten mevcut.',
            'content.required' => 'İçerik boş olamaz.',
        ];
    }
}
