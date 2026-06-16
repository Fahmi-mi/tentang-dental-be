<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromoRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:150',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'detail' => 'sometimes|required|string',
            'original_price' => 'sometimes|required|numeric|min:0|max:99999999.99',
            'promo_price' => 'sometimes|required|numeric|min:0|max:99999999.99|lt:original_price',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama promo wajib diisi',
            'image.image' => 'File harus berupa gambar',
            'image.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp',
            'image.max' => 'Ukuran gambar maksimal 2MB',
            'detail.required' => 'Detail promo wajib diisi',
            'original_price.numeric' => 'Harga normal harus berupa angka',
            'promo_price.numeric' => 'Harga promo harus berupa angka',
            'promo_price.lt' => 'Harga promo harus lebih kecil dari harga normal',
        ];
    }
}
