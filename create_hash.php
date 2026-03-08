<?php
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo "Password: admin123<br>";
echo "Hashed version: " . $hashed;
?>