<?php
require_once __DIR__ . '/session.php';
echo '<h2>Admin Session Test</h2>';
echo 'admin_id: ' . htmlspecialchars((string)($_SESSION['admin_id'] ?? 'NOT SET')) . '<br>';
echo 'admin_username: ' . htmlspecialchars((string)($_SESSION['admin_username'] ?? 'NOT SET')) . '<br><br>';
echo '<a href="cars.php">Open Manage Cars</a>';
?>
