<?php
// addmission.php (Staff Only)
require_once 'dp_connection.php';
require_once 'header.php'; 

// --- ACCESS CHECK: MUST BE STAFF ---
if (!$is_staff) {
    die("Access Denied: You do not have permission to view Admission Information.");
}
// --- END ACCESS CHECK ---

// --- DB LOGIC ---
$db_conn = new mysqli('localhost', 'root', '', 'iqac'); // Using IQAC db
if ($db_conn->connect_error) { die("DB Connection Failed: " . $db_conn->connect_error); }

$default_intake_year1 = 60; 
$default_intake_year2 = 30; 
$default_intake_year3 = 60; 

// Fetch total students admitted (Used for single row sum, not per year)
$total_counseling = $db_conn->query("SELECT COUNT(*) as total FROM students WHERE counselling_quota=1")->fetch_assoc()['total'] ?? 0;
$total_management = $db_conn->query("SELECT COUNT(*) as total FROM students WHERE management_quota=1")->fetch_assoc()['total'] ?? 0;
$total_lateral = $db_conn->query("SELECT COUNT(*) as total FROM students WHERE entry_type='Lateral'")->fetch_assoc()['total'] ?? 0;

// Fetch all distinct batch_years (Assuming most recent 3 are Year 1, 2, 3)
$batch_years_result = $db_conn->query("SELECT DISTINCT batch_year FROM students ORDER BY batch_year DESC LIMIT 3");
$batch_years = [];
while ($row = $batch_years_result->fetch_assoc()) {
    $batch_years[] = $row['batch_year'];
}
$year1 = $batch_years[0] ?? 'N/A';
$year2 = $batch_years[1] ?? 'N/A';
$year3 = $batch_years[2] ?? 'N/A';

// Map batch_year to counts
$batch_year_counts = [];
if (!empty($batch_years)) {
    $placeholders = implode(',', array_fill(0, count($batch_years), '?'));
    // NOTE: This query only counts *all* students for that batch_year.
    $stmt = $db_conn->prepare("SELECT batch_year, COUNT(*) as count FROM students WHERE batch_year IN ($placeholders) GROUP BY batch_year");
    $types = str_repeat('s', count($batch_years));
    $stmt->bind_param($types, ...$batch_years); 
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $batch_year_counts[$row['batch_year']] = $row['count'];
    }
    $stmt->close();
}

$db_conn->close();

start_html("Students Intake Information", $shared_css, $nav_links, basename($_SERVER['PHP_SELF']));
?>

<div class="section-box">
  <h2><i class="fa-solid fa-file-invoice"></i> C. Admission Process</h2>
  <h3>Table 13: Students Intake Information</h3>
  <div style="overflow-x: auto;">
  <table>
    <thead>
        <tr>
          <th>Students Admission Details</th>
          <th>I Year (Batch: <?= h($year1) ?>)</th>
          <th>II Year (Batch: <?= h($year2) ?>)</th>
          <th>III Year (Batch: <?= h($year3) ?>)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
          <td style="text-align: left; font-weight: 600;">Sanctioned intake strength of the program (N)</td>
          <td><?= h($default_intake_year1) ?></td>
          <td><?= h($default_intake_year2) ?></td>
          <td><?= h($default_intake_year3) ?></td>
        </tr>
        <tr>
          <td style="text-align: left;">Total admitted through counseling (N1)</td>
          <td><?= h($total_counseling) ?></td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr>
          <td style="text-align: left;">Number admitted through management quota (N2)</td>
          <td><?= h($total_management) ?></td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr>
          <td style="text-align: left;">Number admitted through lateral entry (N3)*</td>
          <td><?= h($total_lateral) ?></td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr style="background-color:#003c8c; color:var(--light-text); font-weight: bold;">
          <td style="text-align: left;">Total number of students admitted (N1+N2+N3)</td>
          <td><?= h($total_counseling + $total_management + $total_lateral) ?></td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr style="background-color: #f0f8ff;">
          <td style="text-align: left; font-weight: 600;">Actual Students Count (by Batch Year)</td>
          <td><?= h($batch_year_counts[$year1] ?? '0') ?></td>
          <td><?= h($batch_year_counts[$year2] ?? '0') ?></td>
          <td><?= h($batch_year_counts[$year3] ?? '0') ?></td>
        </tr>
    </tbody>
  </table>
  </div>
</div>

<?php require_once 'footer.php'; ?>