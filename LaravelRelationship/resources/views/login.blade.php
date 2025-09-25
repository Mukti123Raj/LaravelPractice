@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Login</h2>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="mb-3">
                    <label for="role" class="form-label">Login as</label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" onchange="toggleFields()">
                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <!-- Student specific fields (optional for login) -->
                <div id="student-fields" style="display:block;"></div>
                <!-- Teacher specific fields (optional for login) -->
                <div id="teacher-fields" style="display:none;"></div>
                <div class="mb-3 text-end">
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </div>
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
