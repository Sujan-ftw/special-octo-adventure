<?php
// Database connection
$conn=new mysqli('localhost','root','','i');
if($conn->connect_error){
    die('Connection failed: '.$conn->connect_error);
}

// Fetch students list for dropdowns
$students_list=[];
$result=$conn->query("SELECT * FROM students");
if($result){
    while($row=$result->fetch_assoc()){
        $students_list[]=$row;
    }
}

// Handle form submissions
$message="";

// Add Student
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action']) && $_POST['action']=='add_student'){
    $first_name=$_POST['first_name'];
    $last_name=$_POST['last_name'];
    $dob=$_POST['dob'];
    $gender=$_POST['gender'];
    $phone=$_POST['phone'];
    $email=$_POST['email'];
    $address=$_POST['address'];
    $stmt=$conn->prepare("INSERT INTO students (first_name, last_name, dob, gender, phone, email, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $first_name, $last_name, $dob, $gender, $phone, $email, $address);
    if($stmt->execute()){
        $message.="Student added successfully.<br>";
        // Refresh students list
        $result=$conn->query("SELECT * FROM students");
        $students_list=[];
        while($row=$result->fetch_assoc()){
            $students_list[]=$row;
        }
    }
    $stmt->close();
}

// Function for handling file upload
function uploadFile($fileInputName, $targetDir='uploads/') {
    if(isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error']==0){
        $fileName=basename($_FILES[$fileInputName]['name']);
        $targetFilePath=$targetDir . time().'_'. $fileName;
        if(!is_dir($targetDir)){
            mkdir($targetDir,0777,true);
        }
        if(move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetFilePath)){
            return $targetFilePath;
        }
    }
    return null;
}

// Add Higher Study
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action']) && $_POST['action']=='add_higher_study'){
    $student_id=intval($_POST['student_id']);
    $student_name=$_POST['student_name'];
    $prev_roll_no=$_POST['previous_roll_no'];
    $curr_roll_no=$_POST['current_roll_no'];
    $college_name=$_POST['college_name'];
    $quota_type=$_POST['quota_type'];
    $percentage=floatval($_POST['percentage']);

    // Upload file
    $filePath=uploadFile('higher_study_file');

    $stmt=$conn->prepare("INSERT INTO higher_studies (student_id, student_name, previous_roll_no, current_roll_no, college_name, quota_type, percentage, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssd", $student_id, $student_name, $prev_roll_no, $curr_roll_no, $college_name, $quota_type, $percentage, $filePath);
    if($stmt->execute()){
        $message.="Higher study record added.<br>";
    }
    $stmt->close();
}

