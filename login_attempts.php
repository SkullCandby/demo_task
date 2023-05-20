<?php
// login_attempts.php
session_start();

$host = 'localhost';
$dbUsername = 'root';
$dbPassword = 'GlebDasha2001';
$dbName = 'demo';

$connection = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}

$username = $_GET['username'];

$query = "SELECT login_attempts, last_login_attempt FROM users WHERE username = '$username'";
$result = $connection->query($query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $user]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>