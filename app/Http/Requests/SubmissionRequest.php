<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,jpg,jpeg,png',
                'max:10240',
            ],
            'link' => [
                'nullable',
                'url',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Format file tidak didukung.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'link.url' => 'Link harus berupa URL yang valid.',
            'link.max' => 'Ukuran link maksimal 2048 karakter.',
        ];
    }
}
