<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StudentSubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'submission_content' => 'nullable|string',
            'submission_file' => 'nullable|file|mimes:pdf,doc,docx|max:1024',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasContent = !empty($this->input('submission_content'));
            $hasFile = $this->hasFile('submission_file');

            if (!$hasContent && !$hasFile) {
                $validator->errors()->add('submission', 'Please provide either text content or upload a file.');
            }
        });
    }
}


