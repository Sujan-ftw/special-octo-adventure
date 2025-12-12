<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}
$student_id = $_SESSION['student_id'];

// Connect to database
$host='localhost'; $user='root'; $pass=''; $db='i';
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("DB connection error: " . $conn->connect_error);

// Fetch student info
$stmt=$conn->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch academic details
$academic_records = [];
$stmt=$conn->prepare("SELECT * FROM academic_details WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result=$stmt->get_result();
while($row=$result->fetch_assoc()){
    $academic_records[]=$row;
}
$stmt->close();

// Fetch achievement details
$achievement_records = [];
$stmt=$conn->prepare("SELECT * FROM achievement_details WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result=$stmt->get_result();
while($row=$result->fetch_assoc()){
    $achievement_records[]=$row;
}
$stmt->close();

// Fetch non-academic activities
$non_academic_records = [];
$stmt=$conn->prepare("SELECT * FROM non_academic_details WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result=$stmt->get_result();
while($row=$result->fetch_assoc()){
    $non_academic_records[]=$row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Student Resume</title>
<style>
/* Your styles here, same as previous example */
body { font-family: 'Inter', sans-serif; margin: 20px; background: #fff; }
.resume-container { max-width: 800px; margin: auto; border: 2px solid #333; padding: 20px; position: relative; }
.header { text-align: center; margin-bottom: 20px; }
.header h1 { margin: 0; }
.photo { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; margin-bottom: 10px; }
.photos-wrapper { display: flex; justify-content: center; gap: 20px; margin-bottom: 20px; }
section { margin-bottom: 20px; }
h2 { border-bottom: 2px solid #333; padding-bottom: 5px; }
table { width: 100%; border-collapse: collapse; }
table, th, td { border: 1px solid #333; }
th, td { padding: 8px; text-align: left; }
.print-btn { position: absolute; right: 20px; top: 20px; padding: 8px 12px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
@media print { .print-btn { display: none; } }
</style>
</head>
<body>

<div class="resume-container" id="resumeContent">
<button class="print-btn" onclick="window.print()">Print Resume</button>
<div class="header">
<?php if($student): ?>
<h1><?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></h1>
<?php if($student['student_photo']): ?>
    <img src="<?= htmlspecialchars($student['student_photo']) ?>" class="photo" />
<?php endif; ?>
<p>Age: <?= htmlspecialchars($student['age']) ?> | DOB: <?= htmlspecialchars($student['dob']) ?></p>
<p>Email: <?= htmlspecialchars($student['email_id']) ?> | Mobile: <?= htmlspecialchars($student['student_mobile']) ?></p>
<p>Address: <?= htmlspecialchars($student['address']) ?></p>
<?php endif; ?>
</div>

<h2>Academic Qualifications</h2>
<?php if($academic_records): ?>
<table>
<thead>
<tr>
<th>Institution</th><th>Type</th><th>Total Marks</th><th>Percentage</th><th>Year</th><th>Roll No</th>
</tr>
</thead>
<tbody>
<?php foreach($academic_records as $rec): ?>
<tr>
<td><?= htmlspecialchars($rec['institution_name']) ?></td>
<td><?= htmlspecialchars($rec['institution_type']) ?></td>
<td><?= htmlspecialchars($rec['total_marks']) ?></td>
<td><?= htmlspecialchars($rec['percentage']) ?>%</td>
<td><?= htmlspecialchars($rec['year_completed']) ?></td>
<td><?= htmlspecialchars($rec['roll_number']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>No academic records available.</p>
<?php endif; ?>

<h2>Achievements</h2>
<?php if($achievement_records): ?>
<table>
<thead>
<tr>
<th>Title</th><th>Description</th><th>Date</th><th>Level</th>
</tr>
</thead>
<tbody>
<?php foreach($achievement_records as $ach): ?>
<tr>
<td><?= htmlspecialchars($ach['achievement_title']) ?></td>
<td><?= htmlspecialchars($ach['description']) ?></td>
<td><?= htmlspecialchars($ach['date_awarded']) ?></td>
<td><?= htmlspecialchars($ach['achievement_level']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>No achievement records.</p>
<?php endif; ?>

<h2>Non-Academic Activities</h2>
<?php if($non_academic_records): ?>
<table>
<thead>
<tr>
<th>Activity Type</th><th>Organization</th><th>Role</th><th>Duration</th>
</tr>
</thead>
<tbody>
<?php foreach($non_academic_records as $rec): ?>
<tr>
<td><?= htmlspecialchars($rec['activity_type']) ?></td>
<td><?= htmlspecialchars($rec['organization_name']) ?></td>
<td><?= htmlspecialchars($rec['role']) ?></td>
<td><?= htmlspecialchars($rec['duration']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p>No non-academic activities.</p>
<?php endif; ?>

</div>
<script>
function printResume() {
    window.print();
}
</script>
</body>
</html>