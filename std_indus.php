<?php
// Connect to database
$host='localhost'; $user='root'; $pass=''; $db='iqac';
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("DB connection error: " . $conn->connect_error);

// Handle new industry visit form submission
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['industry_save'])) {
    $student_id = intval($_POST['student_id']);
    $roll_number = $_POST['roll_number'];
    $course_name = $_POST['course_name'];
    $subject_name = $_POST['subject_name'];
    $company_name = $_POST['company_name'];
    $visit_duration = $_POST['visit_duration'];
    $evidence_path = '';

    // Handle file upload
    if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = basename($_FILES['evidence_file']['name']);
        $timestamp = time();
        $target_path = $upload_dir . $timestamp . '_' . $file_name;
        if (move_uploaded_file($_FILES['evidence_file']['tmp_name'], $target_path)) {
            $evidence_path = $target_path;
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO industry_visits (student_id, roll_number, course_name, subject_name, company_name, visit_duration, evidence_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $student_id, $roll_number, $course_name, $subject_name, $company_name, $visit_duration, $evidence_path);
    $stmt->execute();
    $stmt->close();
}

// Fetch all industry visit records with student info
$result = $conn->query("SELECT iv.*, s.first_name, s.last_name FROM industry_visits iv JOIN students s ON iv.student_id=s.id");
$industry_records = [];
if($result){
    while($row=$result->fetch_assoc()){
        $industry_records[]=$row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Industry Visit Records</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 20px;
  }
  h2 {
    text-align: center;
    margin-top: 20px;
    color: #333;
  }
  /* Style for the form */
  form {
    max-width: 700px;
    margin: 0 auto 40px auto;
    padding: 20px;
    background-color: #fff;
    border: 2px solid #ccc;
    border-radius: 8px;
  }
  form label {
    display: inline-block;
    width: 150px;
    font-weight: bold;
    margin-bottom: 10px;
  }
  form input[type="text"], form select, form input[type="file"] {
    width: calc(100% - 160px);
    padding: 8px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  form button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
  }
  form button:hover {
    background-color: #45a049;
  }
  /* Style for the table container */
  table {
    width: 90%;
    max-width: 1000px;
    margin: 0 auto;
    border-collapse: collapse;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  table, th, td {
    border: 2px solid #333;
  }
  th, td {
    padding: 12px;
    text-align: center;
  }
  th {
    background-color: #f2f2f2;
    font-weight: bold;
  }
  tr:nth-child(even) {
    background-color: #fafafa;
  }
  a {
    color: #0066cc;
    text-decoration: none;
  }
  a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>
<h2>Add Industry Visit Details</h2>
<!-- Form to add new industry visit -->
<form method="POST" enctype="multipart/form-data">
  <label for="student_id">Student:</label>
  <select name="student_id" required>
    <option value="">--Select Student--</option>
    <?php
    // Fetch students for dropdown
    $res_students=$conn->query("SELECT id, first_name, last_name FROM students");
    if($res_students){
        while($s=$res_students->fetch_assoc()){
            echo "<option value=\"{$s['id']}\">" . htmlspecialchars($s['first_name'].' '.$s['last_name']) . "</option>";
        }
    }
    ?>
  </select><br/>
  <label for="roll_number">Roll Number:</label>
  <input type="text" name="roll_number" required /><br/>
  <label for="course_name">Course Name:</label>
  <input type="text" name="course_name" required /><br/>
  <label for="subject_name">Subject Name:</label>
  <input type="text" name="subject_name" required /><br/>
  <label for="company_name">Company Name:</label>
  <input type="text" name="company_name" required /><br/>
  <label for="visit_duration">Duration:</label>
  <input type="text" name="visit_duration" required /><br/>
  <label for="evidence_file">Upload Evidence:</label>
  <input type="file" name="evidence_file" /><br/>
  <button type="submit" name="industry_save">Add Industry Visit</button>
</form>

<h2>Existing Industry Visit Records</h2>
<table>
<tr>
    <th>Student Name</th>
    <th>Roll Number</th>
    <th>Course Name</th>
    <th>Subject Name</th>
    <th>Company Name</th>
    <th>Duration</th>
    <th>Evidence File</th>
</tr>
<?php if($industry_records): ?>
    <?php foreach($industry_records as $rec): ?>
    <tr>
        <td><?= htmlspecialchars($rec['first_name'].' '.$rec['last_name']) ?></td>
        <td><?= htmlspecialchars($rec['roll_number']) ?></td>
        <td><?= htmlspecialchars($rec['course_name']) ?></td>
        <td><?= htmlspecialchars($rec['subject_name']) ?></td>
        <td><?= htmlspecialchars($rec['company_name']) ?></td>
        <td><?= htmlspecialchars($rec['visit_duration']) ?></td>
        <td>
            <?php if($rec['evidence_file']): ?>
                <a href="<?= htmlspecialchars($rec['evidence_file']) ?>" target="_blank">View File</a>
            <?php else: ?>
                N/A
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="7">No industry visit records found.</td></tr>
<?php endif; ?>
</table>
</body>
</html>