<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'barcode'        => ['nullable', 'string', 'max:255'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price'     => ['required', 'numeric', 'min:0'],
            'stock'          => ['required', 'integer', 'min:0'],
            'is_frequent'    => ['nullable', 'boolean'],
            'min_stock'      => ['nullable', 'integer', 'min:0'],
            'category'       => ['nullable', 'string', 'max:255'],
            'supplier'       => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'           => 'El nombre del producto es obligatorio.',
            'purchase_price.required' => 'El precio de compra es obligatorio.',
            'sale_price.required'     => 'El precio de venta es obligatorio.',
            'stock.required'          => 'El stock es obligatorio.',
        ];
    }
}
