<?php
// Use the EXACT details from InfinityFree MySQL Databases page
$servername = "sql105.infinityfree.com";  // ← Copy this exactly
$username = "if0_41335583";               // ← Copy this exactly
$password = "M3XVAxD6lePtWC";            // ← The password you set
$dbname = "if0_41335583_UniScope";    // ← Copy this exactly

// Rest of your code stays the same
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
;
?>