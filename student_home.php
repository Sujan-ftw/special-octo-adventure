<?php
require_once 'auth_check.php';
require_once 'dp_connection.php';

// Require student role
require_role('student');

$user_id = get_user_id();
$username = get_username();

// Fetch student details if they exist
$student_info = null;
$stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $student_info = $result->fetch_assoc();
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Home - PSG Polytechnic</title>
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
        .nav-left {
            display: flex;
            align-items: center;
            gap: 15px;
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
        }
        .nav-links a:hover {
            background: var(--psg-yellow);
            color: var(--psg-blue);
            transform: translateY(-3px) scale(1.05);
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
        <div class="nav-left">
            <div class="nav-title"><i class="fa-solid fa-graduation-cap"></i> PSG Polytechnic - Student Portal</div>
        </div>
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
        <div class="welcome-box">
            <h1>Welcome, <?= h($username) ?>!</h1>
            <p>Your Student Portal Dashboard</p>
        </div>

        <?php if ($student_info): ?>
        <div class="welcome-box">
            <h3>Your Information</h3>
            <p><strong>Name:</strong> <?= h($student_info['first_name'] . ' ' . $student_info['last_name']) ?></p>
            <?php if ($student_info['department']): ?>
                <p><strong>Department:</strong> <?= h($student_info['department']) ?></p>
            <?php endif; ?>
            <?php if ($student_info['year_class']): ?>
                <p><strong>Year/Class:</strong> <?= h($student_info['year_class']) ?></p>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="welcome-box">
            <p><i class="fa-solid fa-info-circle"></i> Please complete your profile to access all features.</p>
        </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <a href="student_about.php" class="dashboard-card">
                <i class="fa-solid fa-circle-info"></i>
                <h3>About</h3>
                <p>Learn about our institution and programs</p>
            </a>

            <a href="student_departments.php" class="dashboard-card">
                <i class="fa-solid fa-building"></i>
                <h3>Departments</h3>
                <p>Explore department information</p>
            </a>

            <a href="student_gallery.php" class="dashboard-card">
                <i class="fa-solid fa-images"></i>
                <h3>Gallery</h3>
                <p>View college events and activities</p>
            </a>

            <a href="student_register.php" class="dashboard-card">
                <i class="fa-solid fa-file-pen"></i>
                <h3>Register</h3>
                <p>Submit registration forms</p>
            </a>

            <a href="student_marks.php" class="dashboard-card">
                <i class="fa-solid fa-chart-line"></i>
                <h3>View Marks</h3>
                <p>Check your academic performance</p>
            </a>
        </div>
    </main>

    <footer>
        &copy; 2025 PSG Polytechnic College. All Rights Reserved.
    </footer>
</body>
</html>
