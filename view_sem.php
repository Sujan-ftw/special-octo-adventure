<?php
session_start(); // Start session

// Connect to database
$conn = new mysqli("localhost", "root", "", "i");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get semester from GET parameter
$semester = isset($_GET['semester']) ? intval($_GET['semester']) : 1;

// Fetch student marks for the selected semester, joining academic_details for roll_number
$sql = "SELECT sm.*, s.first_name, s.last_name, ad.roll_number
        FROM semester_marks sm
        JOIN students s ON sm.student_id = s.id
        LEFT JOIN academic_details ad ON s.id = ad.student_id
        WHERE sm.semester_number = $semester
        ORDER BY ad.roll_number ASC";

// Execute query
$result = $conn->query($sql);

$students_data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Decode subjects JSON
        $subjects = json_decode($row['subjects'], true);
        $students_data[] = [
            'student_id' => $row['student_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'roll_number' => $row['roll_number'] ?? 'N/A', // fallback if null
            'subjects' => $subjects,
            'total_marks' => $row['total_marks'],
            'grade_or_avg' => $row['grade_or_avg'],
            'mark_sheet_path' => $row['mark_sheet_path']
        ];
    }
}

// Calculate ranks based on total marks
usort($students_data, function($a, $b) {
    return $b['total_marks'] - $a['total_marks'];
});

// Assign ranks
$rank = 1;
$prev_total = null;
foreach ($students_data as $index => &$student) {
    if ($prev_total !== null && $student['total_marks'] < $prev_total) {
        $rank = $index + 1;
    }
    $student['rank'] = $rank;
    $prev_total = $student['total_marks'];
}
unset($student);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Semester <?= $semester ?> Student Marks</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
/* styles omitted for brevity, same as before */
body {
  font-family: 'Inter', sans-serif;
  background: #f0f4f8;
  margin: 0;
  padding: 20px;
}
h2 {
  text-align: center;
  color: #002c77;
  margin-bottom: 20px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}
th, td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: left;
  vertical-align: top;
}
th {
  background-color: #e0e0e0;
}
.subjects-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 5px;
}
.subjects-table th, .subjects-table td {
  border: 1px solid #ccc;
  padding: 4px;
  font-size: 0.9em;
}
a {
  color: #002c77;
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
</style>
</head>
<body>
<h2>Semester <?= $semester ?> Student Marks</h2>

<!-- Semester selection -->
<form method="GET" action="">
  <label for="semester"><i class="fas fa-layer-group"></i> Select Semester:</label>
  <select name="semester" id="semester" onchange="this.form.submit()">
    <?php for ($s=1; $s<=6; $s++): ?>
      <option value="<?= $s ?>" <?= ($s == $semester) ? 'selected' : '' ?>>Semester <?= $s ?></option>
    <?php endfor; ?>
  </select>
</form>

<!-- Data Table -->
<table>
  <thead>
    <tr>
      <th>Rank</th>
      <th>Student Name</th>
      <th>Roll Number</th>
      <!-- Removed Register Number column -->
      <th>Subjects & Marks</th>
      <th>Total Marks</th>
      <th>Grade / Average</th>
      <th>Mark Sheet</th>
      <th>View</th> <!-- New column for restricted view -->
    </tr>
  </thead>
  <tbody>
    <?php if (empty($students_data)): ?>
      <tr><td colspan="8" style="text-align:center;">No data available for this semester.</td></tr>
    <?php else: ?>
      <?php foreach ($students_data as $student): ?>
        <tr>
          <td><?= $student['rank'] ?></td>
          <td><?= htmlspecialchars($student['name']) ?></td>
          <td><?= htmlspecialchars($student['roll_number']) ?></td>
          <!-- Removed Register Number display -->
          <td>
            <table class="subjects-table">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Marks</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($student['subjects'] as $sub): ?>
                  <tr>
                    <td><?= htmlspecialchars($sub['name']) ?></td>
                    <td><?= $sub['marks'] ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </td>
          <td><?= $student['total_marks'] ?></td>
          <td><?= htmlspecialchars($student['grade_or_avg']) ?></td>
          <td>
            <?php if ($student['mark_sheet_path']): ?>
              <a href="<?= htmlspecialchars($student['mark_sheet_path']) ?>" target="_blank"><i class="fas fa-file-pdf"></i> View</a>
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td>
            <?php
            // Show link only if logged-in user is the student
            if (isset($_SESSION['student_id']) && $_SESSION['student_id'] == $student['student_id']) {
              echo '<a href="' . htmlspecialchars($student['mark_sheet_path']) . '" target="_blank"><i class="fas fa-eye"></i> View</a>';
            } else {
              echo 'N/A';
            }
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

</body>
</html>