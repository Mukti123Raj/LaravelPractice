@extends('layouts.app')

@section('content')
<x-student-navbar />

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('student.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('student.subjects') }}">
                            <i class="fas fa-book me-2"></i>
                            My Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('student.assignments') }}">
                            <i class="fas fa-tasks me-2"></i>
                            Assignments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-chart-line me-2"></i>
                            Grades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Schedule
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Assignments</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-info fs-6">{{ $assignments->count() }} Total Assignments</span>
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

            <!-- Assignments List -->
            <div class="row">
                @forelse($assignments as $item)
                    @php
                        $assignment = $item['assignment'];
                        $submission = $item['submission'];
                        $status = $item['status'];
                        $subject = $item['subject'];
                    @endphp
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-tasks me-2"></i>
                                    {{ $assignment->title }}
                                </h6>
                                <div>
                                    @if($status === 'done_with_marks')
                                        <span class="badge bg-success">Done with Marks</span>
                                    @elseif($status === 'submitted')
                                        <span class="badge bg-primary">Submitted</span>
                                    @elseif($status === 'late_submit')
                                        <span class="badge bg-warning">Late Submit</span>
                                    @elseif($status === 'due_soon')
                                        <span class="badge bg-warning">Due Soon</span>
                                    @elseif($status === 'overdue')
                                        <span class="badge bg-danger">Overdue</span>
                                    @else
                                        <span class="badge bg-info">Not Submitted</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted">Subject: {{ $subject->name }}</h6>
                                    <p class="card-text">{{ Str::limit($assignment->description, 100) }}</p>
                                </div>
                                
                                <div class="row text-muted small mb-3">
                                    <div class="col-6">
                                        <i class="fas fa-calendar me-1"></i>
                                        Due: {{ $assignment->due_date->format('M d, Y H:i') }}
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-star me-1"></i>
                                        Total Marks: {{ $assignment->total_marks }}
                                    </div>
                                </div>

                                @if($submission)
                                    <div class="mb-3">
                                        <h6 class="text-success">Submission Status:</h6>
                                        <p class="text-muted small">
                                            Submitted: {{ $submission->submitted_at->format('M d, Y H:i') }}
                                        </p>
                                        @if($submission->marks_obtained !== null)
                                            <div class="alert alert-info py-2">
                                                <strong>Marks: {{ $submission->marks_obtained }}/{{ $assignment->total_marks }}</strong>
                                                @if($submission->teacher_feedback)
                                                    <br><small>{{ $submission->teacher_feedback }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="d-grid">
                                    <a href="{{ route('student.assignments.show', $assignment->id) }}" class="btn btn-primary">
                                        <i class="fas fa-eye me-1"></i>
                                        View Assignment
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No assignments yet</h4>
                            <p class="text-muted">You don't have any assignments from your enrolled subjects.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Statistics -->
            @if($assignments->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-bar me-2"></i>
                                Assignment Statistics
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 text-center">
                                    <div class="border-left-success p-3">
                                        <div class="h4 text-success">{{ $assignments->where('status', 'done_with_marks')->count() }}</div>
                                        <div class="text-muted">Done with Marks</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-primary p-3">
                                        <div class="h4 text-primary">{{ $assignments->where('status', 'submitted')->count() }}</div>
                                        <div class="text-muted">Submitted</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-warning p-3">
                                        <div class="h4 text-warning">{{ $assignments->where('status', 'late_submit')->count() }}</div>
                                        <div class="text-muted">Late Submit</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-warning p-3">
                                        <div class="h4 text-warning">{{ $assignments->where('status', 'due_soon')->count() }}</div>
                                        <div class="text-muted">Due Soon</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-danger p-3">
                                        <div class="h4 text-danger">{{ $assignments->where('status', 'overdue')->count() }}</div>
                                        <div class="text-muted">Overdue</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-info p-3">
                                        <div class="h4 text-info">{{ $assignments->count() }}</div>
                                        <div class="text-muted">Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </main>
    </div>
</div>

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

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
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
