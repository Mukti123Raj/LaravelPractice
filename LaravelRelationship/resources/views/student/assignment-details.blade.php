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
                <h1 class="h2">{{ $assignment->title }}</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('student.assignments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Assignments
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

            <!-- Submission Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-upload me-2"></i>
                                Your Submission
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($submission)
                                <!-- Existing Submission -->
                                <div class="mb-3">
                                    <h6>Submission Status:</h6>
                                    @if($submission->status === 'graded')
                                        <span class="badge bg-success">Graded</span>
                                    @else
                                        <span class="badge bg-warning">Submitted</span>
                                    @endif
                                    <span class="text-muted ms-2">
                                        Submitted on: {{ $submission->submitted_at->format('M d, Y H:i') }}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <h6>Your Submission:</h6>
                                    <div class="border p-3 bg-light">
                                        {{ $submission->submission_content }}
                                    </div>
                                </div>

                                @if($submission->marks_obtained !== null)
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-star me-2"></i>Grade Received</h6>
                                        <div class="h4 text-primary">
                                            {{ $submission->marks_obtained }}/{{ $assignment->total_marks }}
                                        </div>
                                        @if($submission->teacher_feedback)
                                            <div class="mt-2">
                                                <strong>Teacher Feedback:</strong>
                                                <p class="mb-0">{{ $submission->teacher_feedback }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($submission->status !== 'graded' && !$assignment->due_date->isPast())
                                    <div class="mt-3">
                                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateSubmissionModal">
                                            <i class="fas fa-edit me-1"></i>
                                            Update Submission
                                        </button>
                                    </div>
                                @endif
                            @else
                                <!-- No Submission -->
                                @if($assignment->due_date->isPast())
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Assignment deadline has passed. You can no longer submit.
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-clock me-2"></i>
                                        You haven't submitted this assignment yet. Submit before {{ $assignment->due_date->format('M d, Y H:i') }}.
                                    </div>
                                    
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitModal">
                                        <i class="fas fa-upload me-1"></i>
                                        Submit Assignment
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="submitModal" tabindex="-1" aria-labelledby="submitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitModalLabel">
                    <i class="fas fa-upload me-2"></i>
                    Submit Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('student.assignments.submit', $assignment->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="submission_content" class="form-label">Your Submission</label>
                        <textarea class="form-control" id="submission_content" name="submission_content" rows="8" required placeholder="Write your assignment submission here..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Make sure to review your submission before submitting. You can update it later if the deadline hasn't passed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>
                        Submit Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Submission Modal -->
@if($submission && $submission->status !== 'graded' && !$assignment->due_date->isPast())
<div class="modal fade" id="updateSubmissionModal" tabindex="-1" aria-labelledby="updateSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateSubmissionModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    Update Submission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('student.assignments.update', $assignment->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="update_submission_content" class="form-label">Your Submission</label>
                        <textarea class="form-control" id="update_submission_content" name="submission_content" rows="8" required>{{ $submission->submission_content }}</textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Updating your submission will replace the previous version.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Update Submission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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
