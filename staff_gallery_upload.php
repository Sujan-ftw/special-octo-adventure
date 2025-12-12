<?php
require_once 'auth_check.php';
require_once 'dp_connection.php';
require_role('staff');

$user_id = get_user_id();
$message = '';
$error = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    
    if (empty($title) || empty($category)) {
        $error = "Please fill in all required fields.";
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error = "Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.";
        } elseif ($file_size > $max_size) {
            $error = "File size exceeds 5MB limit.";
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'uploads/gallery/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('gallery_', true) . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                // Here you could save to database if needed
                // For now, just show success
                $message = "Image uploaded successfully!";
            } else {
                $error = "Failed to upload image. Please try again.";
            }
        }
    } else {
        $error = "Please select an image to upload.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gallery - PSG Polytechnic</title>
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
            min-height: 100px;
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
            <h2><i class="fa-solid fa-upload"></i> Upload Gallery Image</h2>
            
            <?php if ($error): ?>
                <div class="message error"><?= h($error) ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="message success"><?= h($message) ?></div>
            <?php endif; ?>

            <form method="POST" action="staff_gallery_upload.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title"><i class="fa-solid fa-heading"></i> Image Title *</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Annual Day 2024" required>
                </div>

                <div class="form-group">
                    <label for="category"><i class="fa-solid fa-tags"></i> Category *</label>
                    <select id="category" name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="events">Events</option>
                        <option value="campus">Campus</option>
                        <option value="sports">Sports</option>
                        <option value="cultural">Cultural</option>
                        <option value="technical">Technical</option>
                        <option value="infrastructure">Infrastructure</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fa-solid fa-comment"></i> Description</label>
                    <textarea id="description" name="description" placeholder="Brief description of the image..."></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fa-solid fa-image"></i> Select Image * (Max 5MB, JPEG/PNG/GIF/WebP)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required onchange="updateFileName(this)">
                        <label for="image" class="file-input-label" id="fileLabel">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Click to select image
                        </label>
                    </div>
                </div>

                <button type="submit" name="upload_image" class="btn-submit">
                    <i class="fa-solid fa-upload"></i> Upload Image
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
