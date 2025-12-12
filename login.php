<?php
session_start();
require_once 'utils.php'; // Include utilities

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process login
    $conn = connectDatabase();
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            header("Location: index.php");
            exit();
        } else {
            $message = "Invalid username or password!";
        }
    } else {
        $message = "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <form action="login.php" method="post">
        <div><?= h($message); ?></div>
        <label>Username:</label><input type="text" name="username">
        <label>Password:</label><input type="password" name="password">
        <button type="submit">Login</button>
    </form>
</body>
</html>