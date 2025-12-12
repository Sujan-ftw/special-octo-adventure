<?php
// std_upload.php - student profile completion form and save
require_once 'utils.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    // Redirect non-logged-in or non-students to login/register
    header("Location: stlogin.php");
    exit;
}

$iqac = connectDatabase();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_save'])) {
    // Collect and sanitize inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $dob = $_POST['dob'] ?? null;
    $entry_type = $_POST['entry_type'] ?? '';
    $management_quota = isset($_POST['management_quota']) ? 1 : 0;
    $counseling_quota = isset($_POST['counseling_quota']) ? 1 : 0;
    $address = trim($_POST['address'] ?? '');
    $email_id = trim($_POST['email_id'] ?? '');
    $father_mobile = trim($_POST['father_mobile'] ?? '');
    $admission_date = $_POST['admission_date'] ?? null;
    $batch_year = trim($_POST['batch_year'] ?? '');
    $student_mobile = trim($_POST['student_mobile'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');
    $father_occupation = trim($_POST['father_occupation'] ?? '');
    $mother_occupation = trim($_POST['mother_occupation'] ?? '');
    $father_income = floatval($_POST['father_income'] ?? 0.0);
    $department = trim($_POST['department'] ?? '');

    // handle uploads - store paths in the DB
    $student_photo_path = null;
    if (!empty($_FILES['student_photo']['tmp_name'])) {
        $ext = pathinfo($_FILES['student_photo']['name'], PATHINFO_EXTENSION);
        $newfile = 'uploads/students/' . uniqid('student_') . '.' . $ext;
        if (!is_dir(dirname($newfile))) mkdir(dirname($newfile), 0755, true);
        move_uploaded_file($_FILES['student_photo']['tmp_name'], $newfile);
        $student_photo_path = $newfile;
    }

    $father_photo_path = null;
    if (!empty($_FILES['father_photo']['tmp_name'])) {
        $ext = pathinfo($_FILES['father_photo']['name'], PATHINFO_EXTENSION);
        $newfile = 'uploads/parents/' . uniqid('father_') . '.' . $ext;
        if (!is_dir(dirname($newfile))) mkdir(dirname($newfile), 0755, true);
        move_uploaded_file($_FILES['father_photo']['tmp_name'], $newfile);
        $father_photo_path = $newfile;
    }

    $mother_photo_path = null;
    if (!empty($_FILES['mother_photo']['tmp_name'])) {
        $ext = pathinfo($_FILES['mother_photo']['name'], PATHINFO_EXTENSION);
        $newfile = 'uploads/parents/' . uniqid('mother_') . '.' . $ext;
        if (!is_dir(dirname($newfile))) mkdir(dirname($newfile), 0755, true);
        move_uploaded_file($_FILES['mother_photo']['tmp_name'], $newfile);
        $mother_photo_path = $newfile;
    }

    // Insert into students table with department
    $stmt = $iqac->prepare("
        INSERT INTO students (
            first_name,last_name,age,father_name,mother_name,address,
            father_occupation,mother_occupation,father_income,admission_date,
            batch_year,department,student_mobile,email_id,father_mobile,
            student_photo,dob,entry_type,management_quota,counselling_quota,
            father_photo,mother_photo
        ) VALUES (
            ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
        )
    ");
    if (!$stmt) {
        $error = "Prepare failed: " . $iqac->error;
    } else {
        $types = "ssisssssdsssssssssssss";
        $stmt->bind_param($types,
            $first_name, $last_name, $age, $father_name, $mother_name, $address,
            $father_occupation, $mother_occupation, $father_income, $admission_date,
            $batch_year, $department, $student_mobile, $email_id, $father_mobile,
            $student_photo_path, $dob, $entry_type, $management_quota, $counseling_quota,
            $father_photo_path, $mother_photo_path
        );
        if ($stmt->execute()) {
            $new_student_id = $stmt->insert_id;
            $_SESSION['student_id'] = $new_student_id;
            $_SESSION['student_profile_complete'] = true;
            $_SESSION['department'] = $department;
            $message = "Profile saved successfully.";
        } else {
            $error = "Save failed: " . $stmt->error;
        }
        $stmt->close();
    }
}

require_once 'header.php';
generate_header("Complete Student Profile");
?>

<div class="section-box">
  <h2>Complete Student Profile</h2>
  <?php if ($message): ?><div class="success message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="error message"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="form-grid">
    <div class="form-group"><label>First Name</label><input name="first_name" required /></div>
    <div class="form-group"><label>Last Name</label><input name="last_name" required /></div>
    <div class="form-group"><label>Age</label><input type="number" name="age" required /></div>
    <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" required /></div>
    <div class="form-group"><label>Entry Type</label>
      <select name="entry_type" required><option value="">--Select--</option><option value="Regular">Regular</option><option value="Lateral">Lateral</option></select>
    </div>

    <div class="form-group"><label>Department</label>
      <select name="department" required>
        <option value="">--Select Department--</option>
        <option>Information Technology</option>
        <option>Computer Networking</option>
        <option>Electronics and Communication</option>
        <option>Mechanical Engineering</option>
        <option>Civil Engineering</option>
        <option>Electrical and Electronics Engineering</option>
        <option>Computer Science and Engineering</option>
      </select>
    </div>

    <div class="form-group"><label>Address</label><textarea name="address"></textarea></div>
    <div class="form-group"><label>Email</label><input type="email" name="email_id" /></div>
    <div class="form-group"><label>Student Mobile</label><input name="student_mobile" /></div>
    <div class="form-group"><label>Father Mobile</label><input name="father_mobile" /></div>
    <div class="form-group"><label>Father Name</label><input name="father_name" /></div>
    <div class="form-group"><label>Mother Name</label><input name="mother_name" /></div>
    <div class="form-group"><label>Father Occupation</label><input name="father_occupation" /></div>
    <div class="form-group"><label>Mother Occupation</label><input name="mother_occupation" /></div>
    <div class="form-group"><label>Father Income</label><input type="number" step="0.01" name="father_income" /></div>
    <div class="form-group"><label>Admission Date</label><input type="date" name="admission_date" /></div>
    <div class="form-group"><label>Batch Year</label><input name="batch_year" placeholder="e.g., 2023-2024" /></div>

    <div class="form-group">
      <label><input type="checkbox" name="management_quota" /> Management Quota</label>
    </div>
    <div class="form-group">
      <label><input type="checkbox" name="counseling_quota" /> Counseling Quota</label>
    </div>

    <div class="form-group"><label>Student Photo</label><input type="file" name="student_photo" accept="image/*" /></div>
    <div class="form-group"><label>Father Photo</label><input type="file" name="father_photo" accept="image/*" /></div>
    <div class="form-group"><label>Mother Photo</label><input type="file" name="mother_photo" accept="image/*" /></div>

    <div class="form-group" style="grid-column: 1 / -1;">
      <button type="submit" name="student_save" class="btn">Save Profile</button>
    </div>
  </form>
</div>

<?php generate_footer(); ?>
