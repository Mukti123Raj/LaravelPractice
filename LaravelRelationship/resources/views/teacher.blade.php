@extends('layouts.app')

@section('content')
<x-teacher-navbar />

<div class="container">
    <h2 class="mb-4">Teacher Dashboard</h2>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Your Details</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageModal">Create / Assign</button>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Classrooms</div>
                <ul class="list-group list-group-flush">
                    @forelse($classrooms as $classroom)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $classroom->name }}</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary">Students: {{ $classroom->students_count }}</span>
                                <form method="POST" action="{{ route('teacher.classroom.delete', $classroom->id) }}" onsubmit="return confirm('Delete classroom {{ $classroom->name }}? This will remove its students due to cascade.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No classrooms yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Subjects</div>
                <ul class="list-group list-group-flush">
                    @forelse($subjects as $subject)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $subject->name }}</span>
                            <small class="text-muted">Classroom: {{ optional($subject->classroom)->name ?? '—' }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No subjects yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Students In Your Classes</div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Classroom</th>
                        <th>Classroom ID</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>{{ $student->id }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ optional($student->classroom)->name ?? '—' }}</td>
                            <td>{{ $student->classroom_id }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">No students found for your classrooms.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="manageModal" tabindex="-1" aria-labelledby="manageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageModalLabel">Create or Assign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="manageTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="create-classroom-tab" data-bs-toggle="tab" data-bs-target="#create-classroom" type="button" role="tab">Create Classroom</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="create-subject-tab" data-bs-toggle="tab" data-bs-target="#create-subject" type="button" role="tab">Create Subject</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="create-classroom" role="tabpanel">
                            <form method="POST" action="{{ route('teacher.classroom.create') }}" class="p-2">
                                @csrf
                                <div class="input-group">
                                    <input type="text" name="name" class="form-control" placeholder="New Classroom Name" required>
                                    <button type="submit" class="btn btn-primary">Create</button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="create-subject" role="tabpanel">
                            <form method="POST" action="{{ route('teacher.subject.create') }}" class="p-2">
                                @csrf
                                <div class="mb-3">
                                    <input type="text" name="name" class="form-control" placeholder="New Subject Name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="classroom_id" class="form-label">Assign to Classroom</label>
                                    <select name="classroom_id" id="classroom_id" class="form-select" required>
                                        <option value="">Select Classroom</option>
                                        @foreach($allClassrooms as $classroom)
                                            <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Create</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
