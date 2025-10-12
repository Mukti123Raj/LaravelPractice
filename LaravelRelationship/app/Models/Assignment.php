<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use App\Events\AssignmentCreated;

class Assignment extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'total_marks',
        'due_date',
        'subject_id',
        'teacher_id'
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, AssignmentSubmission::class, 'assignment_id', 'id', 'id', 'student_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getStatusForStudent($studentId)
    {
        $submission = $this->submissions()->where('student_id', $studentId)->first();
        
        if ($submission) {
            return $submission->status;
        } else {
            // No submission yet - check due date
            if ($this->due_date->isPast()) {
                return 'overdue';
            } elseif ($this->due_date->diffInDays(now()) <= 1) {
                return 'due_soon';
            } else {
                return 'not_submitted';
            }
        }
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
