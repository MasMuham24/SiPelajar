<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
        $studentId = $this->student?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'classroom_id' => [
                'required',
                'exists:classrooms,id',
            ],
            'nis' => [
                'required',
                'string',
                'unique:students,nis,' . $studentId,
                'unique:users,username,' . ($this->student?->user_id ?? 'NULL'),
            ],
            'nisn' => [
                'required',
                'string',
                'unique:students,nisn,' . $studentId,
            ],
            'gender' => [
                'required',
                'in:Laki-laki,Perempuan',
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^[0-9]+$/',
                'max:20',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'photo' => [
                'nullable',
                'image',
                'max:2048',
            ],
        ];
    }
}
