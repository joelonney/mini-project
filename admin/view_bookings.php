<?php
include '../includes/db.php';

// 1. Security Check: Ensure user is Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

// 2. Fetch Bookings
// We join bookings with users, buses, routes, and seats to show full details
 $sql = "SELECT 
            b.booking_id, 
            b.travel_date, 
            b.status, 
            u.name as passenger_name, 
            u.phone as passenger_phone,
            bus.bus_number, 
            bus.bus_type, 
            r.source, 
            r.destination, 
            s.seat_number,
            p.amount
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN buses bus ON b.bus_id = bus.bus_id
        JOIN routes r ON bus.route_id = r.route_id
        JOIN seats s ON b.seat_id = s.seat_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        ORDER BY b.booking_id DESC";

 $result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Bookings - SmartBus Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { background-color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05) !important; }
        .table thead { background-color: #E53935; color: white; }
        .btn-primary { background-color: #E53935; border-color: #E53935; }
        .btn-primary:hover { background-color: #D32F2F; border-color: #D32F2F; }
        .text-primary { color: #E53935 !important; }
        .navbar-custom { background-color: #E53935; box-shadow: 0 4px 15px rgba(229, 57, 53, 0.2); }
        .status-badge { padding: 5px 10px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; }
        .status-booked { background-color: #d1fae5; color: #0f5132; }
        .status-cancelled { background-color: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-custom p-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-bus-alt me-2 text-white"></i>SmartBus Admin
            </a>
            <a href="../includes/logout.php" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-list-alt me-2 text-primary"></i>Manage Bookings</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">View Bookings</li>
                </ol>
            </nav>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Passenger</th>
                                <th>Route</th>
                                <th>Bus / Seat</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['booking_id']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['passenger_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['passenger_email']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($row['source']); ?></small>
                                        <i class="fas fa-long-arrow-alt-down text-primary mx-1"></i>
                                        <small><?php echo htmlspecialchars($row['destination']); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($row['bus_number']); ?> (<?php echo $row['bus_type']; ?>)</div>
                                        <span class="badge bg-secondary"><?php echo $row['seat_number']; ?></span>
                                    </td>
                                    <td><?php echo $row['travel_date']; ?></td>
                                    <td>₹<?php echo $row['amount']; ?></td>
                                    <td>
                                        <?php if($row['status'] == 'BOOKED'): ?>
                                            <span class="status-badge status-booked">BOOKED</span>
                                        <?php else: ?>
                                            <span class="status-badge status-cancelled">CANCELLED</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Optional: Delete Button -->
                                        <button onclick="if(confirm('Cancel this booking?')) { window.location.href='cancel_booking.php?id=<?php echo $row['booking_id']; ?>'; }" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No bookings found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>