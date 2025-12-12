<?php
// header.php (Unified Design and Navigation)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- INITIALIZE USER DATA ---
$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null; // Null means not logged in
$is_staff = ($role === 'staff');
$is_logged_in = ($user_id !== null);

// --- NAVIGATION LINKS BASED ON ROLE ---
$nav_links = '';

// Links for ALL users (or logged-in users)
$nav_links .= '<a href="index.php"><i class="fa-solid fa-house"></i> Home</a>';
$nav_links .= '<a href="about.php"><i class="fa-solid fa-circle-info"></i> About</a>';
$nav_links .= '<a href="departments.php"><i class="fa-solid fa-building"></i> Departments</a>';
$nav_links .= '<a href="gallery.php"><i class="fa-solid fa-images"></i> Gallery</a>';

if ($is_logged_in) {
    $nav_links .= '<a href="academic_perform.php"><i class="fa-solid fa-graduation-cap"></i> Academics</a>';
}

// Links ONLY for Staff
if ($is_staff) {
    $nav_links .= '<a href="addmission.php"><i class="fa-solid fa-user-plus"></i> Admission</a>';
    $nav_links .= '<a href="curr_gap.php"><i class="fa-solid fa-book-open-reader"></i> Curriculum Gaps</a>';
    $nav_links .= '<a href="upload.php"><i class="fa-solid fa-upload"></i> Upload MOU</a>';
    $nav_links .= '<a href="view.php"><i class="fa-solid fa-eye"></i> View MOU</a>';
}

// Login/Logout Link
if ($is_logged_in) {
    $nav_links .= '<a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>';
} else {
    $nav_links .= '<a href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>';
}

// Function to activate the correct link in the navigation
function activate_link($nav_links, $current_page) {
    $pattern = '/href="' . preg_quote(basename($current_page), '/') . '"/';
    $replacement = 'href="' . basename($current_page) . '" class="active"';
    return preg_replace($pattern, $replacement, $nav_links, 1);
}

// --- SHARED CSS (Optimized from departments.php) ---
$shared_css = '
  :root {
    --psg-blue: #002c77; --psg-yellow: #f4c20d; --light-text: #ffffff;
    --gradient-primary: linear-gradient(135deg, #002c77, #004499, #0066cc);
    --border-radius: 20px;
    --shadow-light: 0 4px 6px rgba(0,0,0,0.05);
    --shadow-medium: 0 8px 20px rgba(0,0,0,0.1);
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: "Inter", sans-serif; 
    background: linear-gradient(135deg, #f9f9f9, #e0e0e0);
    color: #1a1a1a;
    line-height: 1.6;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
  }
  nav {
    background: var(--gradient-primary);
    padding: 1rem 1.5rem;
    display: flex;
    flex-wrap: wrap; 
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: var(--shadow-medium);
    border-radius: 0 0 var(--border-radius) var(--border-radius);
    margin: 0 10px;
  }
  .nav-left { display: flex; align-items: center; gap: 15px; z-index: 2; }
  .nav-left img { height: 45px; border-radius: 8px; }
  .nav-title { font-size: 1.4rem; font-weight: 700; color: var(--light-text); display: flex; align-items: center; gap: 10px; }
  .nav-links { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; z-index: 2; }
  .nav-links a {
    padding: 10px 18px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    color: var(--light-text);
    transition: all 0.4s;
    box-shadow: var(--shadow-light);
  }
  .nav-links a:hover, .nav-links a.active {
    background: var(--psg-yellow);
    color: var(--psg-blue);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(244, 194, 13, 0.4);
  }
  main {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
    flex-grow: 1;
    width: 100%;
  }
  .section-box {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(25px) saturate(180%);
    border-radius: var(--border-radius);
    padding: 30px;
    border-left: 6px solid var(--psg-blue);
    box-shadow: var(--shadow-medium);
    margin-bottom: 25px;
  }
  h2, h3 {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    text-align: center;
    margin-bottom: 20px;
  }
  h2 { font-size: 2.5rem; }
  .message {
    padding: 15px; border-radius: 10px; text-align: center; font-weight: 600; margin-bottom: 20px;
    border-left: 5px solid; background: #e6f7ff; color: #004085; border-left-color: #0066cc;
  }
  .error { background: #f8d7da; color: #721c24; border-left-color: #dc3545; }
  .success { background: #d4edda; color: #155724; border-left-color: #28a745; }

  /* Table style */
  table { width: 100%; border-collapse: collapse; margin-top: 20px; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
  th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
  th { background-color: var(--psg-blue); color: var(--light-text); font-weight: bold; }
  tr:nth-child(even) { background-color: #f7f7f7; }
  tr:hover { background-color: #e6f7ff; }
  
  /* Responsive Adjustments */
  @media(max-width: 992px){
      .nav-links { justify-content: center; margin-top: 10px; }
      .nav-title { font-size: 1.2rem; }
  }
';

// Function to start the page HTML
function start_html($title, $css, $nav_links, $current_page) {
    global $user_id, $role;
    $active_nav_links = activate_link($nav_links, $current_page);
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>{$title}</title>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet' />
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' />
    <style>{$css}</style>
</head>
<body>
<nav>
    <div class='nav-left'>
      <img src='uploads/images/logo1.png' alt='PSG Logo' />
      <span class='nav-title'>
        PSG Polytechnic - MOU Portal
        <img src='uploads/images/logo2.png' alt='Secondary Logo' />
      </span>
    </div>
    <div class='nav-links'>
        {$active_nav_links}
    </div>
</nav>
<main>
";
}

// Alias function for generate_header - wrapper around start_html
function generate_header($title, $css = null, $nav_links_override = null) {
    global $shared_css, $nav_links;
    $css = $css ?? $shared_css;
    $nav_links_override = $nav_links_override ?? $nav_links;
    $current_page = basename($_SERVER['PHP_SELF']);
    start_html($title, $css, $nav_links_override, $current_page);
}

// Function to generate footer
function generate_footer() {
    require_once 'footer.php';
}
?>