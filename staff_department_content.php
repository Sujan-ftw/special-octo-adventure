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

// Handle content upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_content'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $department = trim($_POST['department'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $content_type = $_POST['content_type'] ?? '';
    
    if (empty($department) || empty($title) || empty($content) || empty($content_type)) {
        $error = "Please fill in all required fields.";
    } else {
        // Handle file upload if provided
        $file_path = null;
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $max_size = 10 * 1024 * 1024; // 10MB
            
            $file_type = $_FILES['document']['type'];
            $file_size = $_FILES['document']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = "Invalid file type. Only PDF and DOC/DOCX files are allowed.";
            } elseif ($file_size > $max_size) {
                $error = "File size exceeds 10MB limit.";
            } else {
                // Create uploads directory
                $upload_dir = 'uploads/department_content/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('dept_', true) . '.' . $extension;
                $file_path = $upload_dir . $filename;
                
                if (!move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
                    $error = "Failed to upload document.";
                    $file_path = null;
                }
            }
        }
        
        if (empty($error)) {
            // Here you could save to database
            // For now, just show success
            $message = "Department content uploaded successfully!";
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
    <title>Department Content - PSG Polytechnic</title>
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
            max-width: 900px;
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
            min-height: 150px;
            resize: vertical;
        }
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .file-input-label {
            display: block;
            padding: 12px 15px;
            border: 2px dashed #e0e0e0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-input-label:hover {
            border-color: var(--psg-blue);
            background: #f8f9fa;
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
            width: 100%;
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
            <h2><i class="fa-solid fa-file-circle-plus"></i> Upload Department Content</h2>
            
            <?php if ($error): ?>
                <div class="message error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="message success"><?= h($message) ?></div>
            <?php endif; ?>

            <form method="POST" action="staff_department_content.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                <div class="form-group">
                    <label for="department"><i class="fa-solid fa-building"></i> Department *</label>
                    <select id="department" name="department" required>
                        <option value="">-- Select Department --</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Computer Networking">Computer Networking</option>
                        <option value="Electronics and Communication">Electronics and Communication</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content_type"><i class="fa-solid fa-list"></i> Content Type *</label>
                    <select id="content_type" name="content_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="announcement">Announcement</option>
                        <option value="syllabus">Syllabus</option>
                        <option value="timetable">Timetable</option>
                        <option value="notes">Study Notes</option>
                        <option value="project">Project Guidelines</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title"><i class="fa-solid fa-heading"></i> Title *</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Semester 3 Timetable" required>
                </div>

                <div class="form-group">
                    <label for="content"><i class="fa-solid fa-align-left"></i> Content *</label>
                    <textarea id="content" name="content" placeholder="Enter detailed content here..." required></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fa-solid fa-file"></i> Attach Document (Optional - Max 10MB, PDF/DOC/DOCX)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="document" name="document" accept=".pdf,.doc,.docx" onchange="updateFileName(this)">
                        <label for="document" class="file-input-label" id="fileLabel">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Click to select document (optional)
                        </label>
                    </div>
                </div>

                <button type="submit" name="upload_content" class="btn-submit">
                    <i class="fa-solid fa-upload"></i> Upload Content
                </button>
            </form>
        </div>
    </main>

    <footer>
        &copy; 2025 PSG Polytechnic College. All Rights Reserved.
    </footer>

    <script>
        function updateFileName(input) {
            const label = document.getElementById('fileLabel');
            if (input.files && input.files[0]) {
                label.innerHTML = '<i class="fa-solid fa-check-circle"></i> ' + input.files[0].name;
            }
        }
    </script>
</body>
</html>
