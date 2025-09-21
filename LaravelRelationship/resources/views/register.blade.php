@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-4">Register</h2>
            <form method="POST" action="{{ route('register') }}">
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
                    <label for="role" class="form-label">Register as</label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" onchange="toggleFields()">
                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
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
                <!-- Student specific fields -->
                <div id="student-fields">
                    <div class="mb-3">
                        <label for="classroom_id" class="form-label">Classroom</label>
                        <select class="form-select @error('classroom_id') is-invalid @enderror" id="classroom_id" name="classroom_id">
                            <option value="">Select Classroom</option>
                            @foreach(\App\Models\Classroom::all() as $classroom)
                                <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                        @error('classroom_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="subject_ids" class="form-label">Choose Subjects (optional)</label>
                        <select class="form-select @error('subject_ids') is-invalid @enderror" id="subject_ids" name="subject_ids[]" multiple>
                            @foreach(\App\Models\Subject::with('teacher','classroom')->get() as $subject)
                                <option value="{{ $subject->id }}" {{ in_array($subject->id, old('subject_ids', [])) ? 'selected' : '' }}>{{ $subject->name }} ({{ optional($subject->classroom)->name }}, {{ optional($subject->teacher)->name }})</option>
                            @endforeach
                        </select>
                        @error('subject_ids')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">You can pick subjects and teachers now or later.</div>
                    </div>
                </div>
                <!-- Teacher specific fields -->
                <div id="teacher-fields" style="display:none;"></div>
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
