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
                        <a class="nav-link active" href="{{ route('student.subjects') }}">
                            <i class="fas fa-book me-2"></i>
                            My Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
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
                <h1 class="h2">My Subjects</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="fas fa-plus me-1"></i>
                        Add New Subject
                    </button>
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

            <!-- Student Info Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Student Information</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ Auth::user()->name }}
                                    </div>
                                    <div class="text-muted">
                                        Classroom: {{ optional($student?->classroom)->name ?? 'Not assigned' }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolled Subjects -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-book me-2"></i>
                                Enrolled Subjects ({{ $student?->subjects->count() ?? 0 }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @forelse(($student?->subjects ?? []) as $subject)
                                <div class="card mb-3 border-left-primary">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="card-title mb-1">
                                                    <i class="fas fa-book me-2 text-primary"></i>
                                                    {{ $subject->name }}
                                                </h5>
                                                <p class="card-text text-muted mb-2">
                                                    <i class="fas fa-chalkboard-teacher me-1"></i>
                                                    <strong>Teacher:</strong> {{ optional($subject->teacher)->name ?? 'Not assigned' }}
                                                </p>
                                                <p class="card-text text-muted mb-0">
                                                    <i class="fas fa-door-open me-1"></i>
                                                    <strong>Classroom:</strong> {{ optional($subject->classroom)->name ?? 'Not assigned' }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-success fs-6 mb-2">Enrolled</span>
                                                <br>
                                                <form method="POST" action="{{ route('student.subjects.remove') }}" class="d-inline" onsubmit="return confirm('Are you sure you want to unenroll from {{ $subject->name }}?');">
                                                    @csrf
                                                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-times me-1"></i>
                                                        Unenroll
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-book fa-4x text-muted mb-3"></i>
                                    <h4 class="text-muted">No subjects enrolled yet</h4>
                                    <p class="text-muted">Click "Add New Subject" to enroll in subjects from your classroom.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Subjects -->
            @if($availableSubjects->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-plus-circle me-2"></i>
                                Available Subjects in Your Classroom ({{ $availableSubjects->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($availableSubjects as $subject)
                                <div class="card mb-3 border-left-success">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="card-title mb-1">
                                                    <i class="fas fa-book me-2 text-success"></i>
                                                    {{ $subject->name }}
                                                </h5>
                                                <p class="card-text text-muted mb-2">
                                                    <i class="fas fa-chalkboard-teacher me-1"></i>
                                                    <strong>Teacher:</strong> {{ optional($subject->teacher)->name ?? 'Not assigned' }}
                                                </p>
                                                <p class="card-text text-muted mb-0">
                                                    <i class="fas fa-door-open me-1"></i>
                                                    <strong>Classroom:</strong> {{ optional($subject->classroom)->name ?? 'Not assigned' }}
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <span class="badge bg-warning fs-6 mb-2">Available</span>
                                                <br>
                                                <form method="POST" action="{{ route('student.subjects.add') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-plus me-1"></i>
                                                        Enroll
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </main>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubjectModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>
                    Add New Subject
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($availableSubjects->count() > 0)
                    <p class="text-muted mb-3">Select a subject from your classroom to enroll:</p>
                    <div class="row">
                        @foreach($availableSubjects as $subject)
                            <div class="col-md-6 mb-3">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-book me-2 text-success"></i>
                                            {{ $subject->name }}
                                        </h6>
                                        <p class="card-text text-muted small mb-2">
                                            <i class="fas fa-chalkboard-teacher me-1"></i>
                                            Teacher: {{ optional($subject->teacher)->name ?? 'Not assigned' }}
                                        </p>
                                        <form method="POST" action="{{ route('student.subjects.add') }}">
                                            @csrf
                                            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                <i class="fas fa-plus me-1"></i>
                                                Enroll Now
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                        <h5 class="text-muted">No Available Subjects</h5>
                        <p class="text-muted">There are no more subjects available in your classroom to enroll in.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
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

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
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
