<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Determine if user is a student
$isStudent = ($_SESSION['role'] === 'student');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>PSG Polytechnic College - Academic Portal</title>
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />

<style>
.header-container {
  display: flex;
  align-items: center;
  justify-content: space-between; /* To spread items to edges */
  padding: 15px 20px;
  background-color: #002c77;
  position: relative;
}

/* Center the title */
.header-title {
  flex: 1;
  text-align: center;
  font-size: 1.8rem;
  font-weight: 700;
  color: #fff;
  margin: 0; /* Remove default margin for h1 */
}

/* Logos styling */
.logo {
  height: 50px;
  border-radius: 8px;
}

/* Optional: style for right logo if needed */
.logo-right {
  height: 50px;
  border-radius: 8px;
}
  /* Reset and base styles */
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'Inter', sans-serif;
    background: #f0f2f5;
    color: #1a1a1a;
    line-height: 1.6;
    min-height: 100vh;
  }

  /* Header styles */
  header {
    display: flex;
    align-items: center;
    background-color: #002c77;
    padding: 15px 20px;
    color: #fff;
  }
  header img.logo {
    height: 50px;
    margin-right: 15px;
    border-radius: 8px;
  }
  header h1 {
    font-size: 1.8rem;
    font-weight: 700;
  }

  /* Navbar styles */
  nav {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #002c77, #004499, #0066cc);
    padding: 10px 20px;
    position: sticky;
    top: 0;
    z-index: 999;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }

  /* Logo inside navbar (left) */
  .nav-left {
    display: flex;
    align-items: center;
  }
  .nav-left img {
    height: 50px;
    border-radius: 8px;
  }
  .nav-title {
    margin-left: 10px;
    font-size: 1.4rem;
    font-weight: 600;
    color: #fff;
    letter-spacing: 1px;
  }

  /* Toggle button for mobile */
  .menu-toggle {
    display: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #fff;
  }

  /* Navigation links container */
  .nav-links {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  /* Individual links styles */
  .nav-links a {
    color: #fff;
    padding: 8px 15px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    transition: background 0.3s, transform 0.2s;
  }

  .nav-links a:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.05);
  }

  /* Dropdown styles */
  .dropdown {
    position: relative;
  }
  .dropbtn {
    cursor: pointer;
    display: flex;
    align-items: center;
  }
  .dropbtn i {
    margin-right: 8px;
  }
  .dropdown-content {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    min-width: 180px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    flex-direction: column;
    z-index: 999;
  }
  .dropdown-content a {
    padding: 10px 15px;
    color: #333;
    text-decoration: none;
    transition: background 0.2s;
  }
  .dropdown-content a:hover {
    background: #f0f0f0;
  }
  /* Show dropdown on hover */
  .dropdown:hover .dropdown-content {
    display: flex;
  }

  /* Responsive styles */
  @media(max-width: 768px){
    nav {
      flex-direction: column;
      align-items: flex-start;
    }
    .menu-toggle {
      display: block;
    }
    .nav-links {
      display: none;
      flex-direction: column;
      width: 100%;
      background: linear-gradient(135deg, #002c77, #004499, #0066cc);
    }
    .nav-links.show {
      display: flex;
    }
  }

  /* Main content styles */
main {
  max-width: 1200px;
  margin: 30px auto;
  padding: 0 20px;
}
.section-box {
  background: #fff;
  padding: 20px;
  margin-bottom: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}
h2 {
  margin-bottom: 15px;
  color: #002c77;
}
a {
  color: #0066cc;
  text-decoration: underline;
}
footer {
  text-align: center;
  padding: 15px;
  background-color: #333;
  color: #fff;
  margin-top: 40px;
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="header-container">
  <div class="header-left">
    <img src="uploads/images/logo1.png" alt="Logo" class="logo" />
  </div>
  <h1 class="header-title">PSG Polytechnic College - Academic Portal</h1>
  <div class="header-right">
    <!-- Second logo or any other element on the right -->
    <img src="uploads/images/logo1.png" alt="Second Logo" class="logo-right" />
  </div>
</header>

<!-- NAVBAR -->
<nav>
  <!-- Left side: logo and title -->
  <div class="nav-left">
    <img src="uploads/images/logo1.png" alt="PSG Logo" />
    <div class="nav-title">Academic Portal</div>
  </div>
  
  <!-- Toggle button for mobile -->
  <div class="menu-toggle" id="menu-toggle"><i class="fa fa-bars"></i></div>
  
  <!-- Links -->
  <div class="nav-links" id="nav-links">
    <?php if ($isStudent): ?>
      <!-- Student Links -->
	  <div class="dropdown">
      <a class="dropbtn"><i class="fa-solid fa-user-graduate"></i> Students <i class="fa-solid fa-caret-down"></i></a>
      <div class="dropdown-content">
        <a href="std_upload.php"><i class="fa-solid fa-info-circle"></i> Student Details</a>
        <a href="std_sports_details.php"><i class="fa-solid fa-list"></i> Sports Details</a>
        <a href="std_indus.php"><i class="fa-solid fa-book-open"></i> Add Industry</a>
        <a href="std_achiev.php"><i class="fa-solid fa-trophy"></i> View Achievements</a>
        <a href="semester_details.php"><i class="fa-solid fa-plus-circle"></i> Add Semester</a>
        <a href="view_sem.php"><i class="fa-solid fa-calendar-alt"></i> View Semester</a>
        <a href="std_higher.php"><i class="fa-solid fa-calendar-alt"></i> View placement/higher study</a>
        <a href="std_indus.php"><i class="fa-solid fa-calendar-alt"></i> View industry</a>
        <a href="std_pro.php"><i class="fa-solid fa-calendar-alt"></i> View projects</a>
        <a href="std_publication.php"><i class="fa-solid fa-calendar-alt"></i> View publication</a>
      </div>
    </div>
      <a href="std_upload.php"><i class="fa-solid fa-user"></i> Student Details</a>
      <a href="semester_details.php"><i class="fa-solid fa-calendar-alt"></i> Semester Details</a>
      <a href="std_pro.php"><i class="fa-solid fa-folder-open"></i> Projects</a>
	  
      <a href="std_publication.php"><i class="fa-solid fa-book"></i> Publications</a>
	  <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
    <?php else: ?>
      <!-- Admin or other roles -->
      <div class="dropdown">
        <div class="dropbtn"><i class="fa-solid fa-user"></i> Account <i class="fa-solid fa-caret-down"></i></div>
        <div class="dropdown-content">
          <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    <?php endif; ?>
    <!-- Common links -->
    <a href="departments.php"><i class="fa-solid fa-building"></i> Departments</a>
    <a href="upload.php"><i class="fa-solid fa-upload"></i> Upload</a>
    <a href="view.php"><i class="fa-solid fa-eye"></i> View</a>
  </div>
</nav>

<!-- MAIN CONTENT -->
<main>
<?php if ($isStudent): ?>
  <section id="student-details" class="section-box">
    <h2>Student Details</h2>
    <p>Welcome, Student! <a href="student_profile.php">View your profile</a></p>
  </section>
  <section id="semester-details" class="section-box">
    <h2>Semester Details</h2>
    <p><a href="semester_details.php">View your semester info</a></p>
  </section>
  <section id="projects" class="section-box">
    <h2>Projects</h2>
    <p><a href="student_projects.php">View your projects</a></p>
  </section>
  <section id="publications" class="section-box">
    <h2>Publications</h2>
    <p><a href="student_publications.php">View your publications</a></p>
  </section>
<?php else: ?>
  <h2>Welcome to the Portal</h2>
  <p>Please select an option from the menu.</p>
<?php endif; ?>
</main>

<!-- FOOTER -->
<footer>
  &copy; 2025 PSG Polytechnic College. All Rights Reserved.
</footer>

<!-- JavaScript for menu toggle -->
<script>
  document.getElementById('menu-toggle').addEventListener('click', function() {
    document.getElementById('nav-links').classList.toggle('show');
  });
</script>

</body>
</html>