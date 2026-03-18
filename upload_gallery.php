<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['gallery_images'])) {
    $college_id = $_POST['college_id'];
    $upload_dir = "assets/images/uploads/";
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $uploaded_files = [];
    
    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['gallery_images']['error'][$key] == 0) {
            $file_name = time() . "_" . $_FILES['gallery_images']['name'][$key];
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($tmp_name, $file_path)) {
                $uploaded_files[] = $file_path;
            }
        }
    }
    
    // Get existing gallery images
    $sql = "SELECT gallery_images FROM colleges WHERE college_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $college_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $college = $result->fetch_assoc();
    
    $existing = !empty($college['gallery_images']) ? explode(',', $college['gallery_images']) : [];
    $all_images = array_merge($existing, $uploaded_files);
    $gallery_string = implode(',', $all_images);
    
    // Update database
    $update_sql = "UPDATE colleges SET gallery_images = ? WHERE college_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $gallery_string, $college_id);
    
    if ($update_stmt->execute()) {
        header("Location: college.php?id=" . $college_id . "&uploaded=1");
        exit();
    } else {
        echo "Error updating gallery.";
    }
}
?>