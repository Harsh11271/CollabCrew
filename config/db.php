<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Replace with your password
$dbname = 'marketplace_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
