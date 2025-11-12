<?php
session_start();

// Destroy session
session_destroy();

// Clear all session variables
$_SESSION = [];

// Redirect to login page
header('Location: login.php');
exit();
?>
