<?php
// Universal Database Connection: Works on both XAMPP (Local) and Railway (Production)
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: ''; // Fallback to empty for XAMPP
$dbname = getenv('MYSQLDATABASE') ?: 'marketplace_db';
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
