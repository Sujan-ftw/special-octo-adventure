<?php
require_once 'auth_check.php';
require_once 'dp_connection.php';
require_role('student');

$user_id = get_user_id();
$message = '';
$error = '';

// Get student info
$student_id = null;
$student_info = null;
$stmt = $conn->prepare("SELECT id, first_name, last_name, department, year_class FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $student_info = $result->fetch_assoc();
    $student_id = $student_info['id'];
}
$stmt->close();

// Check for existing registrations and their statuses
$registrations = [];
$has_approved_initial = false;
if ($student_id) {
    $stmt = $conn->prepare("SELECT * FROM student_registrations WHERE student_id = ? ORDER BY submitted_date DESC");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $registrations[] = $row;
        if ($row['form_type'] === 'initial' && $row['status'] === 'approved') {
            $has_approved_initial = true;
        }
    }
    $stmt->close();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_registration'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } elseif (!$student_id) {
        $error = "Please complete your student profile first.";
    } else {
        $form_type = $_POST['form_type'] ?? '';
        $activity_interest = trim($_POST['activity_interest'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        
        if (empty($form_type) || empty($activity_interest) || empty($reason)) {
            $error = "Please fill in all fields.";
        } else {
            // Store form data as JSON
            $form_data = json_encode([
                'activity_interest' => $activity_interest,
                'reason' => $reason,
                'department' => $student_info['department'] ?? '',
                'year_class' => $student_info['year_class'] ?? ''
            ]);
            
            $stmt = $conn->prepare("INSERT INTO student_registrations (student_id, user_id, form_type, form_data, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iiss", $student_id, $user_id, $form_type, $form_data);
            
            if ($stmt->execute()) {
                $message = "Registration form submitted successfully! Awaiting staff approval.";
                // Refresh registrations
                $stmt2 = $conn->prepare("SELECT * FROM student_registrations WHERE student_id = ? ORDER BY submitted_date DESC");
                $stmt2->bind_param("i", $student_id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $registrations = [];
                while ($row = $result->fetch_assoc()) {
                    $registrations[] = $row;
                    if ($row['form_type'] === 'initial' && $row['status'] === 'approved') {
                        $has_approved_initial = true;
                    }
                }
                $stmt2->close();
            } else {
                $error = "Failed to submit registration. Please try again.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PSG Polytechnic</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --psg-blue: #002c77;
            --psg-yellow: #f4c20d;
            --light-text: #ffffff;
            --gradient-primary: linear-gradient(135deg, #002c77, #004499, #0066cc);
            --border-radius: 20px;
            --shadow-medium: 0 8px 20px rgba(0,0,0,0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f9f9f9, #e0e0e0);
            color: #1a1a1a;
            line-height: 1.6;
            min-height: 100vh;
        }
        nav {
            background: var(--gradient-primary);
            padding: 1rem 1.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-medium);
        }
        .nav-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--light-text);
        }
        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .nav-links a {
            padding: 10px 18px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            color: var(--light-text);
            transition: all 0.4s;
        }
        .nav-links a:hover {
            background: var(--psg-yellow);
            color: var(--psg-blue);
        }
        main {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .section-box {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow-medium);
            margin-bottom: 25px;
        }
        h2 {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 20px;
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
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .btn-submit {
            padding: 14px 30px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
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
        .registration-status {
            margin-top: 30px;
        }
        .status-item {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            border-left: 5px solid;
        }
        .status-pending {
            border-left-color: #ffc107;
        }
        .status-approved {
            border-left-color: #28a745;
        }
        .status-rejected {
            border-left-color: #dc3545;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 10px;
        }
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        .badge-approved {
            background: #d4edda;
            color: #155724;
        }
        .badge-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        footer {
            text-align: center;
            padding: 20px;
            background: #333;
            color: white;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-title"><i class="fa-solid fa-graduation-cap"></i> PSG Polytechnic</div>
        <div class="nav-links">
            <a href="student_home.php"><i class="fa-solid fa-home"></i> Home</a>
            <a href="student_about.php"><i class="fa-solid fa-circle-info"></i> About</a>
            <a href="student_departments.php"><i class="fa-solid fa-building"></i> Departments</a>
            <a href="student_gallery.php"><i class="fa-solid fa-images"></i> Gallery</a>
            <a href="student_register.php"><i class="fa-solid fa-file-pen"></i> Register</a>
            <a href="student_marks.php"><i class="fa-solid fa-chart-line"></i> Marks</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <main>
        <div class="section-box">
            <h2><i class="fa-solid fa-file-pen"></i> Activity Registration</h2>
            
            <?php if ($error): ?>
                <div class="message error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="message success"><?= h($message) ?></div>
            <?php endif; ?>

            <?php if (!$student_info): ?>
                <p class="message error">Please complete your student profile before registering for activities.</p>
            <?php else: ?>
                <form method="POST" action="student_register.php">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <div class="form-group">
                        <label for="form_type"><i class="fa-solid fa-list"></i> Registration Type</label>
                        <select id="form_type" name="form_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="initial">Initial Registration</option>
                            <?php if ($has_approved_initial): ?>
                                <option value="sports_club">Sports Club</option>
                                <option value="cultural_club">Cultural Club</option>
                                <option value="technical_club">Technical Club</option>
                            <?php endif; ?>
                        </select>
                        <?php if (!$has_approved_initial): ?>
                            <small style="color: #666;">Additional activities will be unlocked after initial registration approval.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="activity_interest"><i class="fa-solid fa-star"></i> Activity/Interest</label>
                        <input type="text" id="activity_interest" name="activity_interest" placeholder="e.g., Football, Coding Club, Drama" required>
                    </div>

                    <div class="form-group">
                        <label for="reason"><i class="fa-solid fa-comment"></i> Reason for Registration</label>
                        <textarea id="reason" name="reason" placeholder="Explain why you want to register for this activity..." required></textarea>
                    </div>

                    <button type="submit" name="submit_registration" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> Submit Registration
                    </button>
                </form>

                <?php if (!empty($registrations)): ?>
                    <div class="registration-status">
                        <h3>My Registration Status</h3>
                        <?php foreach ($registrations as $reg): ?>
                            <?php
                            $statusClass = 'status-' . $reg['status'];
                            $badgeClass = 'badge-' . $reg['status'];
                            $form_data = json_decode($reg['form_data'], true);
                            ?>
                            <div class="status-item <?= $statusClass ?>">
                                <strong><?= h(ucfirst(str_replace('_', ' ', $reg['form_type']))) ?></strong>
                                <span class="badge <?= $badgeClass ?>"><?= h(ucfirst($reg['status'])) ?></span>
                                <p><strong>Activity:</strong> <?= h($form_data['activity_interest'] ?? 'N/A') ?></p>
                                <p><strong>Submitted:</strong> <?= h($reg['submitted_date']) ?></p>
                                <?php if ($reg['status'] === 'approved' && $reg['reviewed_date']): ?>
                                    <p><strong>Approved on:</strong> <?= h($reg['reviewed_date']) ?></p>
                                <?php elseif ($reg['status'] === 'rejected' && $reg['comments']): ?>
                                    <p><strong>Reason:</strong> <?= h($reg['comments']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        &copy; 2025 PSG Polytechnic College. All Rights Reserved.
    </footer>
</body>
</html>
