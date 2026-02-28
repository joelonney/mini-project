<?php
include '../includes/db.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

// Fetch Stats
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$total_revenue = $conn->query("
    SELECT SUM(p.amount) as total 
    FROM payments p 
    JOIN bookings b ON p.booking_id = b.booking_id 
    WHERE p.payment_status = 'SUCCESS'
")->fetch_assoc()['total'] ?? 0;

$route_stats = $conn->query("
    SELECT r.source, r.destination, COUNT(b.booking_id) as bookings 
    FROM bookings b
    JOIN buses bus ON b.bus_id = bus.bus_id
    JOIN routes r ON bus.route_id = r.route_id
    GROUP BY r.route_id
    ORDER BY bookings DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark p-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">SmartBus Admin</a>
            <a href="../includes/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="fas fa-chart-line me-2 text-info"></i>System Reports</h2>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card text-white bg-primary mb-3 shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Bookings</h5>
                        <p class="card-text display-4 fw-bold"><?php echo $total_bookings; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-success mb-3 shadow">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue</h5>
                        <p class="card-text display-4 fw-bold">$<?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white fw-bold">Popular Routes</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Source</th>
                            <th>Destination</th>
                            <th>Total Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $route_stats->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['source']); ?></td>
                                <td><?php echo htmlspecialchars($row['destination']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo $row['bookings']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button onclick="window.print()" class="btn btn-outline-dark"><i class="fas fa-print me-2"></i>Print Report</button>
        </div>
    </div>
</body>
</html>
