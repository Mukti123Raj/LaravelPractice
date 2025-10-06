<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;

class TeacherSubjectRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If the subject does not exist, let the 'exists' rule handle it
        $subject = Subject::find($value);
        if ($subject === null) {
            return;
        }

        if ($subject->teacher_id !== Auth::id()) {
            $fail('You are not authorized to create an assignment for this subject.');
        }
    }
}
