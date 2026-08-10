<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If session key is missing, boot the user to login
if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/auth/login.php");
    exit();
}