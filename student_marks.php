<?php
require_once 'auth_check.php';
require_once 'dp_connection.php';
require_role('student');

$user_id = get_user_id();

// Get student ID from user_id
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

// Fetch semester marks if student profile exists
$marks = [];
if ($student_id) {
    $stmt = $conn->prepare("SELECT * FROM semester_marks WHERE student_id = ? ORDER BY semester_number ASC");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $marks[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Marks - PSG Polytechnic</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: var(--psg-blue);
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        tr:hover {
            background-color: #e6f7ff;
        }
        .no-data {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            color: #856404;
        }
        .pass {
            color: #28a745;
            font-weight: bold;
        }
        .fail {
            color: #dc3545;
            font-weight: bold;
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
            <h2><i class="fa-solid fa-chart-line"></i> My Academic Performance</h2>
            
            <?php if ($student_info): ?>
                <p><strong>Name:</strong> <?= h($student_info['first_name'] . ' ' . $student_info['last_name']) ?></p>
                <?php if ($student_info['department']): ?>
                    <p><strong>Department:</strong> <?= h($student_info['department']) ?></p>
                <?php endif; ?>
                <?php if ($student_info['year_class']): ?>
                    <p><strong>Year/Class:</strong> <?= h($student_info['year_class']) ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($marks)): ?>
                <?php foreach ($marks as $mark): ?>
                    <div class="section-box">
                        <h3>Semester <?= h($mark['semester_number']) ?></h3>
                        <?php if ($mark['register_number']): ?>
                            <p><strong>Register Number:</strong> <?= h($mark['register_number']) ?></p>
                        <?php endif; ?>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($mark['subjects']) {
                                    $subjects = json_decode($mark['subjects'], true);
                                    if ($subjects && is_array($subjects)) {
                                        foreach ($subjects as $subject) {
                                            echo "<tr>";
                                            echo "<td>" . h($subject['name'] ?? 'N/A') . "</td>";
                                            echo "<td>" . h($subject['marks'] ?? 'N/A') . "</td>";
                                            $status = $subject['status'] ?? 'N/A';
                                            $statusClass = strtolower($status) === 'pass' ? 'pass' : 'fail';
                                            echo "<td class='" . $statusClass . "'>" . h($status) . "</td>";
                                            echo "</tr>";
                                        }
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                        
                        <?php if ($mark['total_marks']): ?>
                            <p style="margin-top: 15px;"><strong>Total Marks:</strong> <?= h($mark['total_marks']) ?></p>
                        <?php endif; ?>
                        <?php if ($mark['grade_or_avg']): ?>
                            <p><strong>Grade/Average:</strong> <?= h($mark['grade_or_avg']) ?></p>
                        <?php endif; ?>
                        <?php if ($mark['pass_fail']): ?>
                            <p><strong>Overall Status:</strong> <span class="<?= strtolower($mark['pass_fail']) === 'pass' ? 'pass' : 'fail' ?>"><?= h($mark['pass_fail']) ?></span></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fa-solid fa-info-circle"></i> No marks have been entered yet. Please check back later.
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        &copy; 2025 PSG Polytechnic College. All Rights Reserved.
    </footer>
</body>
</html>
