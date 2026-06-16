<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
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
            'question' => 'required|string',
            'answer' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Pertanyaan wajib diisi',
            'answer.required' => 'Jawaban wajib diisi',
        ];
    }
}
