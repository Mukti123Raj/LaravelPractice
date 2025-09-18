<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        // List subjects for the authenticated teacher
        $teacher = Auth::user();
        $subjects = Subject::query()
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();

        return view('teacher.attendance.index', compact('subjects'));
    }

    public function show(Subject $subject)
    {
        // Ensure the authenticated teacher owns the subject
        $teacher = Auth::user();
        abort_unless((int) $subject->teacher_id === (int) $teacher->id, 403);

        // Determine date to display
        $date = request('date', now()->toDateString());

        // Students enrolled in the subject (via pivot student_subject) using PowerJoins
        $students = \App\Models\Student::query()
            ->joinRelationship('subjects')
            ->where('subjects.id', $subject->id)
            ->select('students.*')
            ->orderBy('students.name')
            ->get();

        // Existing attendance keyed by student id for the selected date
        $attendanceMap = Attendance::query()
            ->where('subject_id', $subject->id)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        return view('teacher.attendance.show', [
            'subject' => $subject,
            'students' => $students,
            'date' => $date,
            'attendanceMap' => $attendanceMap,
        ]);
    }

    public function store(Request $request, Subject $subject)
    {
        $teacher = Auth::user();
        abort_unless((int) $subject->teacher_id === (int) $teacher->id, 403);

        $validated = $request->validate([
            'attendance_date' => ['required', 'date'],
            'attendance' => ['array'], // [student_id => present]
        ]);

        $attendanceDate = $validated['attendance_date'];
        $marked = $validated['attendance'] ?? [];

        // Upsert attendance entries for each student of this subject
        $studentIds = $subject->students()->pluck('students.id');

        foreach ($studentIds as $studentId) {
            $isPresent = array_key_exists((string)$studentId, $marked);

            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $subject->id,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'is_present' => $isPresent,
                ]
            );
        }

        return redirect()
            ->route('teacher.attendance.show', [$subject, 'date' => $attendanceDate])
            ->with('status', 'Attendance saved.');
    }

    public function summary()
    {
        $authUser = Auth::user();
        $student = Student::query()
            ->where('email', $authUser->email)
            ->first();

        if (!$student) {
            return view('student.attendance.summary', [
                'summary' => collect(),
            ]);
        }

        // Aggregate attendance per subject for this student using PowerJoins
        $summary = Attendance::query()
            ->joinRelationship('subject')
            ->where('attendances.student_id', $student->id)
            ->select('attendances.subject_id', 'subjects.name as subject_name')
            ->selectRaw('COUNT(*) as total_lectures')
            ->selectRaw('SUM(CASE WHEN attendances.is_present THEN 1 ELSE 0 END) as attended')
            ->groupBy('attendances.subject_id', 'subjects.name')
            ->get();

        return view('student.attendance.summary', [
            'summary' => $summary,
        ]);
    }
}
