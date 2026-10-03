<?php

session_start();

// Remove all session data
session_unset();

// Destroy the session
session_destroy();

// Go back to home page
header("Location: index.php");
exit();

?>