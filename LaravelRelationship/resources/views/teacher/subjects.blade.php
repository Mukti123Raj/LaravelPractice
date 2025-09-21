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
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('teacher.subjects.index') }}">Subjects</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $subject->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="h2">{{ $subject->name }} - Subject Details</h1>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('teacher.subjects.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Subjects
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                        <i class="fas fa-plus me-1"></i>
                        Create Assignment
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

            <!-- Subject Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Subject Information</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $subject->name }}
                                    </div>
                                    <div class="text-muted">
                                        Classroom: {{ optional($subject->classroom)->name ?? 'Not assigned' }} | 
                                        Enrolled Students: {{ $subject->students->count() }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-tasks me-2"></i>
                                Assignments ({{ $subject->assignments->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            @forelse($subject->assignments as $assignment)
                                <div class="card mb-3 border-left-info">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="card-title mb-1">
                                                    <i class="fas fa-tasks me-2 text-info"></i>
                                                    {{ $assignment->title }}
                                                </h5>
                                                <p class="card-text text-muted mb-2">
                                                    {{ Str::limit($assignment->description, 100) }}
                                                </p>
                                                <div class="row text-muted small">
                                                    <div class="col-md-4">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Due: {{ $assignment->due_date->format('M d, Y H:i') }}
                                                    </div>
                                                    <div class="col-md-4">
                                                        <i class="fas fa-star me-1"></i>
                                                        Total Marks: {{ $assignment->total_marks }}
                                                    </div>
                                                    <div class="col-md-4">
                                                        <i class="fas fa-users me-1"></i>
                                                        Submissions: {{ $assignment->submissions->count() }}/{{ $subject->students->count() }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="mb-2">
                                                    @if($assignment->due_date->isPast())
                                                        <span class="badge bg-danger">Overdue</span>
                                                    @elseif($assignment->due_date->diffInDays(now()) <= 1)
                                                        <span class="badge bg-warning">Due Soon</span>
                                                    @else
                                                        <span class="badge bg-success">Active</span>
                                                    @endif
                                                </div>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('teacher.assignments.show', $assignment->id) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye me-1"></i>
                                                        View
                                                    </a>
                                                    <form method="POST" action="{{ route('teacher.assignments.delete', $assignment->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-trash me-1"></i>
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                                    <h4 class="text-muted">No assignments yet</h4>
                                    <p class="text-muted">Click "Create Assignment" to add your first assignment.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolled Students -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-user-graduate me-2"></i>
                                Enrolled Students ({{ $subject->students->count() }})
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Classroom</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($subject->students as $student)
                                            <tr>
                                                <td>{{ $student->id }}</td>
                                                <td>{{ $student->name }}</td>
                                                <td>{{ $student->email }}</td>
                                                <td>{{ optional($student->classroom)->name ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No students enrolled yet.</td>
                                            </tr>
                                        @endforelse
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

<!-- Create Assignment Modal -->
<div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-labelledby="createAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAssignmentModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>
                    Create New Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('teacher.assignments.create') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
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
                        <label for="title" class="form-label">Assignment Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="instructions" class="form-label">Instructions</label>
                        <textarea class="form-control @error('instructions') is-invalid @enderror" id="instructions" name="instructions" rows="4" required>{{ old('instructions') }}</textarea>
                        @error('instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="total_marks" class="form-label">Total Marks</label>
                                <input type="number" class="form-control @error('total_marks') is-invalid @enderror" id="total_marks" name="total_marks" min="1" value="{{ old('total_marks') }}" required>
                                @error('total_marks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Create Assignment
                    </button>
                </div>
            </form>
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

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
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

@media (max-width: 767.98px) {
    .sidebar {
        top: 5rem;
    }
}
</style>
@endsection
