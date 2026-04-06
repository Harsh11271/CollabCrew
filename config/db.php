<?php
// Universal Database Connection: Works on both XAMPP (Local) and Railway (Production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'marketplace_db';
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $dbname, (int)$port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>