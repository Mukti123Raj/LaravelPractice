<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_content',
        'file_path',
        'marks_obtained',
        'teacher_feedback',
        'submitted_at',
        'graded_at',
        'assignment_id',
        'student_id'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getStatusAttribute()
    {
        if ($this->graded_at && $this->marks_obtained !== null) {
            return 'done_with_marks';
        } elseif ($this->submitted_at) {
            // Check if submitted late
            if ($this->submitted_at->gt($this->assignment->due_date)) {
                return 'late_submit';
            } else {
                return 'submitted';
            }
        }
        return 'pending';
    }
}
