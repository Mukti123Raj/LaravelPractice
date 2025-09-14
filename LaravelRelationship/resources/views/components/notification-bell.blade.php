@props(['userRole' => 'teacher'])

<div class="dropdown">
    <button class="btn btn-outline-light position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">
            0
        </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="markAllReadBtn" style="display: none;">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
                <button class="btn btn-sm btn-outline-danger" id="clearAllBtn" style="display: none;">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <div id="notificationsList">
            <li class="dropdown-item text-center text-muted">
                <i class="fas fa-spinner fa-spin"></i> Loading notifications...
            </li>
        </div>
        <li><hr class="dropdown-divider"></li>
        <li class="dropdown-item text-center">
            <a href="{{ $userRole === 'teacher' ? route('teacher.notifications') : route('student.notifications') }}" class="text-decoration-none">
                View All Notifications
            </a>
        </li>
    </ul>
</div>

<style>
.notification-dropdown {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 3px solid #2196f3;
}

.notification-content {
    font-size: 14px;
    line-height: 1.4;
}

.notification-time {
    font-size: 12px;
    color: #6c757d;
    margin-top: 4px;
}

.notification-actions {
    margin-top: 8px;
}

.notification-actions .btn {
    font-size: 12px;
    padding: 2px 8px;
}

#notificationBadge {
    font-size: 10px;
    min-width: 18px;
    height: 18px;
    line-height: 16px;
}

.dropdown-menu {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.15);
}

.notification-type-badge {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
}

.type-assignment_created {
    background-color: #d4edda;
    color: #155724;
}

.type-assignment_submitted {
    background-color: #fff3cd;
    color: #856404;
}

.type-assignment_graded {
    background-color: #cce5ff;
    color: #004085;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationsList = document.getElementById('notificationsList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');
    
    let notifications = [];
    let unreadCount = 0;
    
    // Load notifications
    function loadNotifications() {
        const route = '{{ $userRole === "teacher" ? route("teacher.notifications.unread-count") : route("student.notifications.unread-count") }}';
        
        fetch(route)
            .then(response => response.json())
            .then(data => {
                unreadCount = data.count;
                updateBadge();
                loadNotificationList();
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
    }
    
    // Load notification list
    function loadNotificationList() {
        const route = '{{ $userRole === "teacher" ? route("teacher.notifications") : route("student.notifications") }}';
        
        fetch(route)
            .then(response => response.json())
            .then(data => {
                notifications = data.notifications.data || [];
                renderNotifications();
                updateActionButtons();
            })
            .catch(error => {
                console.error('Error loading notification list:', error);
                notificationsList.innerHTML = '<li class="dropdown-item text-center text-muted">Error loading notifications</li>';
            });
    }
    
    // Render notifications
    function renderNotifications() {
        if (notifications.length === 0) {
            notificationsList.innerHTML = '<li class="dropdown-item text-center text-muted">No notifications</li>';
            return;
        }
        
        const html = notifications.slice(0, 5).map(notification => {
            const isUnread = !notification.read_at;
            const timeAgo = getTimeAgo(notification.created_at);
            const typeClass = `type-${notification.data.type || 'default'}`;
            
            return `
                <li class="dropdown-item notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                    <div class="notification-content">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="fw-bold">${notification.data.message || 'New notification'}</span>
                            <span class="notification-type-badge ${typeClass}">${getTypeLabel(notification.data.type)}</span>
                        </div>
                        <div class="notification-time">${timeAgo}</div>
                        ${isUnread ? `
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="${notification.id}">
                                    <i class="fas fa-check"></i> Mark Read
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${notification.id}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        ` : ''}
                    </div>
                </li>
            `;
        }).join('');
        
        notificationsList.innerHTML = html;
        
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
    
    // Update badge
    function updateBadge() {
        if (unreadCount > 0) {
            notificationBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            notificationBadge.style.display = 'block';
        } else {
            notificationBadge.style.display = 'none';
        }
    }
    
    // Update action buttons
    function updateActionButtons() {
        const hasUnread = notifications.some(n => !n.read_at);
        const hasNotifications = notifications.length > 0;
        
        markAllReadBtn.style.display = hasUnread ? 'inline-block' : 'none';
        clearAllBtn.style.display = hasNotifications ? 'inline-block' : 'none';
    }
    
    // Mark notification as read
    function markAsRead(notificationId) {
        const route = '{{ $userRole === "teacher" ? route("teacher.notifications.mark-read", ":id") : route("student.notifications.mark-read", ":id") }}'.replace(':id', notificationId);
        
        fetch(route, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                unreadCount = Math.max(0, unreadCount - 1);
                updateBadge();
                loadNotificationList();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
    
    // Delete notification
    function deleteNotification(notificationId) {
        const route = '{{ $userRole === "teacher" ? route("teacher.notifications.delete", ":id") : route("student.notifications.delete", ":id") }}'.replace(':id', notificationId);
        
        fetch(route, {
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
    
    // Mark all as read
    markAllReadBtn.addEventListener('click', function() {
        const route = '{{ $userRole === "teacher" ? route("teacher.notifications.mark-all-read") : route("student.notifications.mark-all-read") }}';
        
        fetch(route, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                unreadCount = 0;
                updateBadge();
                loadNotificationList();
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
        });
    });
    
    // Clear all notifications
    clearAllBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete all notifications?')) {
            const route = '{{ $userRole === "teacher" ? route("teacher.notifications.delete-all") : route("student.notifications.delete-all") }}';
            
            fetch(route, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    unreadCount = 0;
                    updateBadge();
                    loadNotificationList();
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
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
        if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + 'd ago';
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
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
});
</script>
