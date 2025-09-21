@extends('layouts.app')

@section('content')
<x-teacher-navbar />

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('teacher.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-door-open me-2"></i>
                            Classrooms
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-book me-2"></i>
                            Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user-graduate me-2"></i>
                            Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-chart-bar me-2"></i>
                            Reports
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">{{ $assignment->title }}</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('teacher.subjects.show', $assignment->subject_id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Subject
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Assignment Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Assignment Details</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $assignment->title }}
                                    </div>
                                    <div class="text-muted">
                                        Subject: {{ $assignment->subject->name }} | 
                                        Total Marks: {{ $assignment->total_marks }} | 
                                        Due: {{ $assignment->due_date->format('M d, Y H:i') }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tasks fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Content -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-file-alt me-2"></i>
                                Assignment Content
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6>Description:</h6>
                                <p class="text-muted">{{ $assignment->description }}</p>
                            </div>
                            <div>
                                <h6>Instructions:</h6>
                                <p class="text-muted">{{ $assignment->instructions }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Submissions -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-user-graduate me-2"></i>
                                Student Submissions ({{ count($studentSubmissions) }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Submitted At</th>
                                            <th>Marks</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($studentSubmissions as $item)
                                            <tr>
                                                <td>{{ $item['student']->name }}</td>
                                                <td>{{ $item['student']->email }}</td>
                                                <td>
                                                    @if($item['status'] === 'graded')
                                                        <span class="badge bg-success">Graded</span>
                                                    @elseif($item['status'] === 'submitted')
                                                        <span class="badge bg-warning">Submitted</span>
                                                    @else
                                                        <span class="badge bg-danger">Not Submitted</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['submission'])
                                                        {{ $item['submission']->submitted_at->format('M d, Y H:i') }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['submission'] && $item['submission']->marks_obtained !== null)
                                                        {{ $item['submission']->marks_obtained }}/{{ $assignment->total_marks }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item['submission'])
                                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#gradeModal{{ $item['submission']->id }}">
                                                            <i class="fas fa-edit me-1"></i>
                                                            Grade
                                                        </button>
                                                    @else
                                                        <span class="text-muted">No submission</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Grade Modals -->
@foreach($studentSubmissions as $item)
    @if($item['submission'])
        <div class="modal fade" id="gradeModal{{ $item['submission']->id }}" tabindex="-1" aria-labelledby="gradeModal{{ $item['submission']->id }}Label" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="gradeModal{{ $item['submission']->id }}Label">
                            <i class="fas fa-edit me-2"></i>
                            Grade Assignment - {{ $item['student']->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('teacher.submissions.grade', $item['submission']->id) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <h6>Student Submission:</h6>
                                @if($item['submission']->submission_content)
                                    <div class="border p-3 bg-light mb-3">
                                        {{ $item['submission']->submission_content }}
                                    </div>
                                @endif
                                @if($item['submission']->file_path)
                                    <div class="border p-3 bg-light">
                                        <h6><i class="fas fa-file me-2"></i>Uploaded File:</h6>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <span class="me-3">{{ basename($item['submission']->file_path) }}</span>
                                            <a href="{{ route('teacher.submissions.download', $item['submission']->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i>
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="marks_obtained{{ $item['submission']->id }}" class="form-label">Marks Obtained (Max: {{ $assignment->total_marks }})</label>
                                        <input type="number" class="form-control @error('marks_obtained') is-invalid @enderror" id="marks_obtained{{ $item['submission']->id }}" name="marks_obtained" min="0" max="{{ $assignment->total_marks }}" value="{{ old('marks_obtained', $item['submission']->marks_obtained ?? '') }}" required>
                                        @error('marks_obtained')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Total Marks</label>
                                        <input type="text" class="form-control" value="{{ $assignment->total_marks }}" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="teacher_feedback{{ $item['submission']->id }}" class="form-label">Teacher Feedback</label>
                                <textarea class="form-control @error('teacher_feedback') is-invalid @enderror" id="teacher_feedback{{ $item['submission']->id }}" name="teacher_feedback" rows="3">{{ old('teacher_feedback', $item['submission']->teacher_feedback ?? '') }}</textarea>
                                @error('teacher_feedback')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Save Grade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar-sticky {
    position: relative;
    top: 0;
    height: calc(100vh - 48px);
    padding-top: .5rem;
    overflow-x: hidden;
    overflow-y: auto;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.text-xs {
    font-size: .7rem;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-uppercase {
    text-transform: uppercase !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

@media (max-width: 767.98px) {
    .sidebar {
        top: 5rem;
    }
}
</style>
@endsection
