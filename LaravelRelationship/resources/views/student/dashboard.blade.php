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
                        <a class="nav-link active" href="{{ route('student.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
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
                <h1 class="h2">Student Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="badge bg-success fs-6">Active Student</span>
                </div>
            </div>

            <!-- Welcome Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Welcome Back!</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        Hello, {{ Auth::user()->name }}!
                                    </div>
                                    <div class="text-muted">
                                        You are enrolled in {{ optional($student?->classroom)->name ?? 'No classroom' }}
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

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Enrolled Subjects</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $student?->subjects->count() ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
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
                                        Total Assignments</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $assignmentStats['total'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tasks fa-2x text-gray-300"></i>
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
                                        Average Grade</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">N/A</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Attendance</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">100%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Statistics Card -->
            @if(isset($assignmentStats) && $assignmentStats['total'] > 0)
            <div class="row mb-4">
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
                                        <div class="h4 text-success">{{ $assignmentStats['done_with_marks'] }}</div>
                                        <div class="text-muted">Done with Marks</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-primary p-3">
                                        <div class="h4 text-primary">{{ $assignmentStats['submitted'] }}</div>
                                        <div class="text-muted">Submitted</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-warning p-3">
                                        <div class="h4 text-warning">{{ $assignmentStats['late_submit'] }}</div>
                                        <div class="text-muted">Late Submit</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-warning p-3">
                                        <div class="h4 text-warning">{{ $assignmentStats['due_soon'] }}</div>
                                        <div class="text-muted">Due Soon</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-danger p-3">
                                        <div class="h4 text-danger">{{ $assignmentStats['overdue'] }}</div>
                                        <div class="text-muted">Overdue</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="border-left-info p-3">
                                        <div class="h4 text-info">{{ $assignmentStats['total'] }}</div>
                                        <div class="text-muted">Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Student Information -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Name:</strong></span>
                                    <span>{{ Auth::user()->name }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Email:</strong></span>
                                    <span>{{ Auth::user()->email }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Student ID:</strong></span>
                                    <span>{{ $student?->id ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><strong>Classroom:</strong></span>
                                    <span>{{ optional($student?->classroom)->name ?? 'Not assigned' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('student.subjects') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-book me-2"></i>
                                    View My Subjects
                                </a>
                                <a href="{{ route('student.assignments') }}" class="btn btn-outline-info">
                                    <i class="fas fa-tasks me-2"></i>
                                    Check Assignments
                                </a>
                                <button class="btn btn-outline-success" type="button">
                                    <i class="fas fa-chart-line me-2"></i>
                                    View Grades
                                </button>
                                <button class="btn btn-outline-warning" type="button">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    View Schedule
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subjects and Teachers -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Subjects and Teachers</h6>
                </div>
                <div class="card-body">
                    @forelse(($student?->subjects ?? []) as $subject)
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                            <div>
                                <h6 class="mb-1">{{ $subject->name }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-chalkboard-teacher me-1"></i>
                                    Teacher: {{ optional($subject->teacher)->name ?? 'Not assigned' }}
                                </small>
                            </div>
                            <div>
                                <span class="badge bg-primary">Active</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No subjects enrolled yet</h5>
                            <p class="text-muted">Contact your teacher to get enrolled in subjects.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Logged in to Student Portal</h6>
                                <p class="timeline-text text-muted">Welcome back to your dashboard</p>
                                <small class="text-muted">{{ now()->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @if($student?->subjects->count() > 0)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Enrolled in {{ $student->subjects->count() }} subject(s)</h6>
                                <p class="timeline-text text-muted">You are currently enrolled in subjects</p>
                                <small class="text-muted">Recently</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
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

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
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

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
}

@media (max-width: 767.98px) {
    .sidebar {
        top: 5rem;
    }
}
</style>
@endsection
