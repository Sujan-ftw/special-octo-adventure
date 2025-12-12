<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "iqac");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $id = intval($_POST['id']);
    $event_name = $conn->real_escape_string($_POST['event_name']);
    $event_place = $conn->real_escape_string($_POST['event_place']);
    $participation_date = $conn->real_escape_string($_POST['participation_date']);
    $level = $conn->real_escape_string($_POST['level']);
    // Handle file upload for certificate
    $certificate_copy = '';

    if (isset($_FILES['certificate_copy']) && $_FILES['certificate_copy']['error'] == 0) {
        $target_dir = "uploads/";
        // Ensure the uploads directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target_file = $target_dir . basename($_FILES["certificate_copy"]["name"]);
        if (move_uploaded_file($_FILES["certificate_copy"]["tmp_name"], $target_file)) {
            $certificate_copy = $target_file;
        } else {
            echo "<p class='error'>Error uploading file.</p>";
        }
    }

    // Prepare and execute insert statement
    $sql = "INSERT INTO student_participation (id, event_name, event_place, participation_date, level, certificate_copy)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $id, $event_name, $event_place, $participation_date, $level, $certificate_copy);

    if ($stmt->execute()) {
        echo "<p class='success'>Record added successfully.</p>";
    } else {
        echo "<p class='error'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Participation Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .success {
            color: green;
            margin-bottom: 15px;
            text-align: center;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
<h2>Student Participation Entry</h2>
<form method="POST" enctype="multipart/form-data">
    <label for="id">Student ID:</label>
    <input type="number" id="id" name="id" required>

    <label for="event_name">Event Name:</label>
    <input type="text" id="event_name" name="event_name" required>

    <label for="event_place">Event Place:</label>
    <input type="text" id="event_place" name="event_place" required>

    <label for="participation_date">Participation Date:</label>
    <input type="date" id="participation_date" name="participation_date" required>

    <label for="level">Level:</label>
    <select id="level" name="level" required>
        <option value="National">National</option>
        <option value="State">State</option>
    </select>

    <label for="certificate_copy">Certificate Copy:</label>
    <input type="file" id="certificate_copy" name="certificate_copy">

    <input type="submit" value="Submit">
</form>
</div>
</body>
</html>