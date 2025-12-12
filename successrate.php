<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "i";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Replace these with your actual column names
$student_id_col_students = 'id'; // in students table
$student_id_col_academic = 'student_id'; // in academic_details table
$batch_year_col_students = 'batch_year'; // in students table

// SQL for Success rate without backlog
$sql_without_backlog = "
SELECT 
    students.{$batch_year_col_students} AS batch_year,
    COUNT(DISTINCT academic_details.{$student_id_col_academic}) AS total_students,
    SUM(CASE WHEN NOT EXISTS (
        SELECT 1 FROM semester_marks sm2
        WHERE sm2.{$student_id_col_academic} = academic_details.{$student_id_col_academic} AND sm2.pass_fail = 'fail'
    ) THEN 1 ELSE 0 END) AS success_without_backlog
FROM 
    academic_details
LEFT JOIN 
    students ON academic_details.{$student_id_col_academic} = students.{$student_id_col_students}
LEFT JOIN
    semester_marks ON academic_details.{$student_id_col_academic} = semester_marks.{$student_id_col_academic}
GROUP BY 
    students.{$batch_year_col_students}
ORDER BY 
    students.{$batch_year_col_students} DESC
";

// SQL for Success index with backlog
$sql_with_backlog = "
SELECT 
    students.{$batch_year_col_students} AS batch_year,
    COUNT(DISTINCT academic_details.{$student_id_col_academic}) AS total_students,
    SUM(CASE WHEN 
        (SELECT COUNT(*) FROM semester_marks sm2 WHERE sm2.{$student_id_col_academic} = academic_details.{$student_id_col_academic} AND sm2.pass_fail = 'fail') > 0
        AND EXISTS (
            SELECT 1 FROM semester_marks sm3 
            WHERE sm3.{$student_id_col_academic} = academic_details.{$student_id_col_academic} AND sm3.pass_fail = 'pass'
        ) THEN 1 ELSE 0 END) AS success_with_backlog
FROM 
    academic_details
LEFT JOIN 
    students ON academic_details.{$student_id_col_academic} = students.{$student_id_col_students}
LEFT JOIN
    semester_marks ON academic_details.{$student_id_col_academic} = semester_marks.{$student_id_col_academic}
GROUP BY 
    students.{$batch_year_col_students}
ORDER BY 
    students.{$batch_year_col_students} DESC
";

// Execute the queries
$result_without_backlog = $conn->query($sql_without_backlog);
if (!$result_without_backlog) {
    die("Error in query for success rate without backlog: " . $conn->error);
}

$result_with_backlog = $conn->query($sql_with_backlog);
if (!$result_with_backlog) {
    die("Error in query for success index with backlog: " . $conn->error);
}

// Fetch data into arrays keyed by batch year for easy access
$data_without_backlog = [];
while ($row = $result_without_backlog->fetch_assoc()) {
    $batch = $row['batch_year'];
    $data_without_backlog[$batch] = [
        'total_students' => (int)$row['total_students'],
        'success_without_backlog' => (int)$row['success_without_backlog']
    ];
}

$data_with_backlog = [];
while ($row = $result_with_backlog->fetch_assoc()) {
    $batch = $row['batch_year'];
    $data_with_backlog[$batch] = [
        'total_students' => (int)$row['total_students'],
        'success_with_backlog' => (int)$row['success_with_backlog']
    ];
}

