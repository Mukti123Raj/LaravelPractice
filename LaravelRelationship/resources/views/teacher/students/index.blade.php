@extends('layouts.app')

@section('content')
<x-teacher-navbar />

<div class="container">
    <h1 class="mb-4">Students</h1>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Students in Your Classes</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Classroom</th>
                            <th class="text-center">Attendance</th>
                            <th class="text-center">Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php
                                $total = (int)($student->total_lectures ?? 0);
                                $attended = (int)($student->attended_lectures ?? 0);
                                $pct = $total > 0 ? round(($attended / $total) * 100) : 0;
                            @endphp
                            <tr>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->classroom_name ?? optional($student->classroom)->name }}</td>
                                <td class="text-center">
                                    {{ $attended }} / {{ $total }} ({{ $pct }}%)
                                </td>
                                <td class="text-center text-muted">—</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


