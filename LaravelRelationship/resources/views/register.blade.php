@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Register</h2>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label for="role" class="form-label">Register as</label>
                    <select id="role" name="role" class="form-select" onchange="toggleFields()">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <!-- Student specific fields -->
                <div id="student-fields">
                    <div class="mb-3">
                        <label for="classroom" class="form-label">Classroom</label>
                        <input type="text" class="form-control" id="classroom" name="classroom">
                    </div>
                </div>
                <!-- Teacher specific fields -->
                <div id="teacher-fields" style="display:none;">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>
</div>
<script>
function toggleFields() {
    var role = document.getElementById('role').value;
    document.getElementById('student-fields').style.display = role === 'student' ? 'block' : 'none';
    document.getElementById('teacher-fields').style.display = role === 'teacher' ? 'block' : 'none';
}
</script>
@endsection
