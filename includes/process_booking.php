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
 $seats_str = $_POST['seats']; // Comma separated string: "L1,L2"
 $from = $_POST['from'];
 $to = $_POST['to'];

// Explode seats into array
 $seat_numbers = explode(',', $seats_str);

// 3. Transaction Start (Critical to prevent double booking)
 $conn->begin_transaction();

try {
    // Loop through each seat to create Booking and Ticket
    foreach ($seat_numbers as $seat_number) {
        $seat_number = trim($seat_number);

        // A. Find Seat ID based on Seat Number and Bus ID
        $stmt = $conn->prepare("SELECT seat_id FROM seats WHERE seat_number = ? AND bus_id = ?");
        $stmt->bind_param("si", $seat_number, $bus_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $seat_row = $result->fetch_assoc();

        if (!$seat_row) {
            throw new Exception("Seat $seat_number not found on this bus.");
        }
        $seat_id = $seat_row['seat_id'];

        // B. Insert Booking
        $stmt_book = $conn->prepare("INSERT INTO bookings (user_id, bus_id, seat_id, travel_date, status) VALUES (?, ?, ?, ?, 'BOOKED')");
        $stmt_book->bind_param("iiss", $user_id, $bus_id, $seat_id, $travel_date);
        $stmt_book->execute();
        
        $booking_id = $conn->insert_id; // Get the ID we just created

        // C. Insert Payment (We'll assume the payment method from the form or generic)
        // Note: You didn't pass payment method in the hidden inputs, defaulting to CARD
        $payment_method = "CARD"; // You can enhance this to pass real method
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

    // 4. Redirect to Ticket Page (We need to pick ONE booking ID to show)
    // Since we selected multiple seats, we usually show the first one or a combined view.
    // Let's get the last booking ID
    $last_booking_id = $conn->insert_id; 

    header("Location: ../ticket.php?id=" . ($last_booking_id - count($seat_numbers) + 1)); // Show first ticket
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error processing booking: " . $e->getMessage();
    // Ideally redirect back with an error message
}
?>