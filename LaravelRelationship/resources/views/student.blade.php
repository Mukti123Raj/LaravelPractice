@extends('layouts.app')

@section('content')
<x-student-navbar />

<div class="container">
    <h2 class="mb-4">Student Dashboard</h2>
    <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Name:</strong> {{ Auth::user()->name }}</li>
        <li class="list-group-item"><strong>Email:</strong> {{ Auth::user()->email }}</li>
        <li class="list-group-item"><strong>Classroom:</strong> {{ optional($student?->classroom)->name ?? '—' }}</li>
    </ul>

    <div class="card">
        <div class="card-header">Your Subjects and Teachers</div>
        <ul class="list-group list-group-flush">
            @forelse(($student?->subjects ?? []) as $subject)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>{{ $subject->name }}</span>
                    <small class="text-muted">Teacher: {{ optional($subject->teacher)->name ?? '—' }}</small>
                </li>
            @empty
                <li class="list-group-item text-muted">No subjects selected yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
