<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['new_profile_photo'])) {
    
    if ($_FILES['new_profile_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/img/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['new_profile_photo']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        $allowTypes = array('jpg','png','jpeg','gif', 'webp');
        if(in_array(strtolower($fileType), $allowTypes)){
            
            // Delete old photo if it exists and isn't default.png
            $sql = "SELECT profile_photo FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $old_photo = $row['profile_photo'];
                if ($old_photo && $old_photo !== 'default.png' && file_exists($uploadDir . $old_photo)) {
                    unlink($uploadDir . $old_photo);
                }
            }

            // Move new photo
            if(move_uploaded_file($_FILES["new_profile_photo"]["tmp_name"], $targetFilePath)){
                
                // Update DB
                $update_sql = "UPDATE users SET profile_photo = ? WHERE user_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $fileName, $user_id);
                
                if ($update_stmt->execute()) {
                    // Update Session
                    $_SESSION['profile_photo'] = $fileName;
                    header("Location: ../my_bookings.php?photo_updated=1");
                    exit();
                } else {
                    die("Database update failed.");
                }
            } else {
                die("Failed to move uploaded file.");
            }
        } else {
            die("Invalid file format. Allowed: JPG, JPEG, PNG, GIF, WEBP.");
        }
    } else {
        die("Upload error: " . $_FILES['new_profile_photo']['error']);
    }
}

header("Location: ../my_bookings.php");
exit();
?>
