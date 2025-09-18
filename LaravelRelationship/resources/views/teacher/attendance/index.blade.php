@extends('layouts.app')

@section('content')
<x-teacher-navbar />
<div class="container">
    <h1 class="mb-4">Attendance</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-header">Your Subjects</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Classroom</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($subjects as $subject)
                    <tr>
                        <td>{{ $subject->name }}</td>
                        <td>{{ optional($subject->classroom)->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('teacher.attendance.show', $subject) }}" class="btn btn-primary btn-sm">Mark Attendance</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No subjects found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


