@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Login</h2>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="role" class="form-label">Login as</label>
                    <select id="role" name="role" class="form-select" onchange="toggleFields()">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <!-- Student specific fields (optional for login) -->
                <div id="student-fields" style="display:block;"></div>
                <!-- Teacher specific fields (optional for login) -->
                <div id="teacher-fields" style="display:none;"></div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
                        <div class="mt-3 text-center">
                            <span>Don't have an account?</span>
                            <a href="{{ route('register') }}">Register here</a>
                        </div>
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
