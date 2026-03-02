<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Verify the booking belongs to the current user
    $verify = $conn->prepare("SELECT booking_id FROM bookings WHERE booking_id = ? AND user_id = ?");
    $verify->bind_param("ii", $booking_id, $user_id);
    $verify->execute();
    if ($verify->get_result()->num_rows > 0) {
        
        // Update status to CANCELLED
        $stmt = $conn->prepare("UPDATE bookings SET status = 'CANCELLED' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
    }
}

header("Location: ../my_bookings.php");
exit();
?>
