<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user role
$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$role_stmt->bind_param("i", $user_id);
$role_stmt->execute();
$role_stmt->bind_result($user_role);
$role_stmt->fetch();
$role_stmt->close();

// Redirect based on role
if ($user_role === 'client') {
    header("Location: client_dashboard.php");
} elseif ($user_role === 'freelancer') {
    header("Location: freelancer_dashboard.php");
} else {
    // 'both' — default to client dashboard with a toggle option
    header("Location: client_dashboard.php");
}
exit;
?>
