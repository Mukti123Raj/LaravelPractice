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
                <div>
                    <h1 class="h2">{{ $assignment->title }}</h1>
                    <div class="mt-2">
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
                                    @if($submission->status === 'done_with_marks')
                                        <span class="badge bg-success">Done with Marks</span>
                                    @elseif($submission->status === 'submitted')
                                        <span class="badge bg-primary">Submitted</span>
                                    @elseif($submission->status === 'late_submit')
                                        <span class="badge bg-warning">Late Submit</span>
                                    @else
                                        <span class="badge bg-warning">Submitted</span>
                                    @endif
                                    <span class="text-muted ms-2">
                                        Submitted on: {{ $submission->submitted_at->format('M d, Y H:i') }}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <h6>Your Submission:</h6>
                                    @if($submission->submission_content)
                                        <div class="border p-3 bg-light mb-3">
                                            {{ $submission->submission_content }}
                                        </div>
                                    @endif
                                    @if($submission->file_path)
                                        <div class="border p-3 bg-light">
                                            <h6><i class="fas fa-file me-2"></i>Uploaded File:</h6>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                                <span class="me-3">{{ basename($submission->file_path) }}</span>
                                                <a href="{{ route('student.assignments.download', $submission->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download me-1"></i>
                                                    Download
                                                </a>
                                            </div>
                                        </div>
                                    @endif
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

                                @if($submission->status !== 'done_with_marks' && !$assignment->due_date->isPast())
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
            <form method="POST" action="{{ route('student.assignments.submit', $assignment->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="submission_content" class="form-label">Your Submission (Text)</label>
                        <textarea class="form-control @error('submission_content') is-invalid @enderror" id="submission_content" name="submission_content" rows="6" placeholder="Write your assignment submission here...">{{ old('submission_content') }}</textarea>
                        @error('submission_content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="submission_file" class="form-label">Upload File (Optional)</label>
                        <input type="file" class="form-control @error('submission_file') is-invalid @enderror" id="submission_file" name="submission_file" accept=".pdf,.doc,.docx">
                        @error('submission_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('submission')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Supported formats: PDF, DOC, DOCX. Maximum size: 1MB
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You can submit text, a file, or both. Make sure to review your submission before submitting. You can update it later if the deadline hasn't passed.
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
@if($submission && $submission->status !== 'done_with_marks' && !$assignment->due_date->isPast())
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
            <form method="POST" action="{{ route('student.assignments.update', $assignment->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Display general errors -->
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="update_submission_content" class="form-label">Your Submission (Text)</label>
                        <textarea class="form-control @error('submission_content') is-invalid @enderror" id="update_submission_content" name="submission_content" rows="6">{{ old('submission_content', $submission->submission_content) }}</textarea>
                        @error('submission_content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="update_submission_file" class="form-label">Upload New File (Optional)</label>
                        <input type="file" class="form-control @error('submission_file') is-invalid @enderror" id="update_submission_file" name="submission_file" accept=".pdf,.doc,.docx">
                        @error('submission_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('submission')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Supported formats: PDF, DOC, DOCX. Maximum size: 1MB
                        </div>
                        @if($submission->file_path)
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-file me-1"></i>
                                    Current file: {{ basename($submission->file_path) }}
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Updating your submission will replace the previous version. If you upload a new file, it will replace the existing one.
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
