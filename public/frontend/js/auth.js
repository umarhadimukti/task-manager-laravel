/**
 * Authentication related functions
 */

// API Base URL - update to your backend URL
const API_BASE_URL = '/api';

/**
 * Attempt user login with email and password
 * @param {string} email - User email
 * @param {string} password - User password
 * @returns {Promise} - Promise resolving to login response
 */
async function login(email, password) {
    try {
        const response = await fetch(`${API_BASE_URL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Login failed');
        }

        return await response.json();
    } catch (error) {
        console.error('Login error:', error);
        throw error;
    }
}

/**
 * Check if user is authenticated
 * @returns {boolean} - True if user is authenticated
 */
function isAuthenticated() {
    const token = localStorage.getItem('token');
    return !!token;
}

/**
 * Get current user data from localStorage
 * @returns {Object|null} - User object or null if not logged in
 */
function getCurrentUser() {
    const userString = localStorage.getItem('user');
    return userString ? JSON.parse(userString) : null;
}

/**
 * Get authentication token
 * @returns {string|null} - The authentication token or null
 */
function getToken() {
    return localStorage.getItem('token');
}

/**
 * Logout current user by removing token and user data
 */
function logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = 'index.html';
}

/**
 * Make authenticated API request
 * @param {string} endpoint - API endpoint
 * @param {Object} options - Fetch options
 * @returns {Promise} - Promise resolving to API response
 */
async function apiRequest(endpoint, options = {}) {
    // Default options
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getToken()}`
        }
    };

    // Merge options
    const requestOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {})
        }
    };

    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, requestOptions);

        // Check for token expiration or auth issues
        if (response.status === 401) {
            // Clear auth and redirect to login
            logout();
            throw new Error('Authentication expired. Please login again.');
        }

        // Handle 4xx and 5xx errors
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Request failed with status ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error(`API error (${endpoint}):`, error);
        throw error;
    }
}

// Check auth state on page load
document.addEventListener('DOMContentLoaded', function() {
    // If on login page, check if already logged in
    if (window.location.pathname.includes('index.html') || window.location.pathname === '/') {
        if (isAuthenticated()) {
            window.location.href = 'dashboard.html';
        }
    } 
    // If on any protected page, check auth
    else {
        if (!isAuthenticated()) {
            window.location.href = 'index.html';
        }
    }
});