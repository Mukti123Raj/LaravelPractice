@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <!-- Error Icon -->
                    <div class="mb-4">
                        <i class="fas fa-exclamation-circle text-danger" style="font-size: 5rem;"></i>
                    </div>
                    
                    <!-- Error Message -->
                    <h1 class="display-4 text-danger mb-3">500</h1>
                    <h2 class="h3 text-gray-800 mb-4">Internal Server Error</h2>
                    <p class="lead text-muted mb-4">
                        Something went wrong on our end. We're working to fix this issue.
                    </p>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <button onclick="goBack()" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Go Back
                        </button>
                        
                        @auth
                            @if(Auth::user()->role === 'teacher')
                                <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Teacher Dashboard
                                </a>
                            @elseif(Auth::user()->role === 'student')
                                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Student Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login
                            </a>
                        @endauth
                        
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home me-2"></i>
                            Home
                        </a>
                    </div>
                    
                    <!-- Additional Help -->
                    <div class="mt-5">
                        <p class="text-muted small">
                            If this problem persists, please contact the administrator with the error details.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
}

.btn {
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 500;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .btn-lg {
        padding: 10px 20px;
        font-size: 1rem;
    }
    
    .d-flex.gap-3 {
        flex-direction: column;
        align-items: center;
    }
    
    .d-flex.gap-3 .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<script>
function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // If no history, redirect to appropriate dashboard
        @auth
            @if(Auth::user()->role === 'teacher')
                window.location.href = "{{ route('teacher.dashboard') }}";
            @elseif(Auth::user()->role === 'student')
                window.location.href = "{{ route('student.dashboard') }}";
            @else
                window.location.href = "{{ url('/') }}";
            @endif
        @else
            window.location.href = "{{ url('/') }}";
        @endauth
    }
}
</script>
@endsection
