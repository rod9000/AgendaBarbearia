<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'         => 'required|string|max:100|unique:services,name',
            'duration_min' => 'required|integer|min:5|max:480',
            'price'        => 'required|numeric|min:0',
            'color_hex'    => 'nullable|string|max:7',
            'description'  => 'nullable|string|max:500',
            'active'       => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Informe o nome do serviço.',
            'name.unique' => 'Já existe um serviço com este nome.',
            'duration_min.required' => 'Informe a duração.',
            'price.required' => 'Informe o preço.',
        ];
    }
}
