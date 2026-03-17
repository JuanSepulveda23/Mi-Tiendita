<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
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
            'products'              => ['required', 'array', 'min:1'],
            'products.*.id'         => ['required', 'exists:products,id'],
            'products.*.quantity'   => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'products.required'            => 'Debe agregar al menos un producto.',
            'products.*.id.required'       => 'El producto es obligatorio.',
            'products.*.id.exists'         => 'El producto seleccionado no existe.',
            'products.*.quantity.required'  => 'La cantidad es obligatoria.',
            'products.*.quantity.min'       => 'La cantidad mínima es 1.',
        ];
    }
}
