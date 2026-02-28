<?php
include 'includes/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Validate Input
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Ticket ID");
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 3. Fetch Booking & Bus Details
// We join multiple tables to get a complete picture: Bookings, Buses, Routes, Seats, Users
$sql = "SELECT 
            b.booking_id, b.travel_date, b.status,
            u.name as passenger_name, u.phone,
            bus.bus_number, bus.bus_type, bus.departure_time,
            r.source, r.destination,
            s.seat_number,
            t.qr_code_data,
            p.amount as ticket_price
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN buses bus ON b.bus_id = bus.bus_id
        JOIN routes r ON bus.route_id = r.route_id
        JOIN seats s ON b.seat_id = s.seat_id
        LEFT JOIN tickets t ON b.booking_id = t.booking_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        WHERE b.booking_id = ? AND b.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Ticket not found or you don't have permission to view it.");
}

$ticket = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Ticket #<?php echo $booking_id; ?> - SmartBus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        body { background-color: #f3f4f6; font-family: 'Inter', sans-serif; }
        
        .ticket-container {
            max-width: 700px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }

        /* Perforated Edge Effect */
        .ticket-header {
            background: linear-gradient(135deg, #E53935 0%, #B71C1C 100%);
            color: white;
            padding: 25px;
            position: relative;
        }
        
        .ticket-body { padding: 30px; }

        .info-label { font-size: 0.8rem; text-transform: uppercase; color: #888; font-weight: 600; letter-spacing: 0.5px; }
        .info-value { font-size: 1.1rem; font-weight: 700; color: #333; margin-bottom: 20px; }
        
        .route-line {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 25px; border-bottom: 2px dashed #eee; padding-bottom: 20px;
        }
        .route-city { font-size: 1.5rem; font-weight: 800; color: #E53935; }
        
        .qr-box {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

        .status-badge {
            background: #d1fae5; color: #065f46;
            padding: 5px 12px; border-radius: 50px;
            font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
        }

        @media print {
            body { background: white; }
            .no-print { display: none; }
            .ticket-container { box-shadow: none; border: 1px solid #ddd; margin: 0; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-light bg-white shadow-sm no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-bus-alt text-danger me-2"></i>SmartBus</a>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Back to Home</a>
        </div>
    </nav>

    <div class="container">
        <div class="ticket-container" id="printableTicket">
            
            <div class="ticket-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold">E-TICKET</h4>
                        <small style="opacity:0.8;">SmartBus Travel Services</small>
                    </div>
                    <div class="text-end">
                        <div style="font-family: 'Courier Prime', monospace; font-size: 1.2rem;">
                            PNR: <?php echo str_pad($ticket['booking_id'], 8, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ticket-body">
                
                <div class="route-line">
                    <div class="text-start">
                        <span class="d-block text-muted small">FROM</span>
                        <span class="route-city"><?php echo $ticket['source']; ?></span>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-bus text-muted mx-3"></i>
                        <div class="status-badge mt-1"><?php echo $ticket['status']; ?></div>
                    </div>
                    <div class="text-end">
                        <span class="d-block text-muted small">TO</span>
                        <span class="route-city"><?php echo $ticket['destination']; ?></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="info-label">Passenger</div>
                        <div class="info-value"><?php echo htmlspecialchars($ticket['passenger_name']); ?></div>

                        <div class="info-label">Bus Number</div>
                        <div class="info-value"><?php echo $ticket['bus_number']; ?></div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-label">Travel Date</div>
                        <div class="info-value"><?php echo $ticket['travel_date']; ?></div>

                        <div class="info-label">Seat No</div>
                        <div class="info-value text-danger" style="font-size: 1.4rem;"><?php echo $ticket['seat_number']; ?></div>
                    </div>

                    <div class="col-md-4">
                        <div class="qr-box">
                            <div id="qrcode"></div>
                            <small class="text-muted mt-2 text-center" style="font-size:0.7rem;">Scan for Check-in</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3 pt-3 border-top">
                    <div class="col-6">
                        <div class="info-label">Departure Time</div>
                        <div class="info-value"><?php echo $ticket['departure_time']; ?></div>
                    </div>
                    <div class="col-6 text-end">
                        <div class="info-label">Bus Type</div>
                        <div class="info-value"><?php echo $ticket['bus_type']; ?></div>
                    </div>
                </div>

            </div>
            
            <div class="bg-light p-3 text-center small text-muted rounded-bottom">
                Please show this ticket along with a valid ID proof during boarding.
            </div>
        </div>

        <div class="text-center mb-5 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                <i class="fas fa-print me-2"></i> Print Ticket
            </button>
        </div>
    </div>

    <script type="text/javascript">
        // Generate QR Code containing readable passenger details for scanners
        const qrData = `SmartBus E-Ticket
--------------------
Name: <?php echo addslashes($ticket['passenger_name']); ?>
Route: <?php echo addslashes($ticket['source'] . ' to ' . $ticket['destination']); ?>
Date: <?php echo $ticket['travel_date']; ?>
Time: <?php echo $ticket['departure_time']; ?>
Seat: <?php echo $ticket['seat_number']; ?>
Price: Rs.<?php echo isset($ticket['ticket_price']) ? $ticket['ticket_price'] : 'N/A'; ?>
 
--------------------
PNR: <?php echo str_pad($ticket['booking_id'], 8, '0', STR_PAD_LEFT); ?>`;
        
        new QRCode(document.getElementById("qrcode"), {
            text: qrData,
            width: 140,
            height: 140,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.L
        });
    </script>
</body>
</html>