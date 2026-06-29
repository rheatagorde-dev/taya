<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegalActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin', 'pao_lawyer', 'ngo_lawyer', 'court_admin');
    }

    public function rules(): array
    {
        return [
            'action_type' => 'required|in:motion_for_release,habeas_corpus,pao_referral,ngo_referral,case_review,other',
            'alert_id' => 'required|exists:alerts,id',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
