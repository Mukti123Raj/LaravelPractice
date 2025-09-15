<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'required|string',
            'total_marks' => 'required|integer|min:1',
            'due_date' => 'required|date|after:now',
            'subject_id' => 'required|exists:subjects,id',
        ];
    }
}


