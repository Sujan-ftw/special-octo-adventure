<?php
require_once 'utils.php';
// Connect to database
$conn = connectDatabase();

// Fetch students list
$students_list = [];
$result = $conn->query("SELECT id, first_name, last_name FROM students");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students_list[] = $row;
    }
}

// Get selected semester
$selected_semester = isset($_GET['semester']) ? intval($_GET['semester']) : 1;

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['semester_submit'])) {
    $student_id = intval($_POST['student_id']);
    $sem = intval($_POST['semester']);
    $register_number = trim($_POST["register_number_sem$sem"]);

    $subject_vars = [];
    $total_marks = 0;

    // Loop through 9 subjects
    for ($sub = 1; $sub <= 9; $sub++) {
        $code = trim($_POST["subject{$sem}_{$sub}_code"]);
        $name = trim($_POST["subject{$sem}_{$sub}_name"]);
        $marks_input = $_POST["subject{$sem}_{$sub}_marks"];
        $pass_fail = $_POST["subject{$sem}_{$sub}_pass_fail"];

        $marks = is_numeric($marks_input) ? intval($marks_input) : 0;

        $subject_vars[] = [
            'code' => $code,
            'name' => $name,
            'marks' => $marks,
            'pass_fail' => $pass_fail
        ];

        $total_marks += $marks;
    }

    // Assign variables for binding
    for ($i = 0; $i < 9; $i++) {
        ${"subject" . ($i + 1) . "_code"} = $subject_vars[$i]['code'];
        ${"subject" . ($i + 1) . "_name"} = $subject_vars[$i]['name'];
        ${"subject" . ($i + 1) . "_marks"} = $subject_vars[$i]['marks'];
        ${"subject" . ($i + 1) . "_pass_fail"} = $subject_vars[$i]['pass_fail'];
    }

    $grade_avg = trim($_POST["grade_avg_sem$sem"]);
    $file_path = '';

    // Handle file upload
    if (isset($_FILES['mark_sheet_sem' . $sem]) && $_FILES['mark_sheet_sem' . $sem]['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['mark_sheet_sem' . $sem];
        $upload_dir = 'uploads/mark_sheets/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = basename($file['name']);
        $target_path = $upload_dir . uniqid() . '_' . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $file_path = $target_path;
        }
    }

    // Prepare SQL with 42 placeholders
    $sql = "INSERT INTO achievement_mark(
        student_id, semester_number, register_number,
        subject1_code, subject1_name, subject1_marks, subject1_pass_fail,
        subject2_code, subject2_name, subject2_marks, subject2_pass_fail,
        subject3_code, subject3_name, subject3_marks, subject3_pass_fail,
        subject4_code, subject4_name, subject4_marks, subject4_pass_fail,
        subject5_code, subject5_name, subject5_marks, subject5_pass_fail,
        subject6_code, subject6_name, subject6_marks, subject6_pass_fail,
        subject7_code, subject7_name, subject7_marks, subject7_pass_fail,
        subject8_code, subject8_name, subject8_marks, subject8_pass_fail,
        subject9_code, subject9_name, subject9_marks, subject9_pass_fail,
        grade_or_avg, mark_sheet_path, total_marks
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    // Bind parameters - 42 variables in correct order
    $stmt->bind_param(
        "iii" . str_repeat("ssss", 9) . "s" . "s" . "i",
        $student_id, $sem, $register_number,
        $subject1_code, $subject1_name, $subject1_marks, $subject1_pass_fail,
        $subject2_code, $subject2_name, $subject2_marks, $subject2_pass_fail,
        $subject3_code, $subject3_name, $subject3_marks, $subject3_pass_fail,
        $subject4_code, $subject4_name, $subject4_marks, $subject4_pass_fail,
        $subject5_code, $subject5_name, $subject5_marks, $subject5_pass_fail,
        $subject6_code, $subject6_name, $subject6_marks, $subject6_pass_fail,
        $subject7_code, $subject7_name, $subject7_marks, $subject7_pass_fail,
        $subject8_code, $subject8_name, $subject8_marks, $subject8_pass_fail,
        $subject9_code, $subject9_name, $subject9_marks, $subject9_pass_fail,
        $grade_avg,
        $file_path,
        $total_marks
    );

    $stmt->execute();
    $stmt->close();

    $message = "Semester $sem marks saved successfully! Total Marks: $total_marks";
}

