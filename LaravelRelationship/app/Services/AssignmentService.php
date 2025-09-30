<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Notifications\AssignmentCreatedNotification;
use App\Notifications\AssignmentSubmittedNotification;
use App\Notifications\AssignmentGradedNotification;

class AssignmentService
{
    public function createAssignment(array $data): Assignment
    {
        /** @var User|null $authUser */
        $authUser = $data['user'] ?? Auth::user();
        /** @var Teacher|null $teacher */
        $teacher = isset($data['teacher']) && $data['teacher'] instanceof Teacher
            ? $data['teacher']
            : ($authUser ? Teacher::where('email', $authUser->email)->first() : null);

        if (!$teacher) {
            throw new \RuntimeException('Teacher profile not found.');
        }

        $subjectId = $data['subject_id'] ?? null;
        $subject = Subject::where('id', $subjectId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $assignment = Assignment::create([
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'total_marks' => $data['total_marks'] ?? 0,
            'due_date' => $data['due_date'] ?? null,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        try {
            $students = $subject->students;
            foreach ($students as $student) {
                $user = User::where('email', $student->email)->first();
                if ($user) {
                    $user->notify(new AssignmentCreatedNotification($assignment));
                }
            }
        } catch (\Exception $notificationError) {
            \Log::warning('Failed to send assignment notifications: ' . $notificationError->getMessage(), [
                'assignment_id' => $assignment->id,
                'subject_id' => $subject->id,
            ]);
        }

        Cache::forget("teacher:{$teacher->id}:subject:{$subject->id}");
        return $assignment;
    }

    public function getTeacherAssignments(User $teacherUser)
    {
        $teacher = Teacher::where('email', $teacherUser->email)->firstOrFail();

        $cacheKey = "teacher:{$teacher->id}:subjects:assignments";
        return Cache::remember($cacheKey, 30, function () use ($teacher) {
            return Subject::with(['assignments.submissions.student', 'students'])
                ->where('teacher_id', $teacher->id)
                ->get();
        });
    }

    public function getStudentAssignments(User $studentUser)
    {
        $student = Student::where('email', $studentUser->email)->firstOrFail();

        $cacheKey = "student:{$student->id}:assignments:index";
        $studentWithSubjects = Cache::remember($cacheKey, 30, function () use ($student) {
            return Student::with(['subjects.assignments.submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
                ->where('id', $student->id)
                ->first();
        });

        $assignments = collect();
        foreach ($studentWithSubjects->subjects as $subject) {
            foreach ($subject->assignments as $assignment) {
                $submission = $assignment->submissions->first();
                $status = $assignment->getStatusForStudent($student->id);
                $assignments->push([
                    'assignment' => $assignment,
                    'submission' => $submission,
                    'status' => $status,
                    'subject' => $subject,
                ]);
            }
        }

        return $assignments->sortBy('assignment.due_date');
    }

    public function submitAssignment(Assignment $assignment, Student $student, string $content, ?UploadedFile $file): AssignmentSubmission
    {
        if (now()->gt($assignment->due_date)) {
            throw new \RuntimeException('Assignment deadline has passed.');
        }

        $isEnrolled = $assignment->subject
            ->students()
            ->where('students.id', $student->id)
            ->exists();
        if (!$isEnrolled) {
            throw new \RuntimeException('You do not have permission to submit to this assignment.');
        }

        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();
        if ($existingSubmission) {
            throw new \RuntimeException('You have already submitted this assignment.');
        }

        $filePath = null;
        if ($file) {
            $fileName = time() . '_' . $student->id . '_' . $assignment->id . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('assignment_submissions', $fileName, 'public');
        }

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submission_content' => $content,
            'file_path' => $filePath,
            'submitted_at' => now(),
        ]);

        try {
            $teacher = $assignment->subject->teacher;
            $teacherUser = User::where('email', $teacher->email)->first();
            if ($teacherUser) {
                $teacherUser->notify(new AssignmentSubmittedNotification($submission));
            }
        } catch (\Exception $notificationError) {
            \Log::warning('Failed to send submission notification: ' . $notificationError->getMessage(), [
                'submission_id' => $submission->id,
                'assignment_id' => $assignment->id,
                'teacher_id' => $assignment->subject->teacher_id,
            ]);
        }

        Cache::forget("student:{$student->id}:assignments:index");
        Cache::forget("student:{$student->id}:assignment:{$assignment->id}");
        return $submission;
    }

    public function gradeSubmission(AssignmentSubmission $submission, int $grade, ?string $feedback): AssignmentSubmission
    {
        $teacher = Teacher::where('email', Auth::user()->email)->first();
        if (!$teacher) {
            throw new \RuntimeException('Teacher profile not found.');
        }

        // Ensure the submission belongs to the teacher's assignment
        if ($submission->assignment->teacher_id !== $teacher->id) {
            throw new \RuntimeException('You do not have permission to grade this submission.');
        }

        if ($grade > $submission->assignment->total_marks) {
            throw new \InvalidArgumentException('Marks cannot exceed total marks.');
        }

        $submission->update([
            'marks_obtained' => $grade,
            'teacher_feedback' => $feedback,
            'graded_at' => now(),
        ]);

        try {
            $student = $submission->student;
            $user = User::where('email', $student->email)->first();
            if ($user) {
                $user->notify(new AssignmentGradedNotification($submission));
            }
        } catch (\Exception $notificationError) {
            \Log::warning('Failed to send grading notification: ' . $notificationError->getMessage(), [
                'submission_id' => $submission->id,
                'student_id' => $submission->student_id,
            ]);
        }

        Cache::forget("teacher:{$teacher->id}:assignment:{$submission->assignment_id}");
        Cache::forget("student:{$submission->student_id}:assignment:{$submission->assignment_id}");
        return $submission;
    }
}



