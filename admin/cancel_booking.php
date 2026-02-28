<?php
include '../includes/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Update status instead of deleting for records
    $conn->query("UPDATE bookings SET status = 'CANCELLED' WHERE booking_id = $id");
    header("Location: view_bookings.php");
    exit();
}
?>