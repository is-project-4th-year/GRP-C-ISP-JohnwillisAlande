<?php
session_start();
include 'connect.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Remove user_id cookie if set
if (isset($_COOKIE['user_id'])) {
   setcookie('user_id', '', time() - 3600, '/');
}

// Prevent browser caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Redirect to login page
header('Location: ../login.php');
exit;
?>