<?php
session_start();

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'i');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Role from dropdown

    // Basic validation
    if (empty($username) || empty($password) || empty($role)) {
        $error = "Please fill in all fields.";
    } else {
        // Validate role
        $allowed_roles = ['student', 'staff'];
        if (!in_array($role, $allowed_roles)) {
            $error = "Invalid role selected.";
        } else {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = "Username already exists. Please choose another.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Insert new user with role
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashed_password, $role);
                if ($stmt->execute()) {
                    // Auto-login the user and redirect
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    // For student, mark profile incomplete and redirect to profile completion page
                    if ($role === 'student') {
                        $_SESSION['student_profile_complete'] = false;
                        header("Location: std_upload.php");
                        exit;
                    } else {
                        // For staff or others, go to login page or index
                        header("Location: stlogin.php");
                        exit;
                    }
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register with Role</title>
</head>
<body>
<h2>Register</h2>

<?php
if ($error) {
    echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>";
}
if ($success) {
    echo "<p style='color:green;'>" . htmlspecialchars($success) . "</p>";
}
?>

<form action="register.php" method="POST">
    <label>Username:</label>
    <input type="text" name="username" required><br><br>
    <label>Password:</label>
    <input type="password" name="password" required><br><br>
    <label>Role:</label>
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="student">Student</option>
        <option value="staff">Staff</option>
    </select><br><br>
    <button type="submit">Register</button>
</form>
</body>
</html>