<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_content',
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
        if ($this->graded_at) {
            return 'graded';
        } elseif ($this->submitted_at) {
            return 'submitted';
        }
        return 'pending';
    }
}
