<?php
// academic_perform.php (Student Data Restricted)
require_once 'dp_connection.php';
require_once 'header.php'; 

// --- ACCESS CHECK: MUST BE LOGGED IN ---
if (!$is_logged_in) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}
// --- END ACCESS CHECK ---

start_html("Academic Performance", $shared_css, $nav_links, basename($_SERVER['PHP_SELF']));

// --- DATABASE LOGIC: FETCH STUDENT MARKS ---
$student_marks = [];

// Fetch marks for the specific student from the semester_marks table
$stmt = $conn->prepare("
    SELECT semester_number, register_number, total_marks, grade_or_avg, subjects 
    FROM semester_marks 
    WHERE student_id = ? 
    ORDER BY semester_number ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $sem = (int)$row['semester_number'];
    $student_marks[$sem] = $row;
    $student_marks[$sem]['subjects'] = json_decode($row['subjects'] ?? '[]', true);
}

$stmt->close();
$conn->close();

?>
<div class="section-box">
  <h2><i class="fa-solid fa-book-open"></i> Academic Performance Record</h2>
  <h3 style="font-size: 1.5rem; text-align: center; margin-bottom: 30px; color: var(--psg-blue);">
    Marks for User ID: **<?= h($user_id) ?>** (Role: **<?= h($role) ?>**)
  </h3>

  <?php if (empty($student_marks)): ?>
    <div class="message">
      No academic performance records found for your student ID.
    </div>
  <?php else: ?>

    <?php foreach ($student_marks as $sem => $data): ?>
      <div style="margin-top: 30px;">
        <h3 style="text-align: left; font-size: 1.3rem; margin-top: 0; border-bottom: 2px solid var(--psg-yellow); padding-bottom: 5px;">
          Semester <?= h($sem) ?> 
          (Reg No: <?= h($data['register_number'] ?? 'N/A') ?>)
        </h3>
        
        <?php if (!empty($data['subjects']) && is_array($data['subjects'])): ?>
          <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th>Subject Code</th>
                <th style="text-align: left;">Subject Name</th>
                <th>Marks Secured</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($data['subjects'] as $subject): ?>
                <tr>
                  <td><?= h($subject['code'] ?? 'N/A') ?></td>
                  <td style="text-align: left;"><?= h($subject['name'] ?? 'N/A') ?></td>
                  <td><?= h($subject['marks'] ?? 'N/A') ?></td>
                  <td>
                    <?php 
                      $status = h($subject['status'] ?? 'N/A');
                      $color = ($status === 'Pass') ? 'green' : (($status === 'Fail') ? 'red' : 'inherit');
                      echo "<span style='color: {$color}; font-weight: bold;'>{$status}</span>";
                    ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr style="background-color: #003c8c; color: var(--light-text); font-weight: bold;">
                <td colspan="2" style="text-align: right;">Total Marks / Grade:</td>
                <td><?= h($data['total_marks'] ?? 'N/A') ?></td>
                <td><?= h($data['grade_or_avg'] ?? 'N/A') ?></td>
              </tr>
            </tfoot>
          </table>
          </div>
        <?php else: ?>
          <div class="message">
            Subject details are unavailable for Semester <?= h($sem) ?>.
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

  <?php endif; ?>

</div>
<?php require_once 'footer.php'; ?>