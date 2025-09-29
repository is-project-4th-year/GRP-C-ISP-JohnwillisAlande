<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Remove user_id and tutor_id cookies if set
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}
if (isset($_COOKIE['tutor_id'])) {
    setcookie('tutor_id', '', time() - 3600, '/');
}

// Prevent browser caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Redirect to correct login page
if (isset($_COOKIE['tutor_id'])) {
    header('Location: admin/login.php');
} else {
    header('Location: login.php');
}
exit;
