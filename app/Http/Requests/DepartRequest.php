<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartRequest extends FormRequest
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
            'justificatifs.*' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MO = 5120KB
            'justificatifs' => 'required|array|min:1',
            'description' => 'required|string|min:30',
        ];
    }

    public function messages(): array
    {
        return [
            'justificatifs.required' => 'Vous devez fournir au moins un justificatif',
            'justificatifs.*.max' => 'Chaque fichier ne doit pas dépasser 5MO',
            'justificatifs.*.mimes' => 'Seuls les formats JPEG, PNG et PDF sont acceptés',
            'description.min' => 'La description doit contenir au moins 30 caractères',
        ];
    }
}
