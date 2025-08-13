/**
 * Global JavaScript for Pengaduan Application
 * This file handles global CSRF protection for fetch requests
 */

// Original fetch
const originalFetch = window.fetch;

// Override fetch to include CSRF token in all requests
window.fetch = function(url, options = {}) {
    // Initialize headers if not already set
    if (!options.headers) {
        options.headers = {};
    }
    
    // Check if headers is a Headers object and convert to plain object if needed
    if (options.headers instanceof Headers) {
        const plainHeaders = {};
        for (const [key, value] of options.headers.entries()) {
            plainHeaders[key] = value;
        }
        options.headers = plainHeaders;
    }
    
    // Add CSRF token for non-GET requests if not already set
    if (options.method && options.method.toLowerCase() !== 'get') {
        options.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
    
    // Call original fetch
    return originalFetch(url, options);
};

// Helper function to display notifications
function showNotification(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}
