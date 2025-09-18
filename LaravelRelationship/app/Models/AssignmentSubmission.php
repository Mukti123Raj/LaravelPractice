<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\AssignmentSubmitted;
use App\Events\AssignmentGraded;

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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($submission) {
            // Dispatch event when assignment is submitted
            event(new AssignmentSubmitted($submission));
        });

        static::updated(function ($submission) {
            // Check if marks were just added (graded)
            if ($submission->wasChanged('marks_obtained') && $submission->marks_obtained !== null) {
                event(new AssignmentGraded($submission));
            }
        });
    }

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
