<?php
require_once 'auth_check.php';
require_role('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - PSG Polytechnic</title>
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
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .gallery-item {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .gallery-item:hover {
            transform: scale(1.05);
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .gallery-caption {
            padding: 15px;
            text-align: center;
            color: var(--psg-blue);
            font-weight: 600;
        }
        .info-message {
            background: #e6f7ff;
            border: 1px solid #0066cc;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            color: #004085;
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
            <h2><i class="fa-solid fa-images"></i> College Gallery</h2>
            
            <div class="info-message">
                <i class="fa-solid fa-info-circle"></i> View-only access: Gallery images are managed by staff.
            </div>

            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="https://via.placeholder.com/250x200/002c77/ffffff?text=Campus+View" alt="Campus">
                    <div class="gallery-caption">Campus View</div>
                </div>
                <div class="gallery-item">
                    <img src="https://via.placeholder.com/250x200/002c77/ffffff?text=Annual+Day" alt="Annual Day">
                    <div class="gallery-caption">Annual Day Celebration</div>
                </div>
                <div class="gallery-item">
                    <img src="https://via.placeholder.com/250x200/002c77/ffffff?text=Sports+Day" alt="Sports">
                    <div class="gallery-caption">Sports Day Events</div>
                </div>
                <div class="gallery-item">
                    <img src="https://via.placeholder.com/250x200/002c77/ffffff?text=Tech+Fest" alt="Tech Fest">
                    <div class="gallery-caption">Technical Festival</div>
                </div>
                <div class="gallery-item">
                    <img src="https://via.placeholder.com/250x200/002c77/ffffff?text=Lab+Facilities" alt="Labs">
                    <div class="gallery-caption">Laboratory Facilities</div>
                </div>
                <div class="gallery-item">
                    <img src="https://via.placeholder.com/250x200/002c77/ffffff?text=Workshops" alt="Workshops">
                    <div class="gallery-caption">Industry Workshops</div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        &copy; 2025 PSG Polytechnic College. All Rights Reserved.
    </footer>
</body>
</html>
