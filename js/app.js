/**
 * Global JavaScript for Pengaduan Application
 * This file handles global CSRF protection for fetch requests
 */

// Original fetch
const originalFetch = window.fetch;

// Override fetch to include CSRF token in all requests
window.fetch = function(url, options = {}) {
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Clone options to avoid modifying the original object
    options = { ...options };
    
    // Initialize headers if they don't exist
    if (!options.headers) {
        options.headers = {};
    }
    
    // Add X-CSRF-TOKEN header for all non-GET requests
    if (csrfToken && options.method && options.method.toUpperCase() !== 'GET') {
        options.headers['X-CSRF-TOKEN'] = csrfToken;
        
        // If using FormData, don't set content-type as browser will set it with boundary
        if (!(options.body instanceof FormData)) {
            options.headers['Content-Type'] = options.headers['Content-Type'] || 'application/json';
        }
    }
    
    // Call original fetch with modified options
    return originalFetch(url, options);
};

// Global AJAX setup for jQuery
if (typeof $ !== 'undefined' && $.ajax) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
}

// Helper function to format dates
function formatDate(dateString) {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
