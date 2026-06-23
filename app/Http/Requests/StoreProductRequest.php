<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'            => 'required|string|max:100',
            'brand'           => 'nullable|string|max:100',
            'expiry_date'     => 'nullable|date|after:today',
            'purchase_price'  => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'quantity'        => 'required|integer|min:0',
            'min_stock'       => 'nullable|integer|min:0',
            'supplier'        => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Informe o nome do produto.',
            'purchase_price.required' => 'Informe o preço de compra.',
            'sale_price.required' => 'Informe o preço de venda.',
            'quantity.required' => 'Informe a quantidade em estoque.',
        ];
    }
}
