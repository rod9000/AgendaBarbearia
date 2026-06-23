<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id'        => 'required|exists:customers,id',
            'user_id'            => 'required|exists:users,id',
            'service_ids'        => 'required|array|min:1',
            'service_ids.*'      => 'exists:services,id',
            'start'              => 'required|date',
            'end'                => 'nullable|date|after:start',
            'notes'              => 'nullable|string|max:500',
            'recurring_frequency' => 'nullable|string|in:daily,weekly,biweekly,monthly',
            'recurring_until'    => 'nullable|date|after:start',
        ];
    }

    public function messages()
    {
        return [
            'customer_id.required' => 'Selecione um cliente.',
            'customer_id.exists' => 'Cliente não encontrado.',
            'user_id.required' => 'Selecione um profissional.',
            'service_ids.required' => 'Selecione pelo menos um serviço.',
            'start.required' => 'Informe a data e hora de início.',
        ];
    }
}
