<?php
// LOCAL COMPUTER SETTINGS - USE THIS FOR XAMPP ONLY!
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "college_rating_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>