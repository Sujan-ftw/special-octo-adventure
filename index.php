<?php
require_once 'header.php'; // Includes session & utils logic
?>

<?php if (isset($_SESSION['user_id'])): ?>
    <p>Welcome to the portal, <?= h($_SESSION['role']); ?></p>
<?php else: ?>
    <p>Please <a href="login.php">Log in</a></p>
<?php endif; ?>

<?php require_once 'footer.php'; ?>