<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArriveeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'required|string|min:30',
        ];
    }
      public function messages(): array
    {
        return [
            'description.required' => 'La description est obligatoire',
            'description.min' => 'La description doit contenir au moins 30 caractères',
        ];
    }
}
