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

// Get staff allocated subjects
$allocated_subjects = [];
$stmt = $conn->prepare("SELECT * FROM staff_subjects WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allocated_subjects[] = $row;
}
$stmt->close();

// Get all students for marks entry
$students = [];
$stmt = $conn->prepare("SELECT id, first_name, last_name, department, year_class FROM students ORDER BY first_name, last_name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Handle marks entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_marks'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $student_id = intval($_POST['student_id'] ?? 0);
    $semester = intval($_POST['semester'] ?? 0);
    $register_number = trim($_POST['register_number'] ?? '');
    $subjects_data = $_POST['subjects'] ?? [];
    
    if ($student_id > 0 && $semester > 0 && !empty($subjects_data)) {
        // Calculate total marks
        $total_marks = 0;
        $pass_count = 0;
        $fail_count = 0;
        
        foreach ($subjects_data as $subject) {
            if (isset($subject['marks']) && is_numeric($subject['marks'])) {
                $total_marks += intval($subject['marks']);
                if (isset($subject['status'])) {
                    if (strtolower($subject['status']) === 'pass') {
                        $pass_count++;
                    } else {
                        $fail_count++;
                    }
                }
            }
        }
        
        $pass_fail = ($fail_count === 0) ? 'Pass' : 'Fail';
        $avg = count($subjects_data) > 0 ? round($total_marks / count($subjects_data), 2) : 0;
        
        // Convert subjects to JSON
        $subjects_json = json_encode($subjects_data);
        
        // Check if marks already exist for this student and semester
        $stmt = $conn->prepare("SELECT id FROM semester_marks WHERE student_id = ? AND semester_number = ?");
        $stmt->bind_param("ii", $student_id, $semester);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing
            $row = $result->fetch_assoc();
            $mark_id = $row['id'];
            $stmt->close();
            
            $stmt = $conn->prepare("UPDATE semester_marks SET register_number = ?, subjects = ?, pass_fail = ?, grade_or_avg = ?, total_marks = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $register_number, $subjects_json, $pass_fail, $avg, $total_marks, $mark_id);
        } else {
            $stmt->close();
            // Insert new
            $stmt = $conn->prepare("INSERT INTO semester_marks (student_id, semester_number, register_number, subjects, pass_fail, grade_or_avg, total_marks) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissssi", $student_id, $semester, $register_number, $subjects_json, $pass_fail, $avg, $total_marks);
        }
        
        if ($stmt->execute()) {
            $message = "Marks entered successfully!";
        } else {
            $error = "Failed to enter marks. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all required fields.";
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
    <title>Enter Marks - PSG Polytechnic</title>
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
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
        }
        .subject-entry {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
            align-items: end;
        }
        .btn-add-subject {
            padding: 10px 20px;
            background: var(--psg-blue);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .btn-add-subject:hover {
            opacity: 0.9;
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
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .info-box {
            background: #e6f7ff;
            border: 1px solid #0066cc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
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
            <h2><i class="fa-solid fa-pen-to-square"></i> Enter Student Marks</h2>
            
            <?php if ($error): ?>
                <div class="message error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="message success"><?= h($message) ?></div>
            <?php endif; ?>

            <?php if (empty($allocated_subjects)): ?>
                <div class="info-box">
                    <i class="fa-solid fa-info-circle"></i> 
                    Note: You don't have any subjects allocated yet. Please contact the administrator to assign subjects.
                </div>
            <?php else: ?>
                <div class="info-box">
                    <strong>Your Allocated Subjects:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <?php foreach ($allocated_subjects as $subj): ?>
                            <li><?= h($subj['subject_name']) ?> (<?= h($subj['subject_code']) ?>) - <?= h($subj['department']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="staff_marks_entry.php" id="marksForm">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <div class="form-group">
                    <label for="student_id"><i class="fa-solid fa-user"></i> Select Student</label>
                    <select id="student_id" name="student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>">
                                <?= h($student['first_name'] . ' ' . $student['last_name']) ?> 
                                <?php if ($student['department']): ?>
                                    - <?= h($student['department']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="semester"><i class="fa-solid fa-calendar"></i> Semester</label>
                    <select id="semester" name="semester" required>
                        <option value="">-- Select Semester --</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                        <option value="4">Semester 4</option>
                        <option value="5">Semester 5</option>
                        <option value="6">Semester 6</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="register_number"><i class="fa-solid fa-id-card"></i> Register Number</label>
                    <input type="text" id="register_number" name="register_number" placeholder="e.g., 2023001" required>
                </div>

                <h3 style="margin: 20px 0 15px 0; color: var(--psg-blue);">Subject Marks</h3>
                
                <div id="subjectsContainer">
                    <div class="subject-entry">
                        <div>
                            <label>Subject Name</label>
                            <input type="text" name="subjects[0][name]" placeholder="e.g., Mathematics" required>
                        </div>
                        <div>
                            <label>Marks</label>
                            <input type="number" name="subjects[0][marks]" min="0" max="100" placeholder="0-100" required>
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="subjects[0][status]" required>
                                <option value="Pass">Pass</option>
                                <option value="Fail">Fail</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-add-subject" onclick="addSubject()">
                    <i class="fa-solid fa-plus"></i> Add Another Subject
                </button>

                <button type="submit" name="submit_marks" class="btn-submit">
                    <i class="fa-solid fa-save"></i> Submit Marks
                </button>
            </form>
        </div>
    </main>

    <footer>
        &copy; 2025 PSG Polytechnic College. All Rights Reserved.
    </footer>

    <script>
        let subjectCount = 1;
        
        function addSubject() {
            const container = document.getElementById('subjectsContainer');
            const newSubject = document.createElement('div');
            newSubject.className = 'subject-entry';
            newSubject.innerHTML = `
                <div>
                    <label>Subject Name</label>
                    <input type="text" name="subjects[${subjectCount}][name]" placeholder="e.g., Mathematics" required>
                </div>
                <div>
                    <label>Marks</label>
                    <input type="number" name="subjects[${subjectCount}][marks]" min="0" max="100" placeholder="0-100" required>
                </div>
                <div>
                    <label>Status</label>
                    <select name="subjects[${subjectCount}][status]" required>
                        <option value="Pass">Pass</option>
                        <option value="Fail">Fail</option>
                    </select>
                </div>
            `;
            container.appendChild(newSubject);
            subjectCount++;
        }
    </script>
</body>
</html>
