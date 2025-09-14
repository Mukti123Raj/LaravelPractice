<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('student.dashboard') }}">
            <i class="fas fa-graduation-cap me-2"></i>
            Student Portal
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNavbar" aria-controls="studentNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="studentNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.subjects*') ? 'active' : '' }}" href="{{ route('student.subjects') }}">
                        <i class="fas fa-book me-1"></i>
                        My Subjects
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.assignments*') ? 'active' : '' }}" href="{{ route('student.assignments') }}">
                        <i class="fas fa-tasks me-1"></i>
                        Assignments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.grades*') ? 'active' : '' }}" href="#">
                        <i class="fas fa-chart-line me-1"></i>
                        Grades
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('student.schedule*') ? 'active' : '' }}" href="#">
                        <i class="fas fa-calendar-alt me-1"></i>
                        Schedule
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <x-notification-bell user-role="student" />
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="studentDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="studentDropdown">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
