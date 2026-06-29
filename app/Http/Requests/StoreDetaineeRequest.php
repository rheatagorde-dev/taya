<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetaineeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin', 'bjmp_staff');
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'charge_description' => 'required|string',
            'charge_rpc_code' => 'required|exists:penalty_references,id',
            'commitment_date' => 'required|date|before_or_equal:today',
            'facility_id' => 'required|exists:facilities,id',
        ];
    }

    public function messages(): array
    {
        return [
            'charge_rpc_code.exists' => 'The selected charge code does not exist in the penalty reference database.',
            'commitment_date.before_or_equal' => 'The commitment date cannot be in the future.',
        ];
    }
}
