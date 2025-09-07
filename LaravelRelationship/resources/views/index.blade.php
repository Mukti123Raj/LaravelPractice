@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard</h2>
    @if(Auth::user()->role === 'teacher')
        <h4>Student Details</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Classroom</th>
                    <th>Teacher ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->classroom }}</td>
                    <td>{{ $student->teacher_id }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif(Auth::user()->role === 'student')
        <h4>Your Details</h4>
        <ul class="list-group">
            <li class="list-group-item"><strong>Name:</strong> {{ Auth::user()->name }}</li>
            <li class="list-group-item"><strong>Email:</strong> {{ Auth::user()->email }}</li>
            <li class="list-group-item"><strong>Classroom:</strong> {{ Auth::user()->classroom }}</li>
        </ul>
    @endif
</div>
@endsection
