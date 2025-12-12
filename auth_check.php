<?php
// Session validation and security check utility
// Include this file at the top of protected pages

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Session timeout (30 minutes)
$timeout_duration = 1800;

// Check if user is logged in
function require_login() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: auth_login.php');
        exit;
    }
    
    // Check session timeout
    global $timeout_duration;
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout_duration)) {
        session_unset();
        session_destroy();
        header('Location: auth_login.php?timeout=1');
        exit;
    }
    
    // Update last activity time
    $_SESSION['login_time'] = time();
}

// Check if user has specific role
function require_role($required_role) {
    require_login();
    
    if ($_SESSION['role'] !== $required_role) {
        header('Location: auth_login.php');
        exit;
    }
}

// Check if user is student
function is_student() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Check if user is staff
function is_staff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

// Get current user ID
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function get_username() {
    return $_SESSION['username'] ?? null;
}

// Get current user role
function get_user_role() {
    return $_SESSION['role'] ?? null;
}
