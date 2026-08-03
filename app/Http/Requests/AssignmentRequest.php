<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'classroom_id' => [
                'required',
                'exists:classrooms,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'deadline' => [
                'required',
                'date',
            ],

            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar',
                'max:10240',
            ],
        ];
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [
            'classroom_id.required' => 'Kelas wajib dipilih.',
            'classroom_id.exists' => 'Kelas tidak ditemukan.',

            'title.required' => 'Judul tugas wajib diisi.',

            'description.required' => 'Deskripsi tugas wajib diisi.',

            'deadline.required' => 'Deadline wajib diisi.',

            'attachment.mimes' => 'Format file tidak didukung.',
            'attachment.max' => 'Ukuran file maksimal 10 MB.',
        ];
    }
}
