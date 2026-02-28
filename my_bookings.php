<?php
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

// Fetch all bookings
$sql = "
    SELECT b.*, bus.bus_number, bus.bus_type, bus.departure_time, bus.arrival_time, 
           r.source, r.destination, r.distance_km, 
           s.seat_number, t.ticket_id
    FROM bookings b
    JOIN buses bus ON b.bus_id = bus.bus_id
    JOIN routes r ON bus.route_id = r.route_id
    JOIN seats s ON b.seat_id = s.seat_id
    LEFT JOIN tickets t ON b.booking_id = t.booking_id
    WHERE b.user_id = ?
    ORDER BY b.travel_date DESC, bus.departure_time ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$upcoming = [];
$completed = [];
$cancelled = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] == 'CANCELLED') {
        $cancelled[] = $row;
    } elseif ($row['travel_date'] >= $current_date) {
        $upcoming[] = $row; // Future or Today (Simplified)
    } else {
        $completed[] = $row; // Past
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - SmartBus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .nav-pills .nav-link { 
            color: #495057; border-radius: 20px; padding: 8px 20px; font-weight: 500;
        }
        .nav-pills .nav-link.active { 
            background-color: #E53935; color: white; 
        }
        .card { border: none; border-radius: 12px; transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .btn-report { font-size: 0.8rem; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-bus-alt text-danger"></i> SmartBus</a>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="text-white text-decoration-none small"><i class="fas fa-home"></i> Home</a>
                <a href="includes/logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4 fw-bold">My Trips</h2>
        
        <!-- Tabs -->
        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-upcoming-tab" data-bs-toggle="pill" data-bs-target="#pills-upcoming" type="button" role="tab">Upcoming</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-completed-tab" data-bs-toggle="pill" data-bs-target="#pills-completed" type="button" role="tab">Completed</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-cancelled-tab" data-bs-toggle="pill" data-bs-target="#pills-cancelled" type="button" role="tab">Cancelled</button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            
            <!-- UPCOMING TAB -->
            <div class="tab-pane fade show active" id="pills-upcoming" role="tabpanel">
                <?php renderBookings($upcoming, 'upcoming'); ?>
            </div>

            <!-- COMPLETED TAB -->
            <div class="tab-pane fade" id="pills-completed" role="tabpanel">
                <?php renderBookings($completed, 'completed'); ?>
            </div>

            <!-- CANCELLED TAB -->
            <div class="tab-pane fade" id="pills-cancelled" role="tabpanel">
                <?php renderBookings($cancelled, 'cancelled'); ?>
            </div>

        </div>
    </div>

    <!-- Report Lost Item Modal -->
    <div class="modal fade" id="lostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-box-open text-warning me-2"></i>Report Lost Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="includes/report_lost.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="bus_id" id="modalBusId">
                        <input type="hidden" name="booking_id" id="modalBookingId">
                        <p class="text-muted small">Please describe the item you lost. Include color, brand, or any distinct features.</p>
                        <textarea name="description" class="form-control" rows="4" placeholder="e.g., Blue Samsonite backpack left on seat L4..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openLostModal(busId, bookingId) {
            document.getElementById('modalBusId').value = busId;
            document.getElementById('modalBookingId').value = bookingId;
            new bootstrap.Modal(document.getElementById('lostModal')).show();
        }
    </script>
</body>
</html>

<?php
function renderBookings($list, $type) {
    if (empty($list)) {
        echo '<div class="alert alert-light text-center py-5 shadow-sm"><h5 class="text-muted">No bookings found in this category.</h5></div>';
        return;
    }
    echo '<div class="row">';
    foreach ($list as $row) {
        ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <span class="fw-bold text-dark fs-5"><?php echo date('d M', strtotime($row['travel_date'])); ?></span>
                        <small class="text-muted ms-2"><?php echo date('l', strtotime($row['travel_date'])); ?></small>
                    </div>
                    <span class="badge bg-<?php echo $type == 'cancelled' ? 'danger' : ($type == 'completed' ? 'secondary' : 'success'); ?> px-3 py-2 rounded-pill">
                        <?php echo $row['status']; ?>
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">
                        <?php echo htmlspecialchars($row['source']); ?> 
                        <i class="fas fa-arrow-right text-muted mx-2 small"></i> 
                        <?php echo htmlspecialchars($row['destination']); ?>
                    </h5>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Bus No</small>
                            <span class="fw-bold"><?php echo htmlspecialchars($row['bus_number']); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Seat</small>
                            <span class="fw-bold"><?php echo $row['seat_number']; ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Type</small>
                            <span><?php echo $row['bus_type']; ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Time</small>
                            <span><?php echo date('h:i A', strtotime($row['departure_time'])); ?></span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <?php if ($type != 'cancelled'): ?>
                            <a href="ticket.php?id=<?php echo $row['booking_id']; ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                                <i class="fas fa-qrcode me-1"></i> Ticket
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($type == 'completed'): ?>
                            <button onclick="openLostModal(<?php echo $row['bus_id']; ?>, <?php echo $row['booking_id']; ?>)" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-search me-1"></i> Lost Item?
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    echo '</div>';
}
?>
