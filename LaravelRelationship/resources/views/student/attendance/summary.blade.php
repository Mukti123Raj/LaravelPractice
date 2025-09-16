@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">My Attendance</h1>

    <div class="card">
        <div class="card-header">Summary</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th class="text-center">Total Lectures</th>
                        <th class="text-center">Attended</th>
                        <th class="text-center">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($summary as $row)
                        <tr>
                            <td>{{ $row->subject_name ?? optional($row->subject)->name }}</td>
                            <td class="text-center">{{ (int) $row->total_lectures }}</td>
                            <td class="text-center">{{ (int) $row->attended }}</td>
                            <td class="text-center">
                                @php
                                    $pct = $row->total_lectures > 0 ? round(($row->attended / $row->total_lectures) * 100) : 0;
                                @endphp
                                {{ $pct }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No attendance records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
