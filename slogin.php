<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = intval($_POST['student_id']);
    // Connect to database
    $host='localhost'; $user='root'; $pass=''; $db='iqac';
    $conn=new mysqli($host,$user,$pass,$db);
    if($conn->connect_error) die("DB connection error: " . $conn->connect_error);

    // Verify student exists
    $stmt=$conn->prepare("SELECT id FROM students WHERE id=?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result=$stmt->get_result();
    if($result->num_rows > 0){
        $_SESSION['student_id'] = $student_id;
        header("Location: view_student.php");
        exit;
    } else {
        $error="Invalid Student ID!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Student Login</title>
</head>
<body>
<h2>Login</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" action="">
    <label for="student_id">Student ID:</label>
    <input type="number" name="student_id" required />
    <button type="submit">Login</button>
</form>
</body>
</html>