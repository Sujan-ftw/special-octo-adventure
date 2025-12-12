<?php
// dp_connection.php
// Database connection parameters
// NOTE: These settings MUST match your XAMPP/MySQL configuration.
// If your MySQL requires a password, change $db_password.
$host = 'localhost'; 
$db_user = 'root'; 
$db_password = ''; // Default XAMPP password is empty
$db_name = 'iqac'; // Name of the IQAC database

// Create connection
$conn = new mysqli($host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    // Fatal error handling
    die("Database Connection Failed: " . $conn->connect_error . 
        ". Please ensure MySQL is running in XAMPP and the credentials in dp_connection.php are correct.");
}

// Set charset
$conn->set_charset("utf8mb4");

// Define a function to sanitize output data
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>