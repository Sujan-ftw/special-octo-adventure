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
// NOTE: For production use, database credentials should be stored in environment variables
// or a separate config file with restricted permissions, not hardcoded.
// Current configuration is suitable for local XAMPP development.
function connectDatabase() {
    $conn = new mysqli('localhost', 'root', '', 'i');
    if ($conn->connect_error) {
        die("Database Connection Failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Connect to MOU database
function connectMouDatabase() {
    $conn = new mysqli('localhost', 'root', '', 'm');
    if ($conn->connect_error) {
        die("MOU Database Connection Failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Other helper functions can go here
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}