// Fetch success and backlog counts grouped by batch_year
$success_rates = [];
$years_result = $conn->query("SELECT DISTINCT batch_year FROM students ORDER BY batch_year");
if ($years_result) {
    while ($row = $years_result->fetch_assoc()) {
        $batch_year = intval($row['batch_year']);

        // Total students
        $total_students_q = "SELECT COUNT(*) AS total FROM students WHERE batch_year = $batch_year";
        $total_res = $conn->query($total_students_q);
        $total_students = ($total_res && $total_res->num_rows > 0) ? $total_res->fetch_assoc()['total'] : 0;

        // Success rate without backlog
        $success_without_backlog_q = "
        SELECT COUNT(DISTINCT academic_details.student_id) AS success_count
        FROM academic_details
        JOIN students ON academic_details.student_id = students.id
        LEFT JOIN semester_marks ON academic_details.student_id = semester_marks.student_id
        WHERE students.batch_year = $batch_year
        AND NOT EXISTS (
            SELECT 1 FROM semester_marks sm2
            WHERE sm2.student_id = academic_details.student_id AND sm2.pass_fail = 'fail'
        )";

        $res2 = $conn->query($success_without_backlog_q);
        $success_count = ($res2 && $res2->num_rows > 0) ? $res2->fetch_assoc()['success_count'] : 0;

        // Backlog students count
        $backlog_q = "
        SELECT COUNT(DISTINCT academic_details.student_id) AS backlog_count
        FROM academic_details
        JOIN students ON academic_details.student_id = students.id
        LEFT JOIN semester_marks ON academic_details.student_id = semester_marks.student_id
        WHERE students.batch_year = $batch_year
        AND EXISTS (
            SELECT 1 FROM semester_marks sm2
            WHERE sm2.student_id = academic_details.student_id AND sm2.pass_fail = 'fail'
        )";

        $backlog_res = $conn->query($backlog_q);
        $backlog_count = ($backlog_res && $backlog_res->num_rows > 0) ? $backlog_res->fetch_assoc()['backlog_count'] : 0;

        $success_rates[] = [
            'batch_year' => $batch_year,
            'total' => $total_students,
            'success' => $success_count,
            'backlogs' => $backlog_count
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Semester Wise Entry</title>
<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
/* Styles omitted for brevity, keep your existing styles here */
body {
  font-family: 'Inter', sans-serif;
  background: #f0f4f8;
  margin: 0;
  padding: 20px;
}
.centered {
  max-width: 900px;
  margin: auto;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
h2 {
  text-align: center;
  margin-bottom: 15px;
  color: #002c77;
}
form {
  display: flex;
  flex-direction: column;
}
.select-semester {
  margin-bottom: 20px;
  text-align: center;
}
select {
  padding: 8px 12px;
  font-size: 1em;
  border-radius: 4px;
}
button {
  padding: 10px 20px;
  font-size: 1em;
  background-color: #002c77;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin: auto;
  margin-top: 10px;
}
button:hover {
  background-color: #004499;
}
.message {
  color: green;
  text-align: center;
  font-weight: bold;
  margin-bottom: 15px;
}
fieldset {
  border: 2px solid #002c77;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;
}
legend {
  font-weight: bold;
  padding: 0 10px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  border: 1px solid #999;
}
th, td {
  border: 1px solid #999;
  padding: 8px 12px;
  text-align: center;
}
th {
  background-color: #b0e0e6;
  font-weight: bold;
}
tr:nth-child(even) {
  background-color: #eef9f9;
}
.subject-block {
  margin-bottom: 10px;
  padding: 5px;
  border: 1px dashed #ccc;
  border-radius: 4px;
}
.total-marks {
  font-weight: bold;
  margin-top: 10px;
  color: #333;
}
h3 {
  margin-top: 40px;
  text-align: center;
  color: #002c77;
}
</style>
</head>
<body>
<div class="centered">
<h2><i class="fas fa-pencil-alt"></i> Enter Semester Details</h2>

<?php if ($message): ?>
<p class="message"><?php echo $message; ?></p>
<?php endif; ?>

<!-- Semester selection -->
<div class="select-semester">
  <form method="GET" action="">
    <label for="semester"><i class="fas fa-layer-group"></i> Select Semester:</label>
    <select name="semester" id="semester" onchange="this.form.submit()">
      <?php for ($s=1; $s<=6; $s++): ?>
        <option value="<?= $s ?>" <?= ($s == $selected_semester) ? 'selected' : '' ?>>Semester <?= $s ?></option>
      <?php endfor; ?>
    </select>
  </form>
</div>

<!-- Entry form -->
<form method="POST" enctype="multipart/form-data" action="">
  <input type="hidden" name="semester" value="<?= $selected_semester ?>"/>
  <h3 style="text-align:center;">Semester <?= $selected_semester ?> Entry</h3>
  <table>
    <tr>
      <td style="text-align:left;">
        <label><i class="fas fa-user"></i> Select Student:</label>
        <select name="student_id" required>
          <option value="">--Select Student--</option>
          <?php foreach($students_list as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>
        <label>Register Number:</label>
        <input type="text" name="register_number_sem<?= $selected_semester ?>" required />
      </td>
    </tr>
    <tr>
      <td>
        <h4>Subjects, Marks & Pass/Fail (Optional):</h4>
        <?php for($sub=1; $sub<=9; $sub++): ?>
        <div class="subject-block">
          <strong>Subject <?= $sub ?>:</strong>
          <input type="text" name="subject<?= $selected_semester ?>_<?= $sub ?>_code" placeholder="Subject Code" />
          <input type="text" name="subject<?= $selected_semester ?>_<?= $sub ?>_name" placeholder="Subject Name" />
          <label>Marks:</label>
          <input type="number" name="subject<?= $selected_semester ?>_<?= $sub ?>_marks" min="0" max="100" />
          <label>Pass/Fail:</label>
          <select name="subject<?= $selected_semester ?>_<?= $sub ?>_pass_fail">
            <option value="Pass">Pass</option>
            <option value="Fail">Fail</option>
          </select>
        </div>
        <?php endfor; ?>
      </td>
    </tr>
    <tr>
      <td>
        <label>Grade or Average:</label>
        <input type="text" name="grade_avg_sem<?= $selected_semester ?>" placeholder="Grade / Average" required />
      </td>
    </tr>
    <tr>
      <td>
        <label>Upload Mark Sheet:</label>
        <input type="file" name="mark_sheet_sem<?= $selected_semester ?>" accept=".pdf,.jpg,.png" />
      </td>
    </tr>
  </table>
  <div style="text-align:center;">
    <button type="submit" name="semester_submit"><i class="fas fa-plus"></i> Save Semester <?= $selected_semester ?></button>
  </div>
</form>

<!-- Success & Backlog Rate - Success without Backlog -->
<h3>Success Rate (No Backlogs) by Year</h3>
<table>
  <tr>
    <th>Year of Entry</th>
    <th>Total Students</th>
    <th>Success Rate (No Backlogs)</th>
  </tr>
  <?php
  foreach ($success_rates as $rate) {
      $percent = ($rate['total'] > 0) ? round(($rate['success'] / $rate['total']) * 100, 2) : 0;
      echo "<tr>
        <td>Year {$rate['batch_year']}</td>
        <td>{$rate['total']}</td>
        <td>{$percent}% ({$rate['success']} success)</td>
      </tr>";
  }
  ?>
</table>

<!-- Success & Backlogs Rate - with Backlogs -->
<h3>Success & Backlogs by Year</h3>
<table>
  <tr>
    <th>Year of Entry</th>
    <th>Total Students</th>
    <th>Success Rate (No Backlogs)</th>
    <th>Students with Backlogs</th>
  </tr>
  <?php
  foreach ($success_rates as $rate) {
      $percent = ($rate['total'] > 0) ? round(($rate['success'] / $rate['total']) * 100, 2) : 0;
      echo "<tr>
        <td>Year {$rate['batch_year']}</td>
        <td>{$rate['total']}</td>
        <td>{$percent}% ({$rate['success']} success)</td>
        <td>{$rate['backlogs']}</td>
      </tr>";
  }
  ?>
</table>

</div>
</body>
</html>