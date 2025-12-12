<?php
// Database connection details
$host='localhost'; $user='root'; $pass=''; $db='m';
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("DB connection error: " . $conn->connect_error);

// Create upload directories if they don't exist
$targetDir='uploads/';
$imageDir='uploads/images/';
if(!is_dir($targetDir)) mkdir($targetDir,0777,true);
if(!is_dir($imageDir)) mkdir($imageDir,0777,true);

$message=''; // For feedback messages
$files=[];   // To store fetched records

// Handle form submission
if($_SERVER['REQUEST_METHOD']=='POST'){
    // Check required fields
    if(
        isset($_FILES['file']) &&
        isset($_POST['department']) &&
        isset($_POST['year']) &&
        isset($_POST['company']) &&
        isset($_POST['status']) &&
        isset($_POST['staff_id']) &&
        isset($_POST['sign_date'])
    ){
        $file=$_FILES['file'];
        $department=$_POST['department'];
        $year=$_POST['year'];
        $company=$_POST['company'];
        $status=$_POST['status'];
        $staff_id=$_POST['staff_id'];
        $sign_date=$_POST['sign_date'];

        // Handle document upload
        $filename=null;
        $targetFilePath=null;
        if($file['error']==UPLOAD_ERR_OK){
            $filename=basename(preg_replace('/[^a-zA-Z0-9._-]/','',$file['name']));
            $targetFilePath=$targetDir.time().'_'.$filename;
            if(!move_uploaded_file($file['tmp_name'],$targetFilePath)){
                $message.="Error uploading document.<br>";
            }
        }else{
            $message.="Document upload error code: ".$file['error']."<br>";
        }

        // Handle optional image upload
        $imagePath=null;
        if(isset($_FILES['image']) && $_FILES['image']['error']==UPLOAD_ERR_OK){
            $img=$_FILES['image'];
            $allowed=['image/jpeg','image/png','image/jpg'];
            if(in_array($img['type'],$allowed)){
                $imgName=basename(preg_replace('/[^a-zA-Z0-9._-]/','',$img['name']));
                $imagePath=$imageDir.time().'_'.$imgName;
                if(!move_uploaded_file($img['tmp_name'],$imagePath)){
                    $message.="Error uploading image.<br>";
                    $imagePath=null;
                }
            }else{
                $message.="Invalid image type. Only JPG and PNG allowed.<br>";
            }
        }

        // Insert into database
        $uploadDate=date("Y-m-d H:i:s");
        $stmt=$conn->prepare("INSERT INTO mou_files (department, year, company, status, filename, filepath, upload_date, image, staff_id, sign_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt===false){
            die('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("ssssssssss", $department, $year, $company, $status, $filename, $targetFilePath, $uploadDate, $imagePath, $staff_id, $sign_date);
        if($stmt->execute()){
            $message.="File uploaded successfully.";
        }else{
            $message.="Error saving record: ".$stmt->error;
        }
        $stmt->close();
    }else{
        $message.="Missing form fields.<br>";
    }
}

// Fetch latest records
$result=$conn->query("SELECT * FROM mou_files ORDER BY upload_date DESC LIMIT 10");
if($result){
    while($row=$result->fetch_assoc()){
        $files[]=$row;
    }
}

// Handle delete request
if(isset($_GET['action']) && $_GET['action']=='delete' && isset($_GET['id'])){
    $del_id=intval($_GET['id']);
    $resDel=$conn->query("SELECT * FROM mou_files WHERE id=$del_id");
    if($resDel && $resDel->num_rows>0){
        $record=$resDel->fetch_assoc();
        @unlink($record['filepath']);
        if($record['image']) @unlink($record['image']);
        $stmtDel=$conn->prepare("DELETE FROM mou_files WHERE id=?");
        $stmtDel->bind_param("i",$del_id);
        $stmtDel->execute();
        $stmtDel->close();
        header("Location: upload.php?msg=Record deleted");
        exit;
    }
}

// Redirect to edit page
if(isset($_GET['action']) && $_GET['action']=='edit' && isset($_GET['id'])){
    header("Location: edit.php?id=".$_GET['id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Upload MOU Documents - PSG Polytechnic Portal</title>
<!-- Include your CSS & Font Awesome as needed -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
body { font-family: 'Inter', sans-serif; margin: 20px; }
h2 { margin-top: 30px; }
.message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
.message.success { background-color: #d4edda; color: #155724; }
.message.error { background-color: #f8d7da; color: #721c24; }
form { background: #f0f0f0; padding: 20px; border-radius: 8px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; }
input[type="text"], input[type="date"], select, input[type="file"] { width: 100%; padding: 8px; }
button { padding: 10px 20px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #0056b3; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background-color: #eee; }
.back-button {
  display: inline-block;
  margin-bottom: 20px;
  padding: 8px 12px;
  background-color: #6c757d;
  color: #fff;
  text-decoration: none;
  border-radius: 4px;
  font-weight: 600;
}
.back-button:hover {
  background-color: #5a6268;
}
</style>
</head>
<body>

<!-- Back to Index Button -->
<a href="index.php" class="back-button">🔙 Back to Home</a>

<?php if($msg=isset($_GET['msg']) ? $_GET['msg'] : ''): ?>
<div style="background:#d4edda; padding:10px; border-radius:4px; margin-bottom:20px;">
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<?php if ($message): ?>
<div class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
  <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Upload Form -->
<h2>Upload a New MOU Document</h2>
<form method="POST" enctype="multipart/form-data" action="">
  <div class="form-group">
    <label for="file">Choose Document File:</label>
    <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required />
  </div>
  <div class="form-group">
    <label for="image">Upload Image (optional):</label>
    <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png" />
  </div>
  <div class="form-group">
    <label for="department">Select Department:</label>
    <select id="department" name="department" required>
      <option value="">--Select Department--</option>
      <option value="Information Technology">Information Technology</option>
      <option value="Computer Networking">Computer Networking</option>
      <option value="Electronics and Communication">Electronics and Communication</option>
      <option value="Mechanical Engineering">Mechanical Engineering</option>
      <option value="Civil Engineering">Civil Engineering</option>
      <option value="Electrical and Electronics Engineering">Electrical and Electronics Engineering</option>
      <option value="Computer Science and Engineering">Computer Science and Engineering</option>
    </select>
  </div>
  <div class="form-group">
    <label for="year">Select Year:</label>
    <select id="year" name="year" required>
      <option value="">--Select Year--</option>
      <option value="2020-21">2020-21</option>
      <option value="2021-22">2021-22</option>
      <option value="2022-23">2022-23</option>
      <option value="2023-24">2023-24</option>
      <option value="2024-25">2024-25</option>
    </select>
  </div>
  <div class="form-group">
    <label for="company">Company/Organization Name:</label>
    <input type="text" id="company" name="company" placeholder="Enter company name" required />
  </div>
  <div class="form-group">
    <label for="status">MOU Status:</label>
    <select id="status" name="status" required>
      <option value="Active">Active</option>
      <option value="Completed">Completed</option>
      <option value="Pending">Pending</option>
      <option value="Expired">Expired</option>
    </select>
  </div>
  <div class="form-group">
    <label for="staff_id">Staff ID:</label>
    <input type="text" id="staff_id" name="staff_id" placeholder="Enter Staff ID" required />
  </div>
  <div class="form-group">
    <label for="sign_date">Date of MOU Signing:</label>
    <input type="date" id="sign_date" name="sign_date" required />
  </div>
  <button type="submit" class="btn-upload">📤 Upload Document</button>
</form>

<!-- List Files with Edit & Remove -->
<h2>Recently Uploaded Files</h2>
<?php if ($files): ?>
<table>
  <thead>
    <tr>
      <th>Department</th>
      <th>Year</th>
      <th>Company</th>
      <th>Status</th>
      <th>Filename</th>
      <th>Upload Date</th>
      <th>Staff ID</th>
      <th>Date of MOU Signing</th>
      <th>Image</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($files as $file): ?>
    <tr>
      <td><?= htmlspecialchars($file['department']) ?></td>
      <td><?= htmlspecialchars($file['year']) ?></td>
      <td><?= htmlspecialchars($file['company']) ?></td>
      <td><?= htmlspecialchars($file['status']) ?></td>
      <td><a href="<?= htmlspecialchars($file['filepath']) ?>" target="_blank"><?= htmlspecialchars($file['filename']) ?></a></td>
      <td><?= htmlspecialchars($file['upload_date']) ?></td>
      <td><?= htmlspecialchars($file['staff_id']) ?></td>
      <td><?= htmlspecialchars($file['sign_date']) ?></td>
      <td>
        <?php if($file['image']): ?>
          <img src="<?= htmlspecialchars($file['image']) ?>" width="80" />
        <?php else: ?>
          N/A
        <?php endif; ?>
      </td>
      <td>
        <a href="edit.php?id=<?= $file['id'] ?>">✏️ Edit</a> | 
        <a href="delete.php?action=delete&id=<?= $file['id'] ?>" onclick="return confirm('Are you sure to delete?');">🗑️ Remove</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p>No files uploaded yet.</p>
<?php endif; ?>

</body>
</html>