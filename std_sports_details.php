<?php
// Connect to database
$host='localhost'; $user='root'; $pass=''; $db='i';
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("DB connection error: " . $conn->connect_error);

// Directory for uploads
$upload_dir = 'uploads/certificates/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle form submission
$message = '';
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_sport'])){
    $student_id=intval($_POST['student_id']);
    $sport_name=$_POST['sport_name'];
    $level=$_POST['level'];
    $position=$_POST['position'];
    $year_participated=$_POST['year_participated'];

    // Handle file upload
    $sports_certificate_filename = '';
    if(isset($_FILES['sports_certificate']) && $_FILES['sports_certificate']['error'] == UPLOAD_ERR_OK){
        $file = $_FILES['sports_certificate'];
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];

        if(in_array($ext, $allowed_exts)){
            $new_filename = uniqid('cert_', true) . '.' . $ext;
            $dest_path = $upload_dir . $new_filename;
            if(move_uploaded_file($file['tmp_name'], $dest_path)){
                $sports_certificate_filename = $dest_path;
            } else {
                $message = 'Failed to upload certificate.';
            }
        } else {
            $message = 'Invalid file type. Allowed: pdf, jpg, jpeg, png.';
        }
    }

    // Insert into database
    $stmt=$conn->prepare("INSERT INTO student_sports (student_id, sport_name, level, position, year_participated, sports_certificate) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $student_id, $sport_name, $level, $position, $year_participated, $sports_certificate_filename);
    if($stmt->execute()){
        $message='Sports record added.';
    }
    $stmt->close();
}

// Fetch all sports records
$sports_records = [];
$result = $conn->query("SELECT ss.*, s.first_name, s.last_name FROM student_sports ss JOIN students s ON ss.student_id=s.id");
if($result){
    while($row=$result->fetch_assoc()){
        $sports_records[]=$row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Student Sports Details</title>
<style>
/* Body with border and centered container */
body {
    font-family: 'Inter', sans-serif; 
    background: #f9f9f9; 
    margin: 0;
    padding: 20px;
    display: flex;
    justify-content: center;
}

/* Container for the page content */
.page-container {
    max-width: 1200px;
    width: 100%;
    border: 8px solid #333; /* Page border */
    padding: 20px;
    box-sizing: border-box;
    background-color: #fff; /* White background inside border */
    border-radius: 12px; /* Rounded corners for nice look */
}

/* Heading styles */
h2 {
    text-align: center;
    margin-bottom: 20px;
}

/* Message styles */
.message {
    padding: 10px;
    background-color: #e0ffe0;
    border: 1px solid #00cc00;
    border-radius: 4px;
    margin-bottom: 20px;
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
}

/* Form styles */
form {
    margin-bottom: 30px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    background-color: #fafafa;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
form div {
    margin-bottom: 10px;
}
label {
    display: inline-block;
    width: 200px;
    font-weight: bold;
}
input[type="text"], select, input[type="file"] {
    width: calc(100% - 210px);
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
button:hover {
    background-color: #45a049;
}

/* Table styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 auto;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
table, th, td {
    border: 1px solid #333;
}
th, td {
    padding: 10px;
    text-align: left;
}
th {
    background-color: #f2f2f2;
}
tr:nth-child(even) {
    background-color: #fafafa;
}
</style>
</head>
<body>

<div class="page-container">
<h2>Student Sports Details</h2>

<?php if($message): ?>
<div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
<h3>Add New Sports Record</h3>
<div>
    <label>Student:</label>
    <select name="student_id" required>
        <option value="">--Select Student--</option>
        <?php
        $result_students = $conn->query("SELECT id, first_name, last_name FROM students");
        if($result_students){
            while($row = $result_students->fetch_assoc()){
                echo "<option value='{$row['id']}'>{$row['first_name']} {$row['last_name']}</option>";
            }
        }
        ?>
    </select>
</div>
<div>
    <label>Sport Name:</label>
    <input type="text" name="sport_name" required />
</div>
<div>
    <label>Level (e.g., School, District, State, National):</label>
    <input type="text" name="level" required />
</div>
<div>
    <label>Position / Achievement:</label>
    <input type="text" name="position" />
</div>
<div>
    <label>Year Participated:</label>
    <input type="text" name="year_participated" required />
</div>
<div>
    <label>Certificate (PDF/JPG/PNG):</label>
    <input type="file" name="sports_certificate" accept=".pdf,.jpg,.jpeg,.png" />
</div>
<div style="margin-top:10px; text-align:center;">
    <button type="submit" name="add_sport">Add Sports Record</button>
</div>
</form>

<h3 style="text-align:center;">All Sports Records</h3>
<table>
<thead>
<tr>
    <th>Student</th>
    <th>Sport Name</th>
    <th>Level</th>
    <th>Position</th>
    <th>Year Participated</th>
    <th>Certificate</th>
</tr>
</thead>
<tbody>
<?php
if($sports_records){
    foreach($sports_records as $rec){
        echo "<tr>
            <td>".htmlspecialchars($rec['first_name'].' '.$rec['last_name'])."</td>
            <td>".htmlspecialchars($rec['sport_name'])."</td>
            <td>".htmlspecialchars($rec['level'])."</td>
            <td>".htmlspecialchars($rec['position'])."</td>
            <td>".htmlspecialchars($rec['year_participated'])."</td>
            <td>";
        if($rec['sports_certificate']){
            $file_path = htmlspecialchars($rec['sports_certificate']);
            $filename = basename($file_path);
            echo "<a href='{$file_path}' target='_blank'>{$filename}</a>";
        } else {
            echo "N/A";
        }
        echo "</td></tr>";
    }
} else {
    echo "<tr><td colspan='6'>No records found.</td></tr>";
}
?>
</tbody>
</table>
</div>

</body>
</html>