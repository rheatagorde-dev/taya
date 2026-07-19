<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'doc_type' => 'required|in:commitment_order,charge_sheet,court_record,other',
            'phase_number' => 'nullable|integer|between:1,4',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'The file must not exceed 10MB.',
            'file.mimes' => 'Only PDF, JPG, and PNG files are allowed.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $file = request()->file('file');
        Log::debug('StoreDocumentRequest validation failed', [
            'errors' => $validator->errors()->toArray(),
            'uploaded_files' => request()->allFiles(),
            'post_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'upload_tmp_dir' => ini_get('upload_tmp_dir'),
            'file_error_code' => $file ? $file->getError() : 'no file object',
            'file_isValid' => $file ? $file->isValid() : false,
            'file_clientOriginalName' => $file ? $file->getClientOriginalName() : 'N/A',
            'file_size' => $file ? $file->getSize() : 0,
        ]);

        parent::failedValidation($validator);
    }
}
