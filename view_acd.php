<h3>All Students - Personal Details</h3>
<?php
// Fetch all students
$all_students = [];
$result_all_students = $conn->query("SELECT * FROM students");
if($result_all_students){
    while($row = $result_all_students->fetch_assoc()){
        $all_students[] = $row;
    }
}
?>
<?php if($all_students): ?>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse;">
<tr>
  <th>ID</th>
  <th>First Name</th>
  <th>Last Name</th>
  <th>Age</th>
  <th>Father's Name</th>
  <th>Mother's Name</th>
  <th>Address</th>
  <th>Father's Occupation</th>
  <th>Mother's Occupation</th>
  <th>Father Income</th>
  <th>Admission Date</th>
  <th>Batch Year</th>
</tr>
<?php foreach($all_students as $s): ?>
<tr>
  <td><?= $s['id'] ?></td>
  <td><?= htmlspecialchars($s['first_name']) ?></td>
  <td><?= htmlspecialchars($s['last_name']) ?></td>
  <td><?= $s['age'] ?></td>
  <td><?= htmlspecialchars($s['father_name']) ?></td>
  <td><?= htmlspecialchars($s['mother_name']) ?></td>
  <td><?= htmlspecialchars($s['address']) ?></td>
  <td><?= htmlspecialchars($s['father_occupation']) ?></td>
  <td><?= htmlspecialchars($s['mother_occupation']) ?></td>
  <td><?= $s['father_income'] ?></td>
  <td><?= htmlspecialchars($s['admission_date']) ?></td>
  <td><?= htmlspecialchars($s['batch_year']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>No students found.</p>
<?php endif; ?>