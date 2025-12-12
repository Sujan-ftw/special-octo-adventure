<?php
// curr_gap.php (Staff Only)
require_once 'dp_connection.php';
require_once 'header.php'; 

// --- ACCESS CHECK: MUST BE STAFF ---
if (!$is_staff) {
    die("Access Denied: You do not have permission to view Curriculum Gaps.");
}
// --- END ACCESS CHECK ---

$message='';

// --- DB LOGIC ---
$gaps = [];
$result=$conn->query("SELECT * FROM curriculum_gaps ORDER BY date DESC");
while($row=$result->fetch_assoc()){
    $gaps[]=$row;
}

// Handle form submission to add new record
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['save_gap'])){
    $course_code = $_POST['course_code'];
    $additional_content = $_POST['additional_content'];
    $action_taken = $_POST['action_taken'];
    $date = $_POST['date'];
    $resource_person = $_POST['resource_person'];
    $mode = $_POST['mode'];
    $no_of_students = intval($_POST['no_of_students']);
    $relevance_to_POs_PSOs = $_POST['relevance_to_POs_PSOs'];

    $stmt=$conn->prepare("INSERT INTO curriculum_gaps (course_code, additional_content, action_taken, date, resource_person, mode, no_of_students, relevance_to_POs_PSOs) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssis", $course_code, $additional_content, $action_taken, $date, $resource_person, $mode, $no_of_students, $relevance_to_POs_PSOs);
    
    if($stmt->execute()){
        $message='Record added successfully.';
        // Refresh data (Simple redirection is better here)
        header("Location: curr_gap.php?msg=success");
        exit();
    } else {
        $message='Error: '.$conn->error;
    }
    $stmt->close();
}

$conn->close();

start_html("Curriculum Gaps", $shared_css, $nav_links, basename($_SERVER['PHP_SELF']));

if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $message = 'Record added successfully.';
}
?>

<div class="section-box">
    <h2><i class="fa-solid fa-scissors"></i> Curriculum Gap Analysis & Action</h2>

    <?php if($message): ?>
    <div class="success message">
        <?= h($message) ?>
    </div>
    <?php endif; ?>

    <h3 style="text-align: left; margin-top: 0px; border-bottom: 2px solid var(--psg-blue); padding-bottom: 5px;">Add New Gap Record</h3>
    <form method="POST" style="margin-top: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div class="form-group"><label for="course_code">Course Code:</label><input type="text" name="course_code" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 100%;"/></div>
            <div class="form-group"><label for="date">Date:</label><input type="date" name="date" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 100%;"/></div>
            <div class="form-group"><label for="resource_person">Resource Person:</label><input type="text" name="resource_person" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 100%;"/></div>
            <div class="form-group"><label for="mode">Mode:</label><input type="text" name="mode" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 100%;"/></div>
            <div class="form-group"><label for="no_of_students">No. of Students Present:</label><input type="number" name="no_of_students" min="0" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 100%;"/></div>
            <div class="form-group" style="grid-column: span 2;"><label for="additional_content">Additional Content Identified:</label><textarea name="additional_content" rows="3" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 100%;"></textarea></div>
            <div class="form-group" style="grid-column: span 2;"><label for="action_taken">Action Taken:</label><textarea name="action_taken" rows="3" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 100%;"></textarea></div>
            <div class="form-group" style="grid-column: span 2;"><label for="relevance_to_POs_PSOs">Relevance to POs and PSOs:</label><textarea name="relevance_to_POs_PSOs" rows="3" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 100%;"></textarea></div>
        </div>
        <div style="text-align:center; margin-top: 20px;">
            <button type="submit" name="save_gap" style="padding: 10px 20px; background-color: var(--psg-blue); color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Save Record</button>
        </div>
    </form>

    <h3 style="text-align: left; margin-top: 40px; border-bottom: 2px solid var(--psg-yellow); padding-bottom: 5px;">Existing Curriculum Gaps</h3>
    <?php if($gaps): ?>
    <div style="overflow-x: auto;">
    <table>
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Additional Content</th>
                <th>Action Taken</th>
                <th>Date</th>
                <th>Resource Person</th>
                <th>Mode</th>
                <th>No. of Students</th>
                <th>Relevance to POs & PSOs</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($gaps as $g): ?>
            <tr>
                <td><?= h($g['course_code']) ?></td>
                <td><?= h($g['additional_content']) ?></td>
                <td><?= h($g['action_taken']) ?></td>
                <td><?= h($g['date']) ?></td>
                <td><?= h($g['resource_person']) ?></td>
                <td><?= h($g['mode']) ?></td>
                <td><?= h($g['no_of_students']) ?></td>
                <td><?= h($g['relevance_to_POs_PSOs']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php else: ?>
        <div class="message">No records found.</div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>