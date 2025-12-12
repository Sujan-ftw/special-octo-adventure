<?php
// about.php (RENAMED and MODIFIED)
require_once 'header.php'; // Includes session_start() and role definition

// Generate the page structure
$active_nav_links = activate_link($nav_links, basename($_SERVER['PHP_SELF']));
generate_header("About MOUs", $shared_css, $active_nav_links);
?>

<div class="section-box">
  <h2><i class="fa-solid fa-handshake-angle"></i> What is an MOU?</h2>
  <p>A **Memorandum of Understanding (MOU)** is a formal agreement between two or more parties outlining the terms of cooperation, collaboration, and partnership.</p>

  <h3>Purpose of MOUs</h3>
  <ul style="margin-left: 20px; list-style-type: disc;">
    <li>Establishing clear expectations and objectives.</li>
    <li>Strengthening partnerships and collaboration.</li>
    <li>Promoting transparency and accountability.</li>
  </ul>

  <h3>How MOUs Benefit Us</h3>
  <p>MOUs pave the way for innovative collaborations and resource sharing, enhancing academic and corporate relationships worldwide, which directly benefits our students and staff.</p>
</div>

<?php 
if ($role === 'staff') {
    echo '<div class="section-box">';
    echo '<h3><i class="fa-solid fa-user-tie"></i> Information for Staff/Administration</h3>';
    echo '<p>As a **staff member**, you have access to additional features via the navigation bar, including uploading new MOU documents, viewing the comprehensive list, and managing curriculum gap reports. Please ensure all uploaded files adhere to the institutional naming conventions.</p>';
    echo '</div>';
}
?>

<?php generate_footer(); ?>