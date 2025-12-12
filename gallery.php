<?php
// gallery.php (MODIFIED: restrict department select to student's registered department when applicable)
require_once 'utils.php';
require_once 'header.php'; // Includes session_start() and role definition

// Connect to mou DB
$conn = connectMouDatabase();

// Fetch distinct departments and companies
$departments = [];
$result = $conn->query("SELECT DISTINCT department FROM mou_files");
while ($row = $result->fetch_assoc()) {
    $departments[] = $row['department'];
}

$companies = [];
$result = $conn->query("SELECT DISTINCT company FROM mou_files");
while ($row = $result->fetch_assoc()) {
    $companies[] = $row['company'];
}

// Default selections from GET
$department = isset($_GET['department']) ? $_GET['department'] : ($departments[0] ?? '');
$company = isset($_GET['company']) ? $_GET['company'] : ($companies[0] ?? '');

// If student and profile complete, lock to their department
if (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && !empty($_SESSION['student_id'])) {
    // fetch student's department from iqac DB
    $iqac = connectDatabase();
    $stmt = $iqac->prepare("SELECT department FROM students WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['student_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) {
        $department = $r['department'];
    }
    $stmt->close();
    if(isset($iqac)) $iqac->close();
}

// Fetch images and info for selected department and company
$images = [];
if ($department && $company) {
    $stmt = $conn->prepare("SELECT image, department, company FROM mou_files WHERE department = ? AND company = ? AND image IS NOT NULL AND image != ''");
    $stmt->bind_param("ss", $department, $company);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    $stmt->close();
}
$conn->close();

// Generate the page structure
$active_nav_links = activate_link($nav_links, basename($_SERVER['PHP_SELF']));
generate_header("MOU Gallery", $shared_css, $active_nav_links);
?>

<div class="section-box">
  <h2><i class="fa-solid fa-photo-film"></i> MOU Image Gallery</h2>

  <div id="dropdownContainer">
    <div class="dropdown-block form-group">
      <label for="deptDropdown">Department:</label>
      <select id="deptDropdown" name="department" <?= (isset($_SESSION['role']) && $_SESSION['role']==='student') ? 'disabled' : '' ?>>
        <option value="">--Select Department--</option>
        <?php foreach($departments as $dep): ?>
          <option value="<?= htmlspecialchars($dep) ?>" <?= ($dep==$department)?'selected':'' ?>>
            <?= htmlspecialchars($dep) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if(isset($_SESSION['role']) && $_SESSION['role']==='student'): ?>
        <input type="hidden" id="deptHidden" name="department" value="<?= htmlspecialchars($department) ?>" />
      <?php endif; ?>
    </div>
    <div class="dropdown-block form-group">
      <label for="companyDropdown">Company:</label>
      <select id="companyDropdown" name="company">
        <option value="">--Select Company--</option>
        <?php foreach($companies as $comp): ?>
          <option value="<?= htmlspecialchars($comp) ?>" <?= $comp==$company?'selected':'' ?>>
            <?= htmlspecialchars($comp) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <?php if ($department && $company): ?>
    <h3 style="text-align: center; margin-bottom: 30px;">
      Images for Department: **<?= htmlspecialchars($department) ?>** &amp; Company: **<?= htmlspecialchars($company) ?>**
    </h3>
  <?php endif; ?>

  <?php if($images): ?>
  <div class="gallery">
    <?php foreach($images as $img): ?>
      <img src="<?= htmlspecialchars($img['image']) ?>" alt="MOU Event Image" onclick="openZoom('<?= htmlspecialchars($img['image']) ?>')"/>
    <?php endforeach; ?>
  </div>
  <?php elseif($department && $company): ?>
  <div class="warning message">No images found for this department and company selection.</div>
  <?php else: ?>
  <div class="info message">Please select a Department and a Company to view MOU images.</div>
  <?php endif; ?>

</div>

<div id="imageZoomOverlay">
  <div id="zoomedImageContainer">
    <div id="closeZoom" onclick="closeZoom()">&times;</div>
    <img src="" alt="Zoomed Image" id="zoomedImage"/>
  </div>
</div>

<script>
  // If department is user-locked, we still allow company changes (reload page with dept fixed)
  document.getElementById('companyDropdown').addEventListener('change', function() {
    const selectedComp = this.value;
    let selectedDept = document.getElementById('deptDropdown') ? document.getElementById('deptDropdown').value : '';
    // If dept is disabled and hidden input present, prefer hidden value
    const hiddenDept = document.getElementById('deptHidden');
    if (hiddenDept) selectedDept = hiddenDept.value;
    window.location.href = 'gallery.php?department=' + encodeURIComponent(selectedDept) + '&company=' + encodeURIComponent(selectedComp);
  });

  if (document.getElementById('deptDropdown')) {
    document.getElementById('deptDropdown').addEventListener('change', function() {
      const selectedDept = this.value;
      const selectedComp = document.getElementById('companyDropdown').value;
      window.location.href = 'gallery.php?department=' + encodeURIComponent(selectedDept) + '&company=' + encodeURIComponent(selectedComp);
    });
  }

  // Open zoom modal
  function openZoom(src) {
    document.getElementById('zoomedImage').src = src;
    document.getElementById('imageZoomOverlay').classList.add('show');
  }
  // Close zoom modal
  function closeZoom() {
    document.getElementById('imageZoomOverlay').classList.remove('show');
  }
  // Close when clicking outside the image
  document.getElementById('imageZoomOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
      closeZoom();
    }
  });
</script>

<?php generate_footer(); ?>