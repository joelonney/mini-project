<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $bus_id = $_POST['bus_id'];
    $description = $_POST['description'];
    
    // Optional: Validate that this user actually was on this bus (omitted for brevity, but good for security)
    
    $stmt = $conn->prepare("INSERT INTO lost_and_found (bus_id, description, status) VALUES (?, ?, 'FOUND')");
    $stmt->bind_param("is", $bus_id, $description);
    
    if ($stmt->execute()) {
        header("Location: ../my_bookings.php?msg=report_success");
    } else {
        header("Location: ../my_bookings.php?error=report_failed");
    }
} else {
    header("Location: ../index.php");
}
?>
