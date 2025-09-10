<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;

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

Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/teacher', [TeacherController::class, 'index'])->name('teacher.index');
    Route::post('/teacher/classroom/create', [TeacherController::class, 'createClassroom'])->name('teacher.classroom.create');
    Route::post('/teacher/subject/create', [TeacherController::class, 'createSubject'])->name('teacher.subject.create');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student', [StudentController::class, 'index'])->name('student.index');
});
