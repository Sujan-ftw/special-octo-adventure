<h3>Achievements</h3>
<?php if($achievement_records): ?>
<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse;">
<tr>
  <th>Student Name</th>
  <th>Title</th>
  <th>Description</th>
  <th>Date Awarded</th>
</tr>
<?php foreach($achievement_records as $ach): ?>
<tr>
  <td><?= htmlspecialchars($ach['first_name'].' '.$ach['last_name']) ?></td>
  <td><?= htmlspecialchars($ach['achievement_title']) ?></td>
  <td><?= htmlspecialchars($ach['description']) ?></td>
  <td><?= htmlspecialchars($ach['date_awarded']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>No achievements recorded.</p>
<?php endif; ?>