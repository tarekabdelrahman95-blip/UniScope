<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "college_rating_system";  // CHANGE THIS to your actual database name!

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Start session ONLY if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>