@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Mark Attendance - {{ $subject->name }}</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('teacher.attendance.store', $subject) }}">
        @csrf

        <div class="mb-3">
            <label for="attendance_date" class="form-label">Date</label>
            <input type="date" id="attendance_date" name="attendance_date" class="form-control" value="{{ old('attendance_date', $today) }}" required>
            @error('attendance_date')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Students</span>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAll(true)">Mark All Present</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAll(false)">Mark All Absent</button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="text-center">Present</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>{{ $student->name }}</td>
                                <td class="text-center" style="width: 140px;">
                                    <input type="checkbox" name="attendance[{{ $student->id }}]" value="1">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No students enrolled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save Attendance</button>
            <a href="{{ route('teacher.attendance.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<script>
function toggleAll(val) {
    document.querySelectorAll('input[type="checkbox"][name^="attendance["]').forEach(cb => cb.checked = val);
}
</script>
@endsection


