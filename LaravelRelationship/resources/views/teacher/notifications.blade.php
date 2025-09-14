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
                        <a class="nav-link" href="#">
                            <i class="fas fa-door-open me-2"></i>
                            Classrooms
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('teacher.subjects.index') }}">
                            <i class="fas fa-book me-2"></i>
                            Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user-graduate me-2"></i>
                            Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('teacher.notifications') }}">
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
                <h1 class="h2">Notifications</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-outline-primary me-2" id="markAllReadBtn">
                        <i class="fas fa-check-double me-1"></i>
                        Mark All as Read
                    </button>
                    <button class="btn btn-outline-danger" id="clearAllBtn">
                        <i class="fas fa-trash me-1"></i>
                        Clear All
                    </button>
                </div>
            </div>

            <div id="notificationsContainer">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                    <p class="text-muted">Loading notifications...</p>
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

.notification-card {
    border-left: 4px solid #dee2e6;
    transition: all 0.2s ease;
}

.notification-card.unread {
    border-left-color: #007bff;
    background-color: #f8f9fa;
}

.notification-card:hover {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.notification-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

.type-assignment_created {
    background-color: #d1ecf1;
    color: #0c5460;
}

.type-assignment_submitted {
    background-color: #fff3cd;
    color: #856404;
}

.type-assignment_graded {
    background-color: #d4edda;
    color: #155724;
}

@media (max-width: 767.98px) {
    .sidebar {
        top: 5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationsContainer = document.getElementById('notificationsContainer');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');
    
    // Load notifications
    function loadNotifications() {
        fetch('{{ route("teacher.notifications") }}')
            .then(response => response.json())
            .then(data => {
                renderNotifications(data.notifications.data || []);
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading notifications. Please try again.
                    </div>
                `;
            });
    }
    
    // Render notifications
    function renderNotifications(notifications) {
        if (notifications.length === 0) {
            notificationsContainer.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No notifications</h4>
                    <p class="text-muted">You're all caught up! Check back later for new notifications.</p>
                </div>
            `;
            return;
        }
        
        const html = notifications.map(notification => {
            const isUnread = !notification.read_at;
            const timeAgo = getTimeAgo(notification.created_at);
            const typeClass = `type-${notification.data.type || 'default'}`;
            
            return `
                <div class="card notification-card mb-3 ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${notification.data.message || 'New notification'}</h6>
                            <div>
                                <span class="notification-type-badge ${typeClass}">${getTypeLabel(notification.data.type)}</span>
                                ${isUnread ? '<span class="badge bg-primary ms-1">New</span>' : ''}
                            </div>
                        </div>
                        <p class="card-text text-muted small mb-2">${timeAgo}</p>
                        <div class="d-flex gap-2">
                            ${isUnread ? `
                                <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="${notification.id}">
                                    <i class="fas fa-check me-1"></i> Mark as Read
                                </button>
                            ` : ''}
                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${notification.id}">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        notificationsContainer.innerHTML = html;
        
        // Add event listeners
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                markAsRead(this.dataset.id);
            });
        });
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                deleteNotification(this.dataset.id);
            });
        });
    }
    
    // Mark notification as read
    function markAsRead(notificationId) {
        fetch(`{{ route('teacher.notifications.mark-read', ':id') }}`.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
    
    // Delete notification
    function deleteNotification(notificationId) {
        if (confirm('Are you sure you want to delete this notification?')) {
            fetch(`{{ route('teacher.notifications.delete', ':id') }}`.replace(':id', notificationId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error deleting notification:', error);
            });
        }
    }
    
    // Mark all as read
    markAllReadBtn.addEventListener('click', function() {
        fetch('{{ route("teacher.notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
        });
    });
    
    // Clear all notifications
    clearAllBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete all notifications?')) {
            fetch('{{ route("teacher.notifications.delete-all") }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error clearing all notifications:', error);
            });
        }
    });
    
    // Helper functions
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
        if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' days ago';
        return date.toLocaleDateString();
    }
    
    function getTypeLabel(type) {
        const labels = {
            'assignment_created': 'New Assignment',
            'assignment_submitted': 'Submission',
            'assignment_graded': 'Graded'
        };
        return labels[type] || 'Notification';
    }
    
    // Load notifications on page load
    loadNotifications();
});
</script>
@endsection
