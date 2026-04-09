<?php
// Use the EXACT details from InfinityFree MySQL Databases page
$servername = "sql105.infinityfree.com";
$username = "if0_41335583";
$password = "M3XVAxD6lePtWC";
$dbname = "if0_41335583_UniScope";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>