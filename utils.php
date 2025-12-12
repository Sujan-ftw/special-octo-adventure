<?php
// utilities.php
if (!function_exists('h')) {
    /**
     * Escape HTML for secure output
     */
    function h($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Add global helpers here like database connections or permissions.
function connectDatabase() {
    $conn = new mysqli('localhost', 'root', '', 'iqac');
    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }
    return $conn;
}

// Other helper functions can go here
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}