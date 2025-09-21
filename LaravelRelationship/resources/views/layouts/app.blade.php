<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">Laravel Relationship</a>
        </div>
    </nav>
    <main>
        @yield('content')
    </main>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="successToastBody">
                <!-- Message will be inserted here -->
            </div>
        </div>
        
        <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="errorToastBody">
                <!-- Message will be inserted here -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Session Messages Data -->
    <script>
        window.sessionMessages = {
            success: @json(session('success')),
            error: @json(session('error')),
            validationErrors: @json($errors->all())
        };
    </script>
    
    <script>
        // Toast notification functions
        function showSuccessToast(message) {
            const toastElement = document.getElementById('successToast');
            const toastBody = document.getElementById('successToastBody');
            toastBody.textContent = message;
            
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000
            });
            toast.show();
        }

        function showErrorToast(message) {
            const toastElement = document.getElementById('errorToast');
            const toastBody = document.getElementById('errorToastBody');
            toastBody.textContent = message;
            
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 7000
            });
            toast.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Check for success message
            if (window.sessionMessages.success) {
                showSuccessToast(window.sessionMessages.success);
            }

            // Check for error message
            if (window.sessionMessages.error) {
                showErrorToast(window.sessionMessages.error);
            }

            // Check for validation errors
            if (window.sessionMessages.validationErrors && window.sessionMessages.validationErrors.length > 0) {
                window.sessionMessages.validationErrors.forEach(function(error) {
                    showErrorToast(error);
                });
            }
        });
    </script>
</body>
</html>
