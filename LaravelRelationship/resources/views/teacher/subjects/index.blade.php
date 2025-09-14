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
                        <a class="nav-link active" href="{{ route('teacher.subjects.index') }}">
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
                <h1 class="h2">My Subjects</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-plus me-1"></i>
                        Create New Subject
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

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Subjects</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $subjects->count() }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Assignments</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $subjects->sum('assignments_count') }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tasks fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Total Students</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $subjects->sum('students_count') }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Active Subjects</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $subjects->where('assignments_count', '>', 0)->count() }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subjects Grid -->
            <div class="row">
                @forelse($subjects as $subject)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-book me-2"></i>
                                    {{ $subject->name }}
                                </h6>
                                <span class="badge bg-primary">{{ $subject->assignments->count() }} assignments</span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-door-open me-2 text-muted"></i>
                                        <span class="text-muted">Classroom:</span>
                                        <span class="ms-2 fw-bold">{{ optional($subject->classroom)->name ?? 'Not assigned' }}</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-user-graduate me-2 text-muted"></i>
                                        <span class="text-muted">Students:</span>
                                        <span class="ms-2 fw-bold">{{ $subject->students->count() }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-tasks me-2 text-muted"></i>
                                        <span class="text-muted">Assignments:</span>
                                        <span class="ms-2 fw-bold">{{ $subject->assignments->count() }}</span>
                                    </div>
                                </div>
                                
                                @if($subject->assignments->count() > 0)
                                    <div class="mb-3">
                                        <h6 class="text-muted mb-2">Recent Assignments:</h6>
                                        @foreach($subject->assignments->take(2) as $assignment)
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-truncate me-2" style="max-width: 150px;" title="{{ $assignment->title }}">
                                                    {{ $assignment->title }}
                                                </span>
                                                <small class="text-muted">
                                                    {{ $assignment->due_date->format('M d') }}
                                                </small>
                                            </div>
                                        @endforeach
                                        @if($subject->assignments->count() > 2)
                                            <small class="text-muted">+{{ $subject->assignments->count() - 2 }} more</small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('teacher.subjects.show', $subject->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>
                                        View Details
                                    </a>
                                    @if($subject->assignments->count() > 0)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">No Assignments</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-book fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No subjects yet</h4>
                                <p class="text-muted mb-4">You haven't created any subjects yet. Create your first subject to get started.</p>
                                <a href="{{ route('teacher.dashboard') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    Create Your First Subject
                                </a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
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

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
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

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.btn {
    transition: all 0.2s ease-in-out;
}
</style>
@endsection
