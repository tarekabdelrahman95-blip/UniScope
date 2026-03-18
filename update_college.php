<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $college_id = $_POST['college_id'];
    $overview = $_POST['overview'];
    
    $sql = "UPDATE colleges SET overview = ? WHERE college_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $overview, $college_id);
    
    if ($stmt->execute()) {
        header("Location: college.php?id=" . $college_id . "&updated=1");
        exit();
    } else {
        echo "Error updating overview.";
    }
}
?>