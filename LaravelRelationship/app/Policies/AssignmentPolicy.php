<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Auth\Access\Response;

class AssignmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'teacher' || $user->role === 'student';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'teacher') {
            $teacher = Teacher::where('email', $user->email)->first();
            return $teacher && $assignment->teacher_id === $teacher->id;
        }

        if ($user->role === 'student') {
            $student = Student::where('email', $user->email)->first();
            return $student && $assignment->subject->students->contains($student);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'teacher';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'teacher') {
            $teacher = Teacher::where('email', $user->email)->first();
            return $teacher && $assignment->teacher_id === $teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'teacher') {
            $teacher = Teacher::where('email', $user->email)->first();
            return $teacher && $assignment->teacher_id === $teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can grade submissions for the assignment.
     */
    public function gradeSubmission(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'teacher') {
            $teacher = Teacher::where('email', $user->email)->first();
            return $teacher && $assignment->teacher_id === $teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Assignment $assignment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Assignment $assignment): bool
    {
        return false;
    }
}
