<?php
include 'db.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Get Input
 $user_id = $_SESSION['user_id'];
 $bus_id = intval($_POST['bus_id']);
 $travel_date = $_POST['travel_date'];
 $amount = $_POST['amount'];
 $seats_str = $_POST['seats']; // e.g., "L1,L2"
 $from = $_POST['from'];
 $to = $_POST['to'];

// Explode seats into array
 $seat_numbers = explode(',', $seats_str);

// 3. Transaction Start
 $conn->begin_transaction();

try {
    foreach ($seat_numbers as $seat_number) {
        $seat_number = trim($seat_number);

        // A. Find Seat ID based on Seat Number and Bus ID
        $stmt = $conn->prepare("SELECT seat_id FROM seats WHERE seat_number = ? AND bus_id = ?");
        $stmt->bind_param("si", $seat_number, $bus_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $seat_row = $result->fetch_assoc();

        if (!$seat_row) {
            // ERROR: Seat not found in Database
            throw new Exception("Seat $seat_number not found on this bus. Did you create seats in the database?");
        }
        $seat_id = $seat_row['seat_id'];

        // B. Insert Booking
        // FIXED: Added 'booking_date' column with value CURDATE()
        $stmt_book = $conn->prepare("INSERT INTO bookings (user_id, bus_id, seat_id, booking_date, travel_date, status) VALUES (?, ?, ?, CURDATE(), ?, 'BOOKED')");
        $stmt_book->bind_param("iiss", $user_id, $bus_id, $seat_id, $travel_date);
        $stmt_book->execute();
        
        $booking_id = $conn->insert_id; 
        
        // Capture the first booking ID for redirection
        if (!isset($first_booking_id)) {
            $first_booking_id = $booking_id;
        } 

        // C. Insert Payment
        $payment_method = "CARD"; 
        $stmt_pay = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'SUCCESS')");
        $stmt_pay->bind_param("ids", $booking_id, $amount, $payment_method);
        $stmt_pay->execute();

        // D. Generate QR Data and Insert Ticket
        $qr_data = "PNR:$booking_id|BUS:$bus_id|SEAT:$seat_number|DATE:$travel_date";
        $stmt_ticket = $conn->prepare("INSERT INTO tickets (booking_id, qr_code_data) VALUES (?, ?)");
        $stmt_ticket->bind_param("is", $booking_id, $qr_data);
        $stmt_ticket->execute();
    }

    // Commit Transaction
    $conn->commit();

    // Redirect to Ticket
    // Show the first ticket of the batch
    if (isset($first_booking_id)) {
        header("Location: ../ticket.php?id=" . $first_booking_id);
    } else {
        header("Location: ../my_bookings.php"); // Fallback
    }
    exit();

} catch (Exception $e) {
    $conn->rollback();
    // Show the exact error so you know what is wrong
    echo "Error processing booking: " . $e->getMessage();
    // Optional: Redirect back with error
    // header("Location: ../payment.php?error=" . urlencode($e->getMessage()));
}
?>