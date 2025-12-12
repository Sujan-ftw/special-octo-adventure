<?php
require_once 'auth_check.php';
require_once 'dp_connection.php';
require_role('staff');

$user_id = get_user_id();
$message = '';
$error = '';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $registration_id = intval($_POST['registration_id'] ?? 0);
    $action = $_POST['action'];
    $comments = trim($_POST['comments'] ?? '');
    
    if ($registration_id > 0 && in_array($action, ['approve', 'reject'])) {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $reviewed_date = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("UPDATE student_registrations SET status = ?, reviewed_date = ?, reviewed_by = ?, comments = ? WHERE id = ?");
        $stmt->bind_param("ssisi", $status, $reviewed_date, $user_id, $comments, $registration_id);
        
        if ($stmt->execute()) {
            $message = "Registration " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully!";
        } else {
            $error = "Failed to update registration status.";
        }
        $stmt->close();
    }
    }
}

// Fetch all pending registrations
$pending_registrations = [];
$stmt = $conn->prepare("
    SELECT sr.*, s.first_name, s.last_name, s.department, s.year_class, u.username
    FROM student_registrations sr
    JOIN students s ON sr.student_id = s.id
    JOIN users u ON sr.user_id = u.id
    WHERE sr.status = 'pending'
    ORDER BY sr.submitted_date ASC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pending_registrations[] = $row;
}
$stmt->close();

// Fetch all reviewed registrations
$reviewed_registrations = [];
$stmt = $conn->prepare("
    SELECT sr.*, s.first_name, s.last_name, s.department, s.year_class, u.username, u2.username as reviewed_by_username
    FROM student_registrations sr
    JOIN students s ON sr.student_id = s.id
    JOIN users u ON sr.user_id = u.id
    LEFT JOIN users u2 ON sr.reviewed_by = u2.id
    WHERE sr.status IN ('approved', 'rejected')
    ORDER BY sr.reviewed_date DESC
    LIMIT 20
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reviewed_registrations[] = $row;
}
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Forms - PSG Polytechnic</title>
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
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .registration-card {
            background: #f8f9fa;
            padding: 20px;
            margin: 15px 0;
            border-radius: 12px;
            border-left: 5px solid var(--psg-blue);
        }
        .registration-card h3 {
            color: var(--psg-blue);
            margin-bottom: 10px;
        }
        .student-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .info-item {
            padding: 8px 0;
        }
        .info-item strong {
            color: var(--psg-blue);
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-approve {
            background: #28a745;
            color: white;
        }
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        .comment-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            margin-top: 10px;
            font-family: inherit;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 10px;
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
        <div class="nav-title"><i class="fa-solid fa-chalkboard-user"></i> PSG Polytechnic</div>
        <div class="nav-links">
            <a href="staff_home.php"><i class="fa-solid fa-home"></i> Home</a>
            <a href="staff_gallery_upload.php"><i class="fa-solid fa-upload"></i> Upload Gallery</a>
            <a href="staff_department_content.php"><i class="fa-solid fa-file-circle-plus"></i> Department Content</a>
            <a href="staff_approve_forms.php"><i class="fa-solid fa-clipboard-check"></i> Approve Forms</a>
            <a href="staff_marks_entry.php"><i class="fa-solid fa-pen-to-square"></i> Enter Marks</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <main>
        <div class="section-box">
            <h2><i class="fa-solid fa-clipboard-check"></i> Pending Registration Forms</h2>
            
            <?php if ($error): ?>
                <div class="message error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="message success"><?= h($message) ?></div>
            <?php endif; ?>

            <?php if (!empty($pending_registrations)): ?>
                <?php foreach ($pending_registrations as $reg): ?>
                    <?php $form_data = json_decode($reg['form_data'], true); ?>
                    <div class="registration-card">
                        <h3><?= h(ucfirst(str_replace('_', ' ', $reg['form_type']))) ?> - <?= h($reg['first_name'] . ' ' . $reg['last_name']) ?></h3>
                        
                        <div class="student-info">
                            <div class="info-item">
                                <strong>Username:</strong> <?= h($reg['username']) ?>
                            </div>
                            <div class="info-item">
                                <strong>Department:</strong> <?= h($reg['department'] ?? 'N/A') ?>
                            </div>
                            <div class="info-item">
                                <strong>Year/Class:</strong> <?= h($reg['year_class'] ?? 'N/A') ?>
                            </div>
                            <div class="info-item">
                                <strong>Submitted:</strong> <?= h($reg['submitted_date']) ?>
                            </div>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Activity/Interest:</strong> <?= h($form_data['activity_interest'] ?? 'N/A') ?>
                        </div>
                        <div style="margin: 15px 0;">
                            <strong>Reason:</strong><br>
                            <?= nl2br(h($form_data['reason'] ?? 'N/A')) ?>
                        </div>
                        
                        <form method="POST" action="staff_approve_forms.php">
                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="registration_id" value="<?= $reg['id'] ?>">
                            <textarea name="comments" placeholder="Add comments (optional)" class="comment-input" rows="2"></textarea>
                            <div class="form-actions">
                                <button type="submit" name="action" value="approve" class="btn btn-approve">
                                    <i class="fa-solid fa-check"></i> Approve
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-reject">
                                    <i class="fa-solid fa-times"></i> Reject
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fa-solid fa-inbox"></i>
                    <p>No pending registration forms.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section-box">
            <h2><i class="fa-solid fa-history"></i> Recently Reviewed Forms</h2>
            
            <?php if (!empty($reviewed_registrations)): ?>
                <?php foreach ($reviewed_registrations as $reg): ?>
                    <?php 
                    $form_data = json_decode($reg['form_data'], true);
                    $badgeClass = $reg['status'] === 'approved' ? 'badge-approved' : 'badge-rejected';
                    ?>
                    <div class="registration-card">
                        <h3>
                            <?= h(ucfirst(str_replace('_', ' ', $reg['form_type']))) ?> - <?= h($reg['first_name'] . ' ' . $reg['last_name']) ?>
                            <span class="status-badge <?= $badgeClass ?>"><?= h(ucfirst($reg['status'])) ?></span>
                        </h3>
                        
                        <div class="student-info">
                            <div class="info-item">
                                <strong>Activity:</strong> <?= h($form_data['activity_interest'] ?? 'N/A') ?>
                            </div>
                            <div class="info-item">
                                <strong>Reviewed by:</strong> <?= h($reg['reviewed_by_username'] ?? 'N/A') ?>
                            </div>
                            <div class="info-item">
                                <strong>Reviewed on:</strong> <?= h($reg['reviewed_date']) ?>
                            </div>
                        </div>
                        
                        <?php if ($reg['comments']): ?>
                            <div style="margin-top: 10px;">
                                <strong>Comments:</strong> <?= h($reg['comments']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <p>No reviewed forms yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        &copy; 2025 PSG Polytechnic College. All Rights Reserved.
    </footer>
</body>
</html>
