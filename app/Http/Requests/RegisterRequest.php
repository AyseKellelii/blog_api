<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim alanı zorunludur.',
            'surname.required' => 'Soyisim alanı zorunludur.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'password.confirmed' => 'Parola doğrulaması uyuşmuyor.',
        ];
    }
}
