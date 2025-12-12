<?php
require_once 'auth_check.php';
require_once 'dp_connection.php';

// Require staff role
require_role('staff');

$user_id = get_user_id();
$username = get_username();

// Fetch staff details if they exist
$staff_info = null;
$stmt = $conn->prepare("SELECT * FROM staff_details WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $staff_info = $result->fetch_assoc();
}
$stmt->close();

// Count pending registrations
$pending_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM student_registrations WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $pending_count = $row['count'];
}
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Home - PSG Polytechnic</title>
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
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
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
            align-items: center;
            gap: 8px;
        }
        .nav-links a {
            padding: 10px 18px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            color: var(--light-text);
            transition: all 0.4s;
            position: relative;
        }
        .nav-links a:hover {
            background: var(--psg-yellow);
            color: var(--psg-blue);
            transform: translateY(-3px) scale(1.05);
        }
        .badge-notify {
            position: absolute;
            top: 0;
            right: 0;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }
        main {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .welcome-box {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow-medium);
            margin-bottom: 30px;
            text-align: center;
        }
        .welcome-box h1 {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .dashboard-card i {
            font-size: 3rem;
            color: var(--psg-blue);
            margin-bottom: 15px;
        }
        .dashboard-card h3 {
            color: var(--psg-blue);
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        .dashboard-card p {
            color: #666;
            font-size: 0.95rem;
        }
        .card-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #dc3545;
            color: white;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.85rem;
            font-weight: 600;
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
        <div class="nav-title"><i class="fa-solid fa-chalkboard-user"></i> PSG Polytechnic - Staff Portal</div>
        <div class="nav-links">
            <a href="staff_home.php"><i class="fa-solid fa-home"></i> Home</a>
            <a href="staff_gallery_upload.php"><i class="fa-solid fa-upload"></i> Upload Gallery</a>
            <a href="staff_department_content.php"><i class="fa-solid fa-file-circle-plus"></i> Department Content</a>
            <a href="staff_approve_forms.php" style="position: relative;">
                <i class="fa-solid fa-clipboard-check"></i> Approve Forms
                <?php if ($pending_count > 0): ?>
                    <span class="badge-notify"><?= $pending_count ?></span>
                <?php endif; ?>
            </a>
            <a href="staff_marks_entry.php"><i class="fa-solid fa-pen-to-square"></i> Enter Marks</a>
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <main>
        <div class="welcome-box">
            <h1>Welcome, <?= h($username) ?>!</h1>
            <p>Staff Portal Dashboard</p>
        </div>

        <?php if ($staff_info): ?>
        <div class="welcome-box">
            <h3>Your Information</h3>
            <p><strong>Name:</strong> <?= h($staff_info['first_name'] . ' ' . $staff_info['last_name']) ?></p>
            <?php if ($staff_info['department']): ?>
                <p><strong>Department:</strong> <?= h($staff_info['department']) ?></p>
            <?php endif; ?>
            <?php if ($staff_info['designation']): ?>
                <p><strong>Designation:</strong> <?= h($staff_info['designation']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <a href="staff_gallery_upload.php" class="dashboard-card">
                <i class="fa-solid fa-images"></i>
                <h3>Gallery Upload</h3>
                <p>Upload images to the college gallery</p>
            </a>

            <a href="staff_department_content.php" class="dashboard-card">
                <i class="fa-solid fa-file-circle-plus"></i>
                <h3>Department Content</h3>
                <p>Manage department information</p>
            </a>

            <a href="staff_approve_forms.php" class="dashboard-card">
                <?php if ($pending_count > 0): ?>
                    <span class="card-badge"><?= $pending_count ?> Pending</span>
                <?php endif; ?>
                <i class="fa-solid fa-clipboard-check"></i>
                <h3>Approve Forms</h3>
                <p>Review and approve student registrations</p>
            </a>

            <a href="staff_marks_entry.php" class="dashboard-card">
                <i class="fa-solid fa-pen-to-square"></i>
                <h3>Enter Marks</h3>
                <p>Enter marks for allocated subjects</p>
            </a>
        </div>
    </main>

    <footer>
        &copy; 2025 PSG Polytechnic College. All Rights Reserved.
    </footer>
</body>
</html>