// Add Placement
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['action']) && $_POST['action']=='add_placement'){
    $student_id=intval($_POST['student_id']);
    $company_name=$_POST['company_name'];
    $package=$_POST['package'];
    $date=$_POST['date'];
    $designation=$_POST['designation'];

    // Upload file
    $filePath=uploadFile('placement_file');

    $stmt=$conn->prepare("INSERT INTO placements (student_id, company_name, package, date, designation, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $student_id, $company_name, $package, $date, $designation, $filePath);
    if($stmt->execute()){
        $message.="Placement record added.<br>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Student Management</title>
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
body { font-family: Arial, sans-serif; background:#f0f0f0; padding:20px; }
h1 { text-align:center; margin-bottom:20px; }
section { background:#fff; padding:20px; margin-bottom:20px; border-radius:8px; }
h3 { margin-top:0; }
form { display:flex; flex-wrap:wrap; gap:10px; }
.form-group { flex:1 1 200px; display:flex; flex-direction:column; }
input[type="text"], input[type="date"], input[type="number"], select, input[type="file"] { padding:8px; border-radius:4px; border:1px solid #ccc; }
button { padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer; }
button:hover { background:#0056b3; }
.message { text-align:center; color:green; margin-bottom:20px; }
</style>
</head>
<body>

<h1>Student Registration & Details Management</h1>

<div class="message"><?php echo $message; ?></div>

<!-- Student Registration -->
<section>
<h3><i class="fas fa-user-plus"></i> Student Registration</h3>
<form method="POST" action="">
  <input type="hidden" name="action" value="add_student" />
  <div class="form-group">
    <label for="first_name"><i class="fas fa-user"></i> First Name:</label>
    <input type="text" name="first_name" required />
  </div>
  <div class="form-group">
    <label for="last_name"><i class="fas fa-user"></i> Last Name:</label>
    <input type="text" name="last_name" required />
  </div>
  <div class="form-group">
    <label for="dob"><i class="fas fa-calendar"></i> Date of Birth:</label>
    <input type="date" name="dob" required />
  </div>
  <div class="form-group">
    <label for="gender"><i class="fas fa-venus-mars"></i> Gender:</label>
    <select name="gender" required>
      <option value="">--Select--</option>
      <option value="Male">Male</option>
      <option value="Female">Female</option>
    </select>
  </div>
  <div class="form-group">
    <label for="phone"><i class="fas fa-phone"></i> Phone:</label>
    <input type="text" name="phone" required />
  </div>
  <div class="form-group">
    <label for="email"><i class="fas fa-envelope"></i> Email:</label>
    <input type="email" name="email" required />
  </div>
  <div class="form-group">
    <label for="address"><i class="fas fa-address-card"></i> Address:</label>
    <input type="text" name="address" required />
  </div>
  <div style="width:100%; text-align:center;">
    <button type="submit"><i class="fas fa-plus"></i> Register Student</button>
  </div>
</form>
</section>

<!-- Higher Study Details -->
<section>
<h3><i class="fas fa-graduation-cap"></i> Add Higher Study Details</h3>
<form method="POST" action="" enctype="multipart/form-data">
  <input type="hidden" name="action" value="add_higher_study" />
  <div class="form-group">
    <label for="student_id"><i class="fas fa-user"></i> Select Student:</label>
    <select name="student_id" required>
      <option value="">--Select Student--</option>
      <?php foreach($students_list as $s): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group">
    <label for="student_name"><i class="fas fa-user"></i> Student Name:</label>
    <input type="text" name="student_name" required />
  </div>
  <div class="form-group">
    <label for="previous_roll_no"><i class="fas fa-id-badge"></i> Roll No (Previous College):</label>
    <input type="text" name="previous_roll_no" required />
  </div>
  <div class="form-group">
    <label for="current_roll_no"><i class="fas fa-id-badge"></i> Roll No (Current College):</label>
    <input type="text" name="current_roll_no" required />
  </div>
  <div class="form-group">
    <label for="college_name"><i class="fas fa-university"></i> College Name:</label>
    <input type="text" name="college_name" required />
  </div>
  <div class="form-group">
    <label for="quota_type"><i class="fas fa-percent"></i> Quota Type:</label>
    <select name="quota_type" required>
      <option value="">--Select--</option>
      <option value="Merit">Merit</option>
      <option value="Management">Management</option>
    </select>
  </div>
  <div class="form-group">
    <label for="percentage"><i class="fas fa-percentage"></i> Percentage:</label>
    <input type="number" step="0.01" name="percentage" required />
  </div>
  <div class="form-group">
    <label for="higher_study_file"><i class="fas fa-file-upload"></i> Upload Document:</label>
    <input type="file" name="higher_study_file" />
  </div>
  <div style="width:100%; text-align:center;">
    <button type="submit"><i class="fas fa-plus"></i> Add Higher Study</button>
  </div>
</form>
</section>

<!-- Placement Details -->
<section>
<h3><i class="fas fa-briefcase"></i> Add Placement Details</h3>
<form method="POST" action="" enctype="multipart/form-data">
  <input type="hidden" name="action" value="add_placement" />
  <div class="form-group">
    <label for="student_id"><i class="fas fa-user"></i> Select Student:</label>
    <select name="student_id" required>
      <option value="">--Select Student--</option>
      <?php foreach($students_list as $s): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="form-group">
    <label for="company_name"><i class="fas fa-building"></i> Company Name:</label>
    <input type="text" name="company_name" required />
  </div>
  <div class="form-group">
    <label for="package"><i class="fas fa-wallet"></i> Package:</label>
    <input type="text" name="package" required />
  </div>
  <div class="form-group">
    <label for="date"><i class="fas fa-calendar"></i> Date of Placement:</label>
    <input type="date" name="date" required />
  </div>
  <div class="form-group">
    <label for="designation"><i class="fas fa-user-tie"></i> Designation:</label>
    <input type="text" name="designation" required />
  </div>
  <div class="form-group">
    <label for="placement_file"><i class="fas fa-file-upload"></i> Upload Document:</label>
    <input type="file" name="placement_file" />
  </div>
  <div style="width:100%; text-align:center;">
    <button type="submit"><i class="fas fa-plus"></i> Add Placement</button>
  </div>
</form>
</section>

</body>
</html>