// Get all batch years for headers
$batch_years = array_unique(array_merge(array_keys($data_without_backlog), array_keys($data_with_backlog)));
sort($batch_years);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Academic Performance Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h2 { text-align: center; margin-top: 20px; }
        table { width: 90%; margin: 20px auto; border-collapse: collapse; font-size: 14px; }
        th, td { border: 1px solid #999; padding: 8px 10px; text-align: center; }
        th { background-color: #b0e0e6; }
        .header-cell { text-align: left; font-weight: bold; background-color: #add8e6; }
        .section { margin-bottom: 40px; }
        p { text-align: center; margin: 10px 0; }
        /* Additional styling if needed */
    </style>
</head>
<body>

<h2>Student Academic Performance Report</h2>

<!-- 1. Success rate without backlogs -->
<div class="section">
    <div class="section-title">1. Success rate without backlogs</div>
    <table>
        <tr>
            <th class="header-cell">Item</th>
            <?php foreach ($batch_years as $batch): ?>
                <th><?php echo htmlspecialchars($batch); ?></th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td style="text-align:left; padding-left:10px;">Total number of students (admitted through state level counseling + admitted through Institute on level quota + actually admitted through lateral entry i.e., N1+N2+N3 (Z))</td>
            <?php foreach ($batch_years as $batch): ?>
                <td>
                    <?php
                    echo isset($data_without_backlog[$batch]['total_students']) ? $data_without_backlog[$batch]['total_students'] : 'N/A';
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td style="text-align:left; padding-left:10px;">Number of students who have passed without backlogs in the stipulated period* (X)</td>
            <?php foreach ($batch_years as $batch): ?>
                <td>
                    <?php
                    echo isset($data_without_backlog[$batch]['success_without_backlog']) ? $data_without_backlog[$batch]['success_without_backlog'] : 'N/A';
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td style="text-align:left; padding-left:10px;">Success index (SI) (X/Z)</td>
<?php foreach ($batch_years as $batch): ?>
    <td>
        <?php
        if (isset($data_without_backlog[$batch]['total_students']) && $data_without_backlog[$batch]['total_students'] > 0) {
            $si_value = round(($data_without_backlog[$batch]['success_without_backlog'] / $data_without_backlog[$batch]['total_students']) * 100, 2) . '%';
            $point_value = $data_without_backlog[$batch]['success_without_backlog'] / $data_without_backlog[$batch]['total_students'] * 100;
            echo 'SI: ' . $si_value . ', Point Value: ' . number_format($point_value, 0);
        } else {
            echo 'N/A';
        }
        ?>
    </td>
<?php endforeach; ?>
        </tr>
    </table>
</div>

<!-- 2. Success index with backlog -->
<div class="section">
    <div class="section-title">2. Success Index with backlog</div>
    <table>
        <tr>
            <th class="header-cell">Item</th>
            <?php foreach ($batch_years as $batch): ?>
                <th><?php echo htmlspecialchars($batch); ?></th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td style="text-align:left; padding-left:10px;">Total number of students (admitted through state level counseling + admitted through Institute on level quota + actually admitted through lateral entry i.e., N1+N2+N3 (Z))</td>
            <?php foreach ($batch_years as $batch): ?>
                <td>
                    <?php
                    echo isset($data_with_backlog[$batch]['total_students']) ? $data_with_backlog[$batch]['total_students'] : 'N/A';
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td style="text-align:left; padding-left:10px;">Number of students successfully graduated with backlog</td>
            <?php foreach ($batch_years as $batch): ?>
                <td>
                    <?php
                    echo isset($data_with_backlog[$batch]['success_with_backlog']) ? $data_with_backlog[$batch]['success_with_backlog'] : 'N/A';
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td style="text-align:left; padding-left:10px;">Success index (SI) (X/Z)</td>
            <?php foreach ($batch_years as $batch): ?>
                <td>
                    <?php
                    if (isset($data_with_backlog[$batch]['total_students']) && $data_with_backlog[$batch]['total_students'] > 0) {
                        $si_value = round(($data_with_backlog[$batch]['success_with_backlog'] / $data_with_backlog[$batch]['total_students']) * 100, 2) . '%';
                        echo $si_value;
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
    </table>
</div>

<!-- Custom Table as per your original design -->
<div class="section">
    <div class="section-title"><b>Table 3: Success rate without backlogs*: (Data to be given once in a year)</b></div>
    <p>Stipulated period of study (within 3 yrs): From the year __________ to __________</p>
    <table>
        <tr>
            <th>Item</th>
            <?php foreach ($batch_years as $batch): ?>
                <th><?php echo htmlspecialchars($batch); ?></th>
            <?php endforeach; ?>
        </tr>
        <?php
        foreach ($batch_years as $batch):
            $total_students = isset($data_without_backlog[$batch]['total_students']) ? $data_without_backlog[$batch]['total_students'] : 0;
            $success_without_backlog = isset($data_without_backlog[$batch]['success_without_backlog']) ? $data_without_backlog[$batch]['success_without_backlog'] : 0;
            $si = ($total_students > 0) ? round(($success_without_backlog / $total_students) * 100, 2) . '%' : 'N/A';
        ?>
        <tr>
            <td>Success rate without backlogs*</td>
            <?php foreach ($batch_years as $batch2): ?>
                <td>
                    <?php
                    if ($batch2 == $batch) {
                        echo $total_students;
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td>Batch <?php echo htmlspecialchars($batch); ?></td>
            <?php foreach ($batch_years as $batch2): ?>
                <td>
                    <?php
                    if ($batch2 == $batch) {
                        echo $success_without_backlog;
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td>Success index (SI)</td>
            <?php foreach ($batch_years as $batch2): ?>
                <td>
                    <?php
                    if ($batch2 == $batch) {
                        echo $si;
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php
$conn->close();
?>

</body>
</html>