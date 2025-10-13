@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Trashed Assignments</h1>
    
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
    </div>

    @if($trashedAssignments->isEmpty())
        <p>The trash is empty.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Deleted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trashedAssignments as $assignment)
                    <tr>
                        <td>{{ $assignment->title }}</td>
                        <td>{{ $assignment->subject->name }}</td>
                        <td>{{ $assignment->deleted_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <form action="{{ route('teacher.assignments.restore', $assignment->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Restore</button>
                            </form>
                            
                            <form action="{{ route('teacher.assignments.forceDelete', $assignment->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete this assignment? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete Permanently</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
