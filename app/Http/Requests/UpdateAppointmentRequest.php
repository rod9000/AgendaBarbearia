<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id'        => 'sometimes|required|exists:customers,id',
            'user_id'            => 'sometimes|required|exists:users,id',
            'service_ids'        => 'sometimes|required|array|min:1',
            'service_ids.*'      => 'exists:services,id',
            'start'              => 'sometimes|required|date',
            'end'                => 'sometimes|required|date|after:start',
            'status'             => 'nullable|string|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'notes'              => 'nullable|string|max:500',
            'recurring_frequency' => 'nullable|string|in:daily,weekly,biweekly,monthly',
            'recurring_until'    => 'nullable|date',
            'update_all_series'  => 'nullable|boolean',
        ];
    }
}
