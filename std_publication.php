<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'i'; // Replace with your database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Create table if not exists
$create_table_sql = "
CREATE TABLE IF NOT EXISTS student_publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_no VARCHAR(50),
    student_name VARCHAR(100),
    paper_title VARCHAR(255),
    journal_conference VARCHAR(255),
    issn_isbn VARCHAR(50),
    date_of_publication DATE
)";
$conn->query($create_table_sql);

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $roll_no = $_POST['roll_no'];
    $student_name = $_POST['student_name'];
    $paper_title = $_POST['paper_title'];
    $journal_conference = $_POST['journal_conference'];
    $issn_isbn = $_POST['issn_isbn'];
    $date_of_publication = $_POST['date_of_publication'];

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO student_publications (roll_no, student_name, paper_title, journal_conference, issn_isbn, date_of_publication) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $roll_no, $student_name, $paper_title, $journal_conference, $issn_isbn, $date_of_publication);
    if ($stmt->execute()) {
        $message = "Publication record added successfully.";
    } else {
        $message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Fetch existing records
$publications = [];
$result = $conn->query("SELECT * FROM student_publications ORDER BY date_of_publication DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $publications[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Student Papers Publication</title>
<!-- Font Awesome & Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
<style>
body {
    font-family: 'Inter', sans-serif;
    background: #f9f9f9;
    margin: 0; padding: 20px;
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
.message {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
}
.message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

section {
    background: #fff;
    padding: 20px;
    margin-bottom: 50px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border: 2px solid #007bff;
    position: relative;
}
section::before {
    content: "\f5f3"; /* notebook icon */
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    position: absolute;
    top: -15px;
    left: 20px;
    background: #fff;
    padding: 0 8px;
    font-size: 20px;
    color: #007bff;
}
form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}
form > .form-group {
    flex: 1 1 250px;
    display: flex;
    flex-direction: column;
}
label {
    margin-bottom: 5px;
    font-weight: 600;
}
input[type="text"], input[type="date"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-family: 'Inter', sans-serif;
    box-sizing: border-box;
}
button {
    display: block;
    margin: 10px auto;
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
button:hover {
    background-color: #0056b3;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table, th, td {
    border: 1px solid #333;
}
th, td {
    padding: 10px;
    text-align: left;
}
tr:nth-child(even) {
    background-color: #fafafa;
}
</style>
</head>
<body>

<div class="container">

<?php if($message): ?>
<div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>"><?= $message ?></div>
<?php endif; ?>

<h2>Student Papers Publication</h2>

<!-- Add Publication Details -->
<section>
<h3><i class="fas fa-plus-circle"></i> Add Publication Details</h3>
<form method="POST" action="">
  <div class="form-group">
    <label for="roll_no"><i class="fas fa-id-badge"></i> Roll No:</label>
    <input type="text" name="roll_no" required />
  </div>
  <div class="form-group">
    <label for="student_name"><i class="fas fa-user"></i> Name of the Student:</label>
    <input type="text" name="student_name" required />
  </div>
  <div class="form-group">
    <label for="paper_title"><i class="fas fa-file-alt"></i> Title of the Paper:</label>
    <input type="text" name="paper_title" required />
  </div>
  <div class="form-group">
    <label for="journal_conference"><i class="fas fa-building"></i> Name of Journal/Conference:</label>
    <input type="text" name="journal_conference" required />
  </div>
  <div class="form-group">
    <label for="issn_isbn"><i class="fas fa-id-card"></i> ISSN/ISBN No:</label>
    <input type="text" name="issn_isbn" required />
  </div>
  <div class="form-group">
    <label for="date_of_publication"><i class="fas fa-calendar-alt"></i> Date of Publication:</label>
    <input type="date" name="date_of_publication" required />
  </div>
  <div style="width:100%; text-align:center;">
    <button type="submit"><i class="fas fa-plus"></i> Add Record</button>
  </div>
</form>
</section>

<!-- Display Existing Records -->
<h3><i class="fas fa-list"></i> Existing Publications</h3>
<table>
  <thead>
    <tr>
      <th>S.No</th>
      <th>Roll No.</th>
      <th>Name of the Student</th>
      <th>Title of the Paper</th>
      <th>Name of Journal/Conference</th>
      <th>ISSN/ISBN No</th>
      <th>Date of Publication</th>
    </tr>
  </thead>
  <tbody>
    <?php if($publications): ?>
      <?php $serial = 1; ?>
      <?php foreach($publications as $pub): ?>
        <tr>
          <td><?= $serial++; ?></td>
          <td><?= htmlspecialchars($pub['roll_no']) ?></td>
          <td><?= htmlspecialchars($pub['student_name']) ?></td>
          <td><?= htmlspecialchars($pub['paper_title']) ?></td>
          <td><?= htmlspecialchars($pub['journal_conference']) ?></td>
          <td><?= htmlspecialchars($pub['issn_isbn']) ?></td>
          <td><?= htmlspecialchars($pub['date_of_publication']) ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="7" style="text-align:center;">No publications found.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

</div>
</body>
</html>