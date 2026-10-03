<?php

require_once __DIR__ . "/session.php";

// Remove admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Redirect to admin login
header("Location: login.php");
exit();

?>