<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'       => 'required|string|max:100',
            'cpf'        => 'required|string|max:14|unique:customers,cpf',
            'phone'      => 'required|string|max:20',
            'birth_date' => 'required|date',
            'email'      => 'nullable|email|max:100',
            'photo'      => 'nullable|string',
            'notes'      => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Informe o nome do cliente.',
            'cpf.required' => 'Informe o CPF.',
            'cpf.unique' => 'Já existe um cliente com este CPF.',
            'phone.required' => 'Informe o telefone.',
            'birth_date.required' => 'Informe a data de nascimento.',
        ];
    }
}
