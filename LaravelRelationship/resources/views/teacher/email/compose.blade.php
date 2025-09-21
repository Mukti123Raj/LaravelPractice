@extends('layouts.app')

@section('content')
<x-teacher-navbar />

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('teacher.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('teacher.subjects.index') }}">
                            <i class="fas fa-book me-2"></i>
                            Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('teacher.students.index') }}">
                            <i class="fas fa-user-graduate me-2"></i>
                            Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('teacher.email.compose') }}">
                            <i class="fas fa-envelope me-2"></i>
                            Send Email
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('teacher.notifications') }}">
                            <i class="fas fa-bell me-2"></i>
                            Notifications
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-envelope me-2"></i>
                    Send Email to Students
                </h1>
            </div>


            <!-- Email Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-edit me-2"></i>
                                Compose Email
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('teacher.email.send') }}" id="emailForm">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="classroom_id" class="form-label">Select Classroom</label>
                                            <select class="form-select @error('classroom_id') is-invalid @enderror" id="classroom_id" name="classroom_id">
                                                <option value="">Choose a classroom...</option>
                                                @foreach($classrooms as $classroom)
                                                    <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                                        {{ $classroom->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('classroom_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="to" class="form-label">To (Recipients)</label>
                                    <div id="email-container">
                                        <div class="input-group mb-2">
                                            <input type="email" class="form-control" id="email-input" placeholder="Enter email address">
                                            <button type="button" class="btn btn-outline-primary" id="add-email-btn">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                        <div id="email-list" class="border rounded p-2" style="min-height: 100px; max-height: 200px; overflow-y: auto;">
                                            <p class="text-muted mb-0">No email addresses added yet. Select a classroom or add manually.</p>
                                        </div>
                                    </div>
                                    <!-- Hidden inputs for form submission -->
                                    <div id="hidden-inputs"></div>
                                    @error('to')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('to.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Select a classroom above to load student emails, or add email addresses manually.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="cc" class="form-label">CC (Optional)</label>
                                    <input type="text" class="form-control @error('cc') is-invalid @enderror" id="cc" name="cc" value="{{ old('cc') }}" placeholder="Enter CC email addresses separated by commas">
                                    @error('cc')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('cc.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Separate multiple email addresses with commas (e.g., teacher@example.com, admin@example.com)
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Enter email subject" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="body" class="form-label">Email Body</label>
                                    <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="10" placeholder="Enter your email message here..." required>{{ old('body') }}</textarea>
                                    @error('body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        Back to Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i>
                                        Send Email
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar-sticky {
    position: relative;
    top: 0;
    height: calc(100vh - 48px);
    padding-top: .5rem;
    overflow-x: hidden;
    overflow-y: auto;
}

@media (max-width: 767.98px) {
    .sidebar {
        top: 5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classroomSelect = document.getElementById('classroom_id');
    const emailInput = document.getElementById('email-input');
    const addEmailBtn = document.getElementById('add-email-btn');
    const emailList = document.getElementById('email-list');
    const hiddenInputs = document.getElementById('hidden-inputs');
    const ccInput = document.getElementById('cc');
    const emailForm = document.getElementById('emailForm');

    let emailAddresses = new Set();

    // Handle classroom selection change
    classroomSelect.addEventListener('change', function() {
        const classroomId = this.value;
        
        if (classroomId) {
            // Show loading state
            emailList.innerHTML = '<p class="text-muted mb-0">Loading students...</p>';
            
            // Fetch students for the selected classroom
            fetch(`{{ route('teacher.email.getStudentsByClass') }}?classroom_id=${classroomId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(emails => {
                    // Clear existing emails and add classroom emails
                    emailAddresses.clear();
                    emails.forEach(email => {
                        emailAddresses.add(email);
                    });
                    updateEmailList();
                })
                .catch(error => {
                    console.error('Error fetching students:', error);
                    emailList.innerHTML = '<p class="text-danger mb-0">Error loading students</p>';
                });
        } else {
            // Clear emails when no classroom is selected
            emailAddresses.clear();
            updateEmailList();
        }
    });

    // Handle adding email manually
    addEmailBtn.addEventListener('click', function() {
        const email = emailInput.value.trim();
        if (email && isValidEmail(email)) {
            emailAddresses.add(email);
            emailInput.value = '';
            updateEmailList();
        } else {
            alert('Please enter a valid email address');
        }
    });

    // Handle Enter key in email input
    emailInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addEmailBtn.click();
        }
    });

    // Update email list display
    function updateEmailList() {
        if (emailAddresses.size === 0) {
            emailList.innerHTML = '<p class="text-muted mb-0">No email addresses added yet. Select a classroom or add manually.</p>';
        } else {
            emailList.innerHTML = '';
            emailAddresses.forEach(email => {
                const emailItem = document.createElement('div');
                emailItem.className = 'd-flex justify-content-between align-items-center mb-1 p-2 bg-light rounded';
                emailItem.innerHTML = `
                    <span class="text-truncate me-2">${email}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEmail('${email}')">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                emailList.appendChild(emailItem);
            });
        }
    }

    // Remove email function (global scope for onclick)
    window.removeEmail = function(email) {
        emailAddresses.delete(email);
        updateEmailList();
    };

    // Email validation
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Handle form submission
    emailForm.addEventListener('submit', function(e) {
        // Create hidden inputs for each email
        hiddenInputs.innerHTML = '';
        emailAddresses.forEach(email => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'to[]';
            hiddenInput.value = email;
            hiddenInputs.appendChild(hiddenInput);
        });

        // Handle CC input - convert comma-separated emails to array format
        const ccValue = ccInput.value.trim();
        if (ccValue) {
            const ccEmails = ccValue.split(',').map(email => email.trim()).filter(email => email);
            ccEmails.forEach(email => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'cc[]';
                hiddenInput.value = email;
                hiddenInputs.appendChild(hiddenInput);
            });
            ccInput.name = '';
        }

        // Validate that at least one email is selected
        if (emailAddresses.size === 0) {
            e.preventDefault();
            alert('Please add at least one email address');
            return false;
        }
    });
});
</script>
@endsection

