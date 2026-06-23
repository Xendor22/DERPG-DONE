<?php
session_start();
// Clear all session variables
$_SESSION = array();
// Destroy the session cookie storage completely
session_destroy();
// Bounce back to home menu
header("Location: index.php");
exit();
?>