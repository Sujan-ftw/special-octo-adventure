<?php
// database connection
$host='localhost'; $user='root'; $pass=''; $db='iqac';
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("DB connection error: " . $conn->connect_error);

// Initialize variables
$message='';
$students_list = [];
$achievement_records = [];
$national_levels = ['National Level', 'State Level', 'District Level', 'Local Level'];

// Fetch students list
$result_students=$conn->query("SELECT id, first_name, last_name FROM students");
if($result_students){
    while($row=$result_students->fetch_assoc()){
        $students_list[]=$row;
    }
}

// Fetch achievement records
$result_achievement=$conn->query("SELECT ach.*, s.first_name, s.last_name FROM achievement_details ach JOIN students s ON ach.student_id=s.id");
if($result_achievement){
    while($row=$result_achievement->fetch_assoc()){
        $achievement_records[]=$row;
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['achievement_save'])){
    $student_id=intval($_POST['student_id']);
    $achievement_title=$_POST['achievement_title'];
    $description=$_POST['description'];
    $date_awarded=$_POST['date_awarded'];
    $achievement_level = $_POST['achievement_level'] ?? '';

    // File uploads
    $certificate_path = '';
    if(isset($_FILES['certificate']) && $_FILES['certificate']['error'] == UPLOAD_ERR_OK){
        $cert_tmp = $_FILES['certificate']['tmp_name'];
        $cert_name = basename($_FILES['certificate']['name']);
        $cert_ext = strtolower(pathinfo($cert_name, PATHINFO_EXTENSION));
        $cert_new_name = uniqid('cert_') . '.' . $cert_ext;
        if(!is_dir('uploads/certificates')){
            mkdir('uploads/certificates', 0755, true);
        }
        $dest_cert = 'uploads/certificates/' . $cert_new_name;
        move_uploaded_file($cert_tmp, $dest_cert);
        $certificate_path = $dest_cert;
    }

    $photo_path = '';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK){
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $photo_name = basename($_FILES['photo']['name']);
        $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
        $photo_new_name = uniqid('photo_') . '.' . $photo_ext;
        if(!is_dir('uploads/photos')){
            mkdir('uploads/photos', 0755, true);
        }
        $dest_photo = 'uploads/photos/' . $photo_new_name;
        move_uploaded_file($photo_tmp, $dest_photo);
        $photo_path = $dest_photo;
    }

    $stmt=$conn->prepare("INSERT INTO achievement_details (student_id, achievement_title, description, date_awarded, certificate_path, photo_path, achievement_level) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $student_id, $achievement_title, $description, $date_awarded, $certificate_path, $photo_path, $achievement_level);
    if($stmt->execute()){
        $message='Achievement added successfully.';
        // Refresh achievement records
        $achievement_records=[];
        $result_achievement=$conn->query("SELECT ach.*, s.first_name, s.last_name FROM achievement_details ach JOIN students s ON ach.student_id=s.id");
        if($result_achievement){
            while($row=$result_achievement->fetch_assoc()){
                $achievement_records[]=$row;
            }
        }
    } else {
        $message='Error: ' . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Student Achievements</title>
<!-- Font Awesome & CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
body {
    font-family: 'Inter', sans-serif; 
    background: #f4f4f4; 
    margin: 0;
    padding: 30px;
}
.page-wrapper {
    max-width: 1000px;
    margin: 0 auto;
    border: 8px solid #333;
    border-radius: 12px;
    background: #fff;
    padding: 20px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 28px;
    color: #222;
}
.message {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-size: 16px;
}
.message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

form {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}
form > div {
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 350px;
}
label {
    margin-bottom: 6px;
    font-weight: 600;
}
input[type="text"], input[type="date"], select, textarea {
    padding: 10px;
    border: 2px solid #333;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
}
button {
    padding: 12px 25px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 15px;
    align-self: center;
}
button:hover {
    background-color: #0056b3;
}

/* Table styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    font-family: 'Inter', sans-serif;
}
table th, table td {
    border: 1px solid #444;
    padding: 12px;
    text-align: center;
}
table th {
    background-color: #e2e2e2;
    font-weight: 600;
}
table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}
table tbody tr:hover {
    background-color: #f1f1f1;
}

/* Responsive adjustments */
@media(max-width: 600px){
    form {
        flex-direction: column;
        align-items: center;
    }
    form > div {
        max-width: 90%;
    }
}
</style>
</head>
<body>

<div class="page-wrapper">

<h2><i class="fas fa-trophy"></i> Student Achievements</h2>

<?php if($message): ?>
<div class="message <?= strpos($message,'Error')!==false ? 'error' : 'success' ?>"><?= $message ?></div>
<?php endif; ?>

<!-- Achievement form -->
<form method="POST" enctype="multipart/form-data">
<h3 style="text-align:center;">Add a New Achievement</h3>
<div>
<label>Student:</label>
<select name="student_id" required>
  <option value="">--Select Student--</option>
  <?php foreach($students_list as $s): ?>
    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></option>
  <?php endforeach; ?>
</select>
</div>
<div>
<label>Title:</label>
<input type="text" name="achievement_title" required />
</div>
<div>
<label>Description:</label>
<textarea name="description" rows="3" required></textarea>
</div>
<div>
<label>Date Awarded:</label>
<input type="date" name="date_awarded" required />
</div>
<div>
<label>Level of Achievement:</label>
<select name="achievement_level" required>
  <option value="">--Select Level--</option>
  <?php foreach($national_levels as $level): ?>
    <option value="<?= htmlspecialchars($level) ?>"><?= htmlspecialchars($level) ?></option>
  <?php endforeach; ?>
</select>
</div>
<div>
<label>Certificate Upload:</label>
<input type="file" name="certificate" accept=".pdf,.jpg,.jpeg,.png" required />
</div>
<div>
<label>Photo Upload:</label>
<input type="file" name="photo" accept=".jpg,.jpeg,.png" required />
</div>
<button type="submit" name="achievement_save">Add Achievement</button>
</form>

<!-- Achievements List Table -->
<h3 style="text-align:center; margin-top:40px;">Achievements Records</h3>
<table>
<tr>
<th>Title</th>
<th>Student</th>
<th>Description</th>
<th>Date</th>
<th>Level</th>
<th>Certificate</th>
<th>Photo</th>
</tr>
<?php if($achievement_records): ?>
  <?php foreach($achievement_records as $ach): ?>
    <tr>
      <td><?= htmlspecialchars($ach['achievement_title']) ?></td>
      <td><?= htmlspecialchars($ach['first_name'].' '.$ach['last_name']) ?></td>
      <td><?= htmlspecialchars($ach['description']) ?></td>
      <td><?= htmlspecialchars($ach['date_awarded']) ?></td>
      <td><?= htmlspecialchars($ach['achievement_level']) ?></td>
      <td>
        <?php if($ach['certificate_path']): ?>
          <a href="<?= htmlspecialchars($ach['certificate_path']) ?>" target="_blank">View</a>
        <?php endif; ?>
      </td>
      <td>
        <?php if($ach['photo_path']): ?>
          <img src="<?= htmlspecialchars($ach['photo_path']) ?>" width="80" />
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="7" style="text-align:center;">No records found.</td></tr>
<?php endif; ?>
</table>

<!-- Link to main page -->
<div style="text-align:center; margin-top:30px;">
  <a href="index.php" style="text-decoration:none; font-weight:bold; font-size:16px;">&laquo; Back to Main Page</a>
</div>

</div>
</body>
</html>