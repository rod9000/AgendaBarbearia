<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'       => 'required|string|max:100',
            'cpf'        => 'required|string|max:14|unique:customers,cpf,' . $this->route('customer')->id,
            'phone'      => 'required|string|max:20',
            'birth_date' => 'required|date',
            'email'      => 'nullable|email|max:100',
            'photo'      => 'nullable|string',
            'notes'      => 'nullable|string|max:500',
        ];
    }
}
