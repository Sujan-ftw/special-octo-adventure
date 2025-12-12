<?php
// departments.php (MODIFIED to restrict department selection for students)
require_once 'header.php'; // Includes session_start() and role definition

// Connect to mou DB for mou_files
$host = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'mou';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$selectedDepartment = isset($_GET['department']) ? $_GET['department'] : '';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '';

// If current user is a student and has profile, restrict to their department
$student_department = null;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && !empty($_SESSION['student_id'])) {
    // fetch from iqac DB
    $iqac = new mysqli('localhost','root','','iqac');
    if (!$iqac->connect_error) {
        $stmt = $iqac->prepare("SELECT department FROM students WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['student_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $student_department = $row['department'];
            // override any selectedDepartment from query
            $selectedDepartment = $student_department;
        }
        $stmt->close();
    }
    if(isset($iqac)) $iqac->close();
}

$mous = [];
if ($selectedDepartment && $selectedYear) {
    // Fetch MOUs matching department and year
    $stmt = $conn->prepare("SELECT * FROM mou_files WHERE department = ? AND year = ? ORDER BY upload_date DESC");
    $stmt->bind_param("ss", $selectedDepartment, $selectedYear);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $mous[] = $row;
    }
    $stmt->close();
}

$conn->close();

// Generate the page structure
$active_nav_links = activate_link($nav_links, basename($_SERVER['PHP_SELF']));
generate_header("Departments - MOU Portal", $shared_css, $active_nav_links);
?>

<div class="section-box">
  <h2><i class="fa-solid fa-sitemap"></i> Department MOU View</h2>

  <form method="GET" action="">
    <div class="filters" style="margin:20px; display:flex; gap:20px; flex-wrap:wrap; justify-content: center;">
      <div class="form-group" style="margin-bottom: 0;">
        <label for="department">Department</label>
        <select id="department" name="department" onchange="this.form.submit()" <?= $student_department ? 'disabled' : '' ?>>
          <option value="">--Select Department--</option>
          <option value="Information Technology" <?= ($selectedDepartment=='Information Technology')?'selected':'' ?>>Information Technology</option>
          <option value="Computer Networking" <?= ($selectedDepartment=='Computer Networking')?'selected':'' ?>>Computer Networking</option>
          <option value="Electronics and Communication" <?= ($selectedDepartment=='Electronics and Communication')?'selected':'' ?>>Electronics and Communication</option>
          <option value="Mechanical Engineering" <?= ($selectedDepartment=='Mechanical Engineering')?'selected':'' ?>>Mechanical Engineering</option>
        </select>
        <?php if($student_department): ?>
          <input type="hidden" name="department" value="<?= htmlspecialchars($student_department) ?>" />
        <?php endif; ?>
      </div>
      <div class="form-group" style="margin-bottom: 0;">
        <label for="year">Year</label>
        <select id="year" name="year" onchange="this.form.submit()" <?= empty($selectedDepartment)?'disabled':'' ?>>
          <option value="">--Select Year--</option>
          <option value="2020-21" <?= ($selectedYear=='2020-21')?'selected':'' ?>>2020-21</option>
          <option value="2021-22" <?= ($selectedYear=='2021-22')?'selected':'' ?>>2021-22</option>
          <option value="2022-23" <?= ($selectedYear=='2022-23')?'selected':'' ?>>2022-23</option>
          <option value="2023-24" <?= ($selectedYear=='2023-24')?'selected':'' ?>>2023-24</option>
          <option value="2024-25" <?= ($selectedYear=='2024-25')?'selected':'' ?>>2024-25</option>
        </select>
      </div>
    </div>
  </form>

  <?php if ($selectedDepartment && $selectedYear): ?>
    <div class="selected-info info message" style="margin:20px auto; max-width: 800px;">
      Showing MOUs for **<?= htmlspecialchars($selectedDepartment) ?>** - **<?= htmlspecialchars($selectedYear) ?>**
    </div>

    <div class="mou-table" style="overflow-x: auto;">
      <table>
        <thead>
          <tr>
            <th>Company</th>
            <th>Date</th>
            <th>Status</th>
            <th>Document</th>
            <th>Image</th>
          </tr>
        </thead>
        <tbody>
          <?php if($mous): ?>
            <?php foreach($mous as $mou): ?>
              <tr>
                <td><?= htmlspecialchars($mou['company']) ?></td>
                <td><?= htmlspecialchars($mou['upload_date']) ?></td>
                <td><?= htmlspecialchars($mou['status']) ?></td>
                <td><a href="<?= htmlspecialchars($mou['filepath']) ?>" target="_blank" class="btn btn-secondary" style="display:inline-block; padding: 6px 12px; font-size: 14px;">View Doc</a></td>
                <td>
                  <?php if (!empty($mou['image'])): ?>
                    <img src="<?= htmlspecialchars($mou['image']) ?>" alt="Thumbnail" style="width:50px; height:auto; border-radius:4px; cursor:pointer;" onclick="openZoom('<?= htmlspecialchars($mou['image']) ?>')"/>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align:center;">No MOUs found for this selection.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div id="imageZoomOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:9999;">
      <div style="position:relative; max-width:90%; max-height:90%; background:#fff; padding:10px; border-radius:10px;">
        <div style="position:absolute; top:-15px; right:-15px; background:#fff; color:#333; border-radius:50%; width:30px; height:30px; display:flex; justify-content:center; align-items:center; cursor:pointer; font-size:1.5rem; font-weight:bold;" onclick="closeZoom()">&times;</div>
        <img src="" alt="Zoomed Image" id="zoomedImage" style="max-width:100%; max-height:100%; display:block; border-radius:8px;"/>
      </div>
    </div>

    <script>
      // Zoom functions (required for this page)
      function openZoom(src) {
        document.getElementById('zoomedImage').src = src;
        document.getElementById('imageZoomOverlay').style.display = 'flex';
      }
      function closeZoom() {
        document.getElementById('imageZoomOverlay').style.display = 'none';
      }
      document.getElementById('imageZoomOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeZoom();
      });
    </script>

  <?php endif; ?>
</div>

<?php generate_footer(); ?>