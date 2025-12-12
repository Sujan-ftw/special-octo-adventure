<?php
// Registration page with role selection
session_start();
session_regenerate_id(true);

require_once 'dp_connection.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'student') {
        header('Location: student_home.php');
    } else {
        header('Location: staff_home.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? '';

        // Validation
        if (empty($username) || empty($password) || empty($role)) {
            $error = "Please fill in all fields.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif (!in_array($role, ['student', 'staff'])) {
            $error = "Invalid role selected.";
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Username already exists. Please choose another.";
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashed_password, $role);
                
                if ($stmt->execute()) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
            $stmt->close();
        }
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PSG Polytechnic College</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --psg-blue: #002c77;
            --psg-yellow: #f4c20d;
            --light-text: #ffffff;
            --gradient-primary: linear-gradient(135deg, #002c77, #004499, #0066cc);
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f9f9f9, #e0e0e0);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h1 {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .register-header p {
            color: #666;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--psg-blue);
            font-weight: 600;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--psg-blue);
        }
        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 44, 119, 0.3);
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .login-link a {
            color: var(--psg-blue);
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1><i class="fa-solid fa-user-plus"></i> Register</h1>
            <p>Create your account</p>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?= h($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message success"><?= h($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="auth_register.php">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password" minlength="6">
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fa-solid fa-lock"></i> Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" minlength="6">
            </div>

            <div class="form-group">
                <label for="role"><i class="fa-solid fa-user-tag"></i> Role</label>
                <select id="role" name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="student">Student</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

            <button type="submit" class="btn-register">
                <i class="fa-solid fa-user-plus"></i> Register
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="auth_login.php">Login here</a>
        </div>
    </div>
</body>
</html>
