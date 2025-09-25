<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\StudentAssignmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmailController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'teacher') {
            return redirect()->route('teacher.index');
        }
        if (Auth::user()->role === 'student') {
            return redirect()->route('student.index');
        }
    }
    return view('welcome');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Email verification routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->intended('/');
    })->middleware(['signed'])->name('verification.verify');

    // Graceful redirect if someone hits the POST URL with GET
    Route::get('/email/verification-notification', function () {
        return redirect()->route('verification.notice');
    })->name('verification.send.get');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back();
        }
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

Route::middleware(['auth', 'verified', 'role:teacher'])->group(function () {
    Route::get('/teacher', [TeacherController::class, 'index'])->name('teacher.index');
    Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
    Route::get('/teacher/subjects', [TeacherController::class, 'subjects'])->name('teacher.subjects.index');
    Route::get('/teacher/students', [TeacherController::class, 'students'])->name('teacher.students.index');
    Route::post('/teacher/classroom/create', [TeacherController::class, 'createClassroom'])->name('teacher.classroom.create');
    Route::post('/teacher/subject/create', [TeacherController::class, 'createSubject'])->name('teacher.subject.create');
    Route::delete('/teacher/classroom/{classroom}', [TeacherController::class, 'deleteClassroom'])->name('teacher.classroom.delete');
    
    // Assignment routes
    Route::get('/teacher/subjects/{subject}', [AssignmentController::class, 'index'])->name('teacher.subjects.show');
    Route::post('/teacher/assignments/create', [AssignmentController::class, 'create'])->name('teacher.assignments.create');
    Route::get('/teacher/assignments/{assignment}', [AssignmentController::class, 'show'])->name('teacher.assignments.show');
    Route::post('/teacher/submissions/{submission}/grade', [AssignmentController::class, 'gradeSubmission'])->name('teacher.submissions.grade');
    Route::delete('/teacher/assignments/{assignment}', [AssignmentController::class, 'delete'])->name('teacher.assignments.delete');
    Route::get('/teacher/submissions/{submission}/download', [TeacherController::class, 'downloadSubmission'])->name('teacher.submissions.download');
    
    // Notification routes
    Route::get('/teacher/notifications', [NotificationController::class, 'index'])->name('teacher.notifications');
    Route::get('/teacher/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('teacher.notifications.unread-count');
    Route::post('/teacher/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('teacher.notifications.mark-read');
    Route::post('/teacher/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('teacher.notifications.mark-all-read');
    Route::delete('/teacher/notifications/{notification}', [NotificationController::class, 'delete'])->name('teacher.notifications.delete');
    Route::delete('/teacher/notifications', [NotificationController::class, 'deleteAll'])->name('teacher.notifications.delete-all');

    // Attendance routes (teacher)
    Route::get('/teacher/attendance', [AttendanceController::class, 'index'])->name('teacher.attendance.index');
    Route::get('/teacher/attendance/{subject}', [AttendanceController::class, 'show'])->name('teacher.attendance.show');
    Route::post('/teacher/attendance/{subject}', [AttendanceController::class, 'store'])->name('teacher.attendance.store');

    // Email routes (teacher)
    Route::get('/teacher/email/compose', [EmailController::class, 'compose'])->name('teacher.email.compose');
    Route::get('/teacher/email/get-students-by-class', [EmailController::class, 'getStudentsByClass'])->name('teacher.email.getStudentsByClass');
    Route::post('/teacher/email/send', [EmailController::class, 'send'])->name('teacher.email.send');
});

Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/student', [StudentController::class, 'index'])->name('student.index');
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/student/subjects', [StudentController::class, 'subjects'])->name('student.subjects');
    Route::post('/student/subjects/add', [StudentController::class, 'addSubject'])->name('student.subjects.add');
    Route::post('/student/subjects/remove', [StudentController::class, 'removeSubject'])->name('student.subjects.remove');
    
    // Assignment routes
    Route::get('/student/assignments', [StudentAssignmentController::class, 'index'])->name('student.assignments');
    Route::get('/student/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('student.assignments.show');
    Route::post('/student/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('student.assignments.submit');
    Route::post('/student/assignments/{assignment}/update', [StudentAssignmentController::class, 'updateSubmission'])->name('student.assignments.update');
    Route::get('/student/submissions/{submission}/download', [StudentAssignmentController::class, 'download'])->name('student.assignments.download');
    
    // Notification routes
    Route::get('/student/notifications', [NotificationController::class, 'index'])->name('student.notifications');
    Route::get('/student/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('student.notifications.unread-count');
    Route::post('/student/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('student.notifications.mark-read');
    Route::post('/student/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('student.notifications.mark-all-read');
    Route::delete('/student/notifications/{notification}', [NotificationController::class, 'delete'])->name('student.notifications.delete');
    Route::delete('/student/notifications', [NotificationController::class, 'deleteAll'])->name('student.notifications.delete-all');

    // Attendance routes (student)
    Route::get('/student/attendance', [AttendanceController::class, 'summary'])->name('student.attendance.summary');
});

// Password Reset Routes
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